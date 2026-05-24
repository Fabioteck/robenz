@props(['stations'])

<div x-data="initGlobalMap({{ json_encode($stations) }})" class="w-full">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <div id="map_canvas" class="w-full h-[500px] bg-slate-100 rounded-lg shadow-md"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/BR9PVJ_kHyh00x1lP+7o=" crossorigin=""></script>
    <script>
        function initGlobalMap(stations) {
            return {
                stations: stations,
                init() {
                    this.$nextTick(() => {
                        this.renderMap();
                    });
                },
                renderMap() {
                    var map = L.map('map_canvas').setView([45.0704, 11.7914], 11);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    if (this.stations.length > 0) {
                        var markers = [];
                        
                        this.stations.forEach(station => {
                            var popupContent = '<div>' +
                                '<h3>' + station.name + '</h3>' +
                                '<p>' + station.address + '</p>' +
                                '<p><strong>Prezzo: </strong>' + parseFloat(station.price).toFixed(3) + ' €/L</p>' +
                                '<a href="/stations/' + station.id + '" class="text-blue-600 underline font-bold">Vedi dettagli</a>' +
                                '</div>';

                            var marker = L.marker([station.lat, station.lng]).addTo(map);
                            marker.bindPopup(popupContent);
                            markers.push(marker);
                        });

                        var group = L.featureGroup(markers).addTo(map);
                        map.fitBounds(group.getBounds().pad(0.1));
                    }
                }
            }
        }
    </script>
</div>
