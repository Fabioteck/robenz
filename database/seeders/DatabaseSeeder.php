<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Price;
use App\Models\PriceHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $comuniRovigo = [
            ['nome' => 'Rovigo', 'lat' => 45.0704, 'lon' => 11.7901],
            ['nome' => 'Adria', 'lat' => 45.0535, 'lon' => 12.0549],
            ['nome' => 'Porto Viro', 'lat' => 45.0164, 'lon' => 12.2185],
            ['nome' => 'Lendinara', 'lat' => 45.0845, 'lon' => 11.6001],
            ['nome' => 'Badia Polesine', 'lat' => 45.0912, 'lon' => 11.4934],
            ['nome' => 'Occhiobello', 'lat' => 44.9214, 'lon' => 11.5833],
        ];

        $marchi = ['Eni', 'IP', 'Q8', 'Tamoil', 'Pompe Bianche', 'Esso'];
        $carburanti = [
            ['type' => 'Benzina', 'base' => 1.780],
            ['type' => 'Gasolio', 'base' => 1.650],
            ['type' => 'GPL', 'base' => 0.710],
            ['type' => 'Metano', 'base' => 1.250]
        ];

        foreach ($comuniRovigo as $comune) {
            for ($i = 1; $i <= 4; $i++) {
                $brand = $marchi[array_rand($marchi)];
                
                $station = Station::create([
                    'mimit_id' => rand(10000, 99999),
                    'brand' => $brand,
                    'name' => $brand . " - " . $comune['nome'] . " S.R.L.",
                    'address' => "Via Roma " . rand(1, 150),
                    'municipality' => strtoupper($comune['nome']),
                    'province' => 'RO',
                    'latitude' => $comune['lat'] + (rand(-15, 15) / 500),
                    'longitude' => $comune['lon'] + (rand(-15, 15) / 500),
                    'updated_at' => Carbon::now()->subHours(rand(0, 48))
                ]);

                foreach ($carburanti as $fuel) {
                    foreach ([true, false] as $isSelf) {
                        if ($fuel['type'] === 'GPL' || $fuel['type'] === 'Metano') {
                            $isSelf = false; // GPL e Metano sono quasi sempre servito
                        }

                        $variabilePrezzo = (rand(-80, 80) / 1000);
                        $prezzoFinale = $fuel['base'] + $variabilePrezzo + ($isSelf ? 0 : 0.120);

                        Price::create([
                            'station_id' => $station->id,
                            'fuel_type' => $fuel['type'],
                            'is_self' => $isSelf,
                            'price' => $prezzoFinale,
                        ]);

                        // Genera 7 giorni di storico per i grafici
                        for ($d = 6; $d >= 0; $d--) {
                            PriceHistory::create([
                                'station_id' => $station->id,
                                'fuel_type' => $fuel['type'],
                                'is_self' => $isSelf,
                                'price' => $prezzoFinale + (rand(-30, 30) / 1000),
                                'recorded_at' => Carbon::today()->subDays($d)
                            ]);
                        }
                    }
                }
            }
        }
    }
}
