document.addEventListener('DOMContentLoaded', function() {
    // Get the latitude and longitude from hidden inputs
    const latitude = parseFloat(document.getElementById('booking-latitude').value);
    const longitude = parseFloat(document.getElementById('booking-longitude').value);
    const address = document.getElementById('charge-point-address').textContent;
    
    // Check if we have valid coordinates
    if (isNaN(latitude) || isNaN(longitude)) {
        document.getElementById('map-container').innerHTML = 
            '<div class="alert alert-warning text-center py-4 mb-0">' +
            '<i class="bi bi-geo-alt fs-1 text-muted"></i>' +
            '<p class="mt-3 mb-0">Location coordinates not available</p></div>';
        return;
    }
    
    // Initialize the map
    const map = L.map('map-container').setView([latitude, longitude], 15);
    
    // Add the OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add a marker at the charge point location
    const marker = L.marker([latitude, longitude]).addTo(map);
    marker.bindPopup(`<b>${address}</b><br>Charge Point`).openPopup();
    
    // Ensure the map displays correctly
    setTimeout(function() {
        map.invalidateSize();
    }, 100);

    // Scroll to the bottom of message container to show latest messages
    const messageContainer = document.querySelector('.overflow-auto');
    if (messageContainer) {
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }
});
