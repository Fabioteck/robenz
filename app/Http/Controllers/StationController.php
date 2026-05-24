<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StationController extends Controller
{
    public function index(Request $request)
{
    $fuelType = $request->input('fuel_type', 'Benzina');
    $isSelf = $request->boolean('is_self', true);
    $municipality = $request->input('municipality');

    $cleanMunicipality = $municipality ? strtoupper(trim($municipality)) : null;

    $cacheKey = "stations_array_RO_{$fuelType}_{$isSelf}_" . ($cleanMunicipality ?? 'all');

    $stations = Cache::remember($cacheKey, 1800, function () use ($fuelType, $isSelf, $cleanMunicipality) {
        $query = Station::where('province', 'RO')
            ->join('prices', 'stations.id', '=', 'prices.station_id')
            ->where('prices.fuel_type', $fuelType)
            ->where('prices.is_self', $isSelf)
            ->select(
                'stations.*', 
                'stations.municipality as municipality_name', 
                'stations.lat', 
                'stations.lng', 
                'stations.name', 
                'prices.price'
            )
            ->orderBy('prices.price', 'asc');

        if ($cleanMunicipality) {
            $query->where('stations.municipality', $cleanMunicipality);
        }

        // Salva in cache come array nativo per evitare conflitti di serializzazione
        return $query->get()->toArray();
    });

    $comuni = Cache::remember('comuni_RO', 86400, function () {
        return Station::where('province', 'RO')
            ->whereNotNull('municipality')
            ->distinct()
            ->orderBy('municipality')
            ->pluck('municipality')
            ->map(fn($item) => strtoupper(trim($item)))
            ->toArray();
    });

    return view('stations.index', [
        'stations' => collect($stations), // Lo convertiamo al volo in collection per non rompere i metodi count() di Blade
        'comuni' => $comuni,
        'fuelType' => $fuelType,
        'isSelf' => $isSelf,
        'municipality' => $cleanMunicipality
    ]);
}


    public function show(Station $station)
    {
        $station->load('prices');

        $historyData = PriceHistory::where('station_id', $station->id)
            ->orderBy('recorded_at', 'asc')
            ->get()
            ->groupBy('fuel_type');

        return view('stations.show', compact('station', 'historyData'));
    }
}
