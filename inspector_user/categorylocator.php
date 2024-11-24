<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Leaflet CSS and JS CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <title>Market Locator</title>
</head>
<body>
    <div style="margin-bottom: 10px;">
        <input type="text" id="search" placeholder="Search location..." style="width: 100px; padding: 10px;">
        <button onclick="searchLocation()">Search</button>
    </div>

    <div id="map" style="height: 500px;"></div>

    <script>
        // Initialize the map and set its view to Manila coordinates
        var map = L.map('map').setView([14.7353, 120.9962], 13);

        // Set up the tile layer from OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add a draggable marker to the map
        var marker = L.marker([14.7353, 120.9962], { draggable: true }).addTo(map);

        // Function to get location name using reverse geocoding
        function getLocationName(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        marker.bindPopup(`<b>Name Location:</b><br>${data.display_name}`).openPopup();
                    } else {
                        marker.bindPopup(`<b>Name Location:</b><br>Unknown Location`).openPopup();
                    }
                })
                .catch(error => {
                    console.error('Error fetching location name:', error);
                });
        }

        // Initial location name display
        getLocationName(marker.getLatLng().lat, marker.getLatLng().lng);

        // Event listener for when the marker is dragged
        marker.on('dragend', function(e) {
            var position = marker.getLatLng();
            getLocationName(position.lat, position.lng);
        });

        // Function to search for a location using the Nominatim API
        function searchLocation() {
            var query = document.getElementById('search').value;
            if (query) {
                fetch(`https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            var lat = data[0].lat;
                            var lon = data[0].lon;
                            // Move the marker to the searched location
                            marker.setLatLng([lat, lon]);
                            // Set the map view to the new location
                            map.setView([lat, lon], 13);
                            // Get the location name for the searched location
                            getLocationName(lat, lon);
                        } else {
                            alert('Location not found.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching search results:', error);
                    });
            } else {
                alert('Please enter a location to search.');
            }
        }
    </script>
</body>
</html>
