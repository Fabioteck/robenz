@props(['stations'])

@php
    $cleanMarkers = [];
    foreach ($stations as $station) {
        $s = (array) $station;
        
        $lat = isset($s['lat']) ? (float) $s['lat'] : null;
        $lng = isset($s['lng']) ? (float) $s['lng'] : null;
        
        // CORRETTO: Aggiunto il simbolo del dollaro $ a $lng
        if (!$lat || !$lng) continue;

        $cleanMarkers[] = [
            'id'           => $s['id'] ?? null,
            'name'         => $s['name'] ?? ($s['manager'] ?? 'Distributore'),
            'address'      => $s['address'] ?? '',
            'municipality' => $s['municipality_name'] ?? ($s['municipality'] ?? ''),
            'price'        => isset($s['price']) ? number_format($s['price'], 3, ',', '.') : '0,000',
            'lat'          => $lat,
            'lng'          => $lng,
            'url'          => isset($s['id']) ? route('stations.show', $s['id']) : '#'
        ];
    }
@endphp

<div x-data="{ init() { this.buildProvincialMap(); } }" class="w-full">
    <div id="global_provincial_map" class="w-full h-[500px] bg-slate-100 rounded-xl shadow-inner border border-slate-200 z-0"></div>
</div>

<script>
    const provincialDistributors = {!! json_encode($cleanMarkers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

    function buildProvincialMap() {
        const mapContainer = document.getElementById('global_provincial_map');
        if (!mapContainer || mapContainer._leaflet_id) return;

        if (typeof L === 'undefined') {
            console.error("Errore Logico: Leaflet non è presente globalmente.");
            return;
        }

        const provincialMap = L.map('global_provincial_map').setView([45.0704, 11.7914], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(provincialMap);

        if (provincialDistributors && provincialDistributors.length > 0) {
            const markersGroup = new L.featureGroup();
            
            provincialDistributors.forEach(station => {
                const popupContent = `
                    <div class="p-1 font-sans text-slate-800">
                        <h3 class="font-bold text-sm text-slate-900 mb-1">${station.name}</h3>
                        <p class="text-xs text-slate-500 mb-2">${station.address}, ${station.municipality}</p>
                        <div class="flex justify-between items-center bg-slate-50 p-2 rounded-lg mb-2">
                            <span class="text-xs text-slate-500 font-medium">Prezzo:</span>
                            <span class="text-sm font-bold text-emerald-600">${station.price} €/L</span>
                        </div>
                        <a href="${station.url}" class="block text-center text-xs bg-blue-600 text-white font-semibold py-1.5 px-3 rounded-md" style="color:white!important;text-decoration:none;">Vedi dettagli</a>
                    </div>
                `;

                const marker = L.marker([station.lat, station.lng]).bindPopup(popupContent);
                markersGroup.addLayer(marker);
            });

            markersGroup.addTo(provincialMap);
            
            setTimeout(() => {
                provincialMap.fitBounds(markersGroup.getBounds().pad(0.1));
                provincialMap.invalidateSize();
            }, 100);
        }
    }
</script>

<style>
    .leaflet-popup-content-wrapper { border-radius: 12px !important; padding: 4px !important; }
    .leaflet-popup-close-button { top: 8px !important; right: 8px !important; }
</style>
