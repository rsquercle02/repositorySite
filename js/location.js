// Initialize the map and set the default view to Quezon City
var map = L.map('map').setView([14.6760, 121.0437], 13);  // Quezon City coordinates

// Add a tile layer to the map (you can use other tile providers if needed)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

var marker; // To store the marker reference

// Add a click event listener to the map
map.on('click', function(e) {
var lat = e.latlng.lat;
var lng = e.latlng.lng;

// Update the input fields with the clicked latitude and longitude
document.getElementById('latitude').value = lat;
document.getElementById('longitude').value = lng;

// If there's already a marker, remove it
if (marker) {
    marker.remove();
}

// Add a marker at the clicked location
marker = L.marker([lat, lng]).addTo(map)
    .bindPopup("<b>You clicked here!</b><br>Latitude: " + lat + "<br>Longitude: " + lng)
    .openPopup();
});

/* // Handle the button click event for "Pin Location"
document.getElementById('pinLocationBtn').addEventListener('click', function() {
// Get the coordinates from the input fields
var lat = parseFloat(document.getElementById('latitude').value);
var lng = parseFloat(document.getElementById('longitude').value);

// If valid coordinates, drop the pin on the map
if (!isNaN(lat) && !isNaN(lng)) {
    if (marker) {
    marker.remove(); // Remove the previous marker if exists
    }

    // Add a marker at the coordinates
    marker = L.marker([lat, lng]).addTo(map)
    .bindPopup("<b>Location:</b><br>Latitude: " + lat + "<br>Longitude: " + lng)
    .openPopup();

    // Optionally, move the map view to the new marker location
    map.setView([lat, lng], 13);

    // Log coordinates in the console
    console.log('Latitude: ' + lat + ', Longitude: ' + lng);
} else {
    alert("Please enter valid coordinates.");
}
}); */

// Handle the "Locate Me" button click event
document.getElementById('locateMeBtn').addEventListener('click', function() {
// Check if geolocation is available
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;

    // Update input fields with current location
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;

    // If there's already a marker, remove it
    if (marker) {
        marker.remove();
    }

    // Add a marker for the current location
    marker = L.marker([lat, lng]).addTo(map)
        .bindPopup("<b>Your current location</b><br>Latitude: " + lat + "<br>Longitude: " + lng)
        .openPopup();

    // Optionally, move the map view to the user's current location
    map.setView([lat, lng], 13);

    // Log the current location coordinates
    console.log('Current Location -> Latitude: ' + lat + ', Longitude: ' + lng);
    }, function() {
    alert("Unable to retrieve your location.");
    });
} else {
    alert("Geolocation is not supported by this browser.");
}
});