<?php

namespace App\Services;

use App\Models\Price;
use App\Models\Station;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Scarica e importa i CSV open data MIMIT.
 *
 * Due file giornalieri:
 *   - anagrafica_impianti_attivi.csv  → dati impianto (stazione)
 *   - prezzo_alle_8.csv               → prezzi del giorno
 *
 * Delimitatore: pipe "|"
 * Encoding: UTF-8
 */
class MimitImportService
{
    // URL pubblici MIMIT (aggiornati quotidianamente)
    const URL_ANAGRAFICA = 'https://www.mimit.gov.it/images/exportCSV/anagrafica_impianti_attivi.csv';
    const URL_PREZZI     = 'https://www.mimit.gov.it/images/exportCSV/prezzo_alle_8.csv';

    // Filtra solo queste province (estendi per il Veneto completo)
    const TARGET_PROVINCES = ['RO', 'VE', 'PD', 'VR', 'VI', 'TV', 'BL'];

    private int $stationsImported = 0;
    private int $pricesImported   = 0;

    // ─── Entry point ──────────────────────────────────────────────

    public function run(array $provinces = ['RO']): array
    {
        Log::info('[MimitImport] Avvio sync', ['province' => $provinces]);

        $anagrafica = $this->downloadCsv(self::URL_ANAGRAFICA, 'anagrafica.csv');
        $prezzi     = $this->downloadCsv(self::URL_PREZZI, 'prezzi.csv');

        $stationIds = $this->importStations($anagrafica, $provinces);
        $this->importPrices($prezzi, $stationIds);

        Log::info('[MimitImport] Completato', [
            'stazioni' => $this->stationsImported,
            'prezzi'   => $this->pricesImported,
        ]);

        return [
            'stations' => $this->stationsImported,
            'prices'   => $this->pricesImported,
        ];
    }

    // ─── Download ─────────────────────────────────────────────────

    private function downloadCsv(string $url, string $localName): string
    {
        $response = Http::timeout(60)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("Download fallito: {$url} → HTTP {$response->status()}");
        }

        $path = storage_path("app/mimit/{$localName}");
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, $response->body());

        return $path;
    }

    // ─── Import anagrafica ────────────────────────────────────────

    /**
     * Struttura CSV anagrafica (pipe-separated):
     * idImpianto|Gestore|Bandiera|Tipo Impianto|Nome Impianto|Indirizzo|Comune|Provincia|Latitudine|Longitudine
     */
    private function importStations(string $filePath, array $provinces): array
    {
        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, '|'); // salta header

        $stationIds = [];

        DB::transaction(function () use ($handle, $provinces, &$stationIds) {
            while (($row = fgetcsv($handle, 0, '|')) !== false) {
                if (count($row) < 10) continue;

                [$mimitId, $manager, $flag, $type, $name, $address, $municipality, $province, $lat, $lng] = $row;

                $province = strtoupper(trim($province));
                if (!in_array($province, $provinces)) continue;

                $station = Station::updateOrCreate(
                    ['mimit_id' => (int) $mimitId],
                    [
                        'manager'      => trim($manager),
                        'flag'         => trim($flag) ?: null,
                        'type'         => trim($type),
                        'name'         => trim($name) ?: null,
                        'address'      => trim($address),
                        'municipality' => strtoupper(trim($municipality)),
                        'province'     => $province,
                        'lat'          => $lat ? (float) str_replace(',', '.', $lat) : null,
                        'lng'          => $lng ? (float) str_replace(',', '.', $lng) : null,
                    ]
                );

                $stationIds[$mimitId] = $station->id;
                $this->stationsImported++;
            }
        });

        fclose($handle);

        return $stationIds;
    }

    // ─── Import prezzi ────────────────────────────────────────────

    /**
     * Struttura CSV prezzi (pipe-separated):
     * idImpianto|descCarburante|prezzo|isSelf|dtComu
     *
     * Strategia: upsert sull'unique key (station_id, fuel_type, is_self)
     * così il record viene aggiornato se il gestore ha mandato un nuovo prezzo.
     */
    private function importPrices(string $filePath, array $stationIds): void
    {
        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, '|'); // salta header

        $today  = now()->toDateString();
        $buffer = [];

        while (($row = fgetcsv($handle, 0, '|')) !== false) {
            if (count($row) < 5) continue;

            [$mimitId, $fuelType, $price, $isSelf, $dtComu] = $row;

            // Salta impianti fuori dalle province target
            if (!isset($stationIds[$mimitId])) continue;

            $stationId = $stationIds[$mimitId];
            $fuelType  = trim($fuelType);

            // Normalizza tipo carburante
            $fuelType = $this->normalizeFuelType($fuelType);

            // Parsea data comunicazione (GG/MM/AAAA HH:MM:SS)
            $reportedAt = $this->parseDate($dtComu);

            $buffer[] = [
                'station_id'     => $stationId,
                'fuel_type'      => $fuelType,
                'price'          => (float) str_replace(',', '.', $price),
                'is_self'        => (int) $isSelf === 1,
                'reported_at'    => $reportedAt,
                'reference_date' => $today,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            // Flush ogni 500 righe
            if (count($buffer) >= 500) {
                $this->upsertPrices($buffer);
                $buffer = [];
            }
        }

        if (!empty($buffer)) {
            $this->upsertPrices($buffer);
        }

        fclose($handle);
    }

    private function upsertPrices(array $rows): void
    {
        Price::upsert(
            $rows,
            ['station_id', 'fuel_type', 'is_self'],   // unique key
            ['price', 'reported_at', 'reference_date', 'updated_at']
        );
        $this->pricesImported += count($rows);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Normalizza nomi carburanti MIMIT in etichette consistenti.
     * Il CSV può avere varianti come "Benzina_Super" o "Gasolio BS".
     */
    private function normalizeFuelType(string $raw): string
    {
        $raw = strtolower(trim($raw));

        return match (true) {
            str_contains($raw, 'benzina') => 'Benzina',
            str_contains($raw, 'gasolio') => 'Gasolio',
            str_contains($raw, 'gpl')     => 'GPL',
            str_contains($raw, 'metano')  => 'Metano',
            str_contains($raw, 'gnl')     => 'GNL',
            default                       => ucfirst($raw),
        };
    }

    /**
     * Converte "GG/MM/AAAA HH:MM:SS" in Carbon-compatibile.
     */
    private function parseDate(string $raw): string
    {
        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', trim($raw))->toDateTimeString();
        } catch (\Exception) {
            return now()->toDateTimeString();
        }
    }
}
