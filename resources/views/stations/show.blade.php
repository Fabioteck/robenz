@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">{{ $station->name }}</h1>
        <p class="text-slate-600 mb-4">{{ $station->address }}, {{ $station->municipality }}</p>
        
        <div class="mb-6 space-y-2">
            @if($station->prices && $station->prices->count() > 0)
                @foreach($station->prices as $p)
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold text-sm mr-2 mb-2">
                        {{ $p->fuel_type }} ({{ $p->is_self ? 'Self' : 'Servito' }}): {{ number_format($p->price, 3, ',', '.') }} €/L
                    </span>
                @endforeach
            @else
                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full font-bold text-sm">
                    Prezzo attuale: {{ number_format($station->price ?? 0, 3, ',', '.') }} €/L
                </span>
            @endif
        </div>

        <!-- Mappa locale focalizzata -->
        <div id="station_map" class="w-full h-[400px] rounded-lg border border-slate-200 bg-slate-50"></div>
    </div>
    
    <div class="mt-6">
        <a href="{{ route('stations.index') }}" class="text-slate-500 hover:text-slate-800 font-medium">← Torna alla lista</a>
    </div>

    {{-- CDN di Leaflet puliti senza attributi integrity corrotti --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Conversione sicura in valori decimali per JavaScript
            var lat = parseFloat("{{ $station->lat }}");
            var lng = parseFloat("{{ $station->lng }}");
            
            // Blocco di sicurezza se le coordinate nel DB sono assenti o corrotte
            if (!lat || !lng) {
                console.error("Coordinate geografiche non valide per questo distributore.");
                return;
            }

            // Inizializza la mappa centrata sul distributore con uno zoom ravvicinato (16)
            var map = L.map('station_map').setView([lat, lng], 16);

            // Scarica i tasselli stradali da internet
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Inserisce il marker sulla posizione esatta e apre il fumetto
            L.marker([lat, lng]).addTo(map)
                .bindPopup('<b>{{ addslashes($station->name) }}</b><br>{{ addslashes($station->address) }}')
                .openPopup();
        });
    </script>
</div>
@endsection
