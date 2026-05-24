@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">{{ $station->name }}</h1>
        <p class="text-slate-600 mb-4">{{ $station->address }}, {{ $station->municipality }}</p>
        
        <div class="mb-6">
            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold">
                Prezzo attuale: {{ number_format($station->price, 3) }} €/L
            </span>
        </div>

        <!-- Mappa locale focalizzata -->
        <div id="station_map" class="w-full h-[400px] rounded-lg border border-slate-200"></div>
    </div>
    
    <div class="mt-6">
        <a href="{{ route('stations.index') }}" class="text-slate-500 hover:text-slate-800 font-medium">← Torna alla lista</a>
    </div>

    <!-- Leaflet CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/BR9PVJ_kHyh00x1lP+7o=" crossorigin=""></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var lat = {{ $station->lat }};
            var lng = {{ $station->lng }};
            
            var map = L.map('station_map').setView([lat, lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map)
                .bindPopup('<b>{{ $station->name }}</b><br>{{ $station->address }}')
                .openPopup();
        });
    </script>
</div>
@endsection
