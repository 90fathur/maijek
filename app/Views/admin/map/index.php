<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 600px;
        border-radius: 12px;
        z-index: 1;
    }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Peta Pelacakan Langsung (Live Map)</h1>
    <span style="padding: 6px 12px; background: rgba(16, 185, 129, 0.1); color: var(--primary); border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
        <i class="fa-solid fa-satellite-dish" style="margin-right: 4px;"></i> Real-time
    </span>
</div>

<div class="card-table animate-fade" style="padding: 16px;">
    <div id="map"></div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Render Zones and Auto-Focus
        var zones = <?= json_encode($zones ?? []) ?>;
        var zoneLayers = [];

        // Initialize map (default center at Jakarta)
        var map = L.map('map').setView([-6.200000, 106.816666], 12);

        // Add Google Maps tiles (Free, Reliable, No API Key watermarks)
        L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            attribution: '&copy; Google Maps',
            maxZoom: 20
        }).addTo(map);

        if (zones && zones.length > 0) {
            zones.forEach(function(zone) {
                try {
                    var coords = JSON.parse(zone.coordinates);
                    if (coords && coords.length > 0) {
                        var polygon = L.polygon(coords, {
                            color: '#10b981',
                            fillColor: '#10b981',
                            fillOpacity: 0.1,
                            weight: 2,
                            dashArray: '5, 5'
                        }).addTo(map);
                        polygon.bindPopup('<b>Zona: ' + zone.name + '</b><br>Status: ' + zone.status);
                        zoneLayers.push(polygon);
                    }
                } catch (e) {
                    console.error("Error parsing zone coordinates", e);
                }
            });

            if (zoneLayers.length > 0) {
                var group = new L.featureGroup(zoneLayers);
                map.fitBounds(group.getBounds());
            }
        }

        // Custom Marker Icon for Driver
        var driverIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/3063/3063822.png',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        var markers = {};

        function fetchDrivers() {
            fetch('<?= base_url('admin/api/drivers') ?>')
                .then(response => response.json())
                .then(data => {
                    for (var id in markers) {
                        map.removeLayer(markers[id]);
                    }
                    markers = {};

                    data.forEach(driver => {
                        var marker = L.marker([driver.latitude, driver.longitude], {
                            icon: driverIcon
                        }).addTo(map);
                        marker.bindPopup(`<b>${driver.name}</b><br>Plat: ${driver.vehicle_plate}<br>Status: ${driver.is_online ? 'Online' : 'Offline'}`);
                        markers[driver.id] = marker;
                    });
                });
        }

        // Initial fetch
        fetchDrivers();

        // Fetch every 10 seconds
        setInterval(fetchDrivers, 10000);
    });
</script>
<?= $this->endSection() ?>