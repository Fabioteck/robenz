<?php

namespace App\Console\Commands;

use App\Services\MimitImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncFuelPrices extends Command
{
    protected $signature = 'fuel:sync
                            {--province=RO : Sigla provincia (es. RO, VE, PD). Separare con virgola per più province.}
                            {--force : Forza il sync anche se già eseguito oggi}';

    protected $description = 'Scarica e importa i prezzi carburanti dal portale open data MIMIT';

    public function handle(MimitImportService $importer): int
    {
        $provinces = array_map('trim', explode(',', $this->option('province')));
        $provinces = array_map('strtoupper', $provinces);

        $cacheKey = 'fuel_sync_done_' . implode('_', $provinces) . '_' . now()->toDateString();

        if (!$this->option('force') && Cache::has($cacheKey)) {
            $this->info('✅ Sync già eseguito oggi. Usa --force per ripetere.');
            return Command::SUCCESS;
        }

        $this->info('🔄 Avvio sync MIMIT per province: ' . implode(', ', $provinces));

        try {
            $result = $importer->run($provinces);

            $this->table(
                ['Tipo', 'Importati'],
                [
                    ['Stazioni', $result['stations']],
                    ['Prezzi',   $result['prices']],
                ]
            );

            // Cache per 23 ore: evita doppi sync
            Cache::put($cacheKey, true, now()->addHours(23));

            // Invalida cache delle query UI
            Cache::tags(['stations'])->flush();

            $this->info('✅ Sync completato.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Errore durante il sync: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
