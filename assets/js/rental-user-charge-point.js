document.addEventListener('DOMContentLoaded', function() {
    let map, markers = [];
    let markerLayer = L.layerGroup();
    let currentLocation = {
        lat: 26.0667,
        lng: 50.5577
    }; 

    // Custom charging station icon
    const chargingIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    // Initialize Map
    function initMap() {
        // Create the map
        map = L.map('map').setView([currentLocation.lat, currentLocation.lng], 12);
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Add geocoder control for address searching
        L.Control.geocoder({
            placeholder: "Search for a location...",
            errorMessage: "Nothing found.",
            defaultMarkGeocode: false
        })
        .on('markgeocode', function(e) {
            const { center, bbox } = e.geocode;
            document.getElementById('latitude').value = center.lat;
            document.getElementById('longitude').value = center.lng;
            loadChargePoints();
            map.fitBounds(bbox);
        })
        .addTo(map);
        
        // Add the marker layer to the map
        markerLayer.addTo(map);
        
        // Add markers for initial charge points
        updateMapMarkers(initialChargePoints);
        
        // Try to get user's current location
        getCurrentLocation();
        
        // Add click listener to set coordinates
        map.on('click', (e) => {
            const latLng = e.latlng;
            document.getElementById('latitude').value = latLng.lat;
            document.getElementById('longitude').value = latLng.lng;
            loadChargePoints();
        });
    }

    // Get User's Current Location
    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                currentLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                document.getElementById('latitude').value = currentLocation.lat;
                document.getElementById('longitude').value = currentLocation.lng;

                // Center the map on the user's current location
                map.setView([currentLocation.lat, currentLocation.lng], 12);

                // Optionally load charge points near the current location
                loadChargePoints();
            }, showError);
        } else {
            console.error('Geolocation is not supported by this browser.');
        }
    }

    // Clear all filters
    function clearAllFilters() {
        document.getElementById('location').value = '';
        document.getElementById('minPrice').value = '';
        document.getElementById('maxPrice').value = '';
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        document.getElementById('availableOnly').checked = true;
        loadChargePoints();
    }

    document.getElementById('useCurrentLocation').addEventListener('click', getCurrentLocation);
    document.getElementById('clearFilters').addEventListener('click', clearAllFilters);

    // Error Handling for Geolocation
    function showError(error) {
        console.error('Geolocation error:', error);
    }

    // AJAX Filtering
    const searchForm = document.getElementById('searchForm');
    let debounceTimer;

    // Event Listeners
    searchForm.addEventListener('submit', e => {
        e.preventDefault();
        loadChargePoints();
    });

    // Handle all filter inputs
    const filterInputs = ['location', 'minPrice', 'maxPrice', 'latitude', 'longitude', 'availableOnly'];
    filterInputs.forEach(id => {
        const element = document.getElementById(id);
        element.addEventListener('input', () => {
            clearTimeout(debounceTimer);

            // If input is cleared, reset all filters
            if ((id === 'location' && element.value === '') ||
                (['minPrice', 'maxPrice', 'latitude', 'longitude'].includes(id) && element.value === '')) {
                clearAllFilters();
                return;
            }

            debounceTimer = setTimeout(loadChargePoints, 300);
        });
    });

    // Load Charge Points
    async function loadChargePoints() {
        try {
            const params = new URLSearchParams();
            params.append('ajax', 'search');

            // Only include non-empty filters
            const location = document.getElementById('location').value;
            const minPrice = document.getElementById('minPrice').value;
            const maxPrice = document.getElementById('maxPrice').value;
            const latitude = document.getElementById('latitude').value;
            const longitude = document.getElementById('longitude').value;

            if (location) params.append('location', location);
            if (minPrice) params.append('minPrice', minPrice);
            if (maxPrice) params.append('maxPrice', maxPrice);
            if (latitude) params.append('latitude', latitude);
            if (longitude) params.append('longitude', longitude);

            params.append('availableOnly', document.getElementById('availableOnly').checked ? 1 : 0);

            const response = await fetch(`chargePoint.php?${params}`);
            if (!response.ok) throw new Error('Network response was not ok');

            const points = await response.json();
            updateChargePointsList(points);
            updateMapMarkers(points);

        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Update Results List
    function updateChargePointsList(points) {
        const list = document.getElementById('chargePointsList');
        list.innerHTML = '';

        if (points.length === 0) {
            list.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-lightning-charge-fill text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No charge points found</h5>
                <p class="text-muted">Try adjusting your filters</p>
                <button class="btn btn-outline-primary" onclick="clearAllFilters()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                </button>
            </div>
        `;
            return;
        }

        points.forEach(point => {
            const col = document.createElement('div');
            col.className = 'col';
            col.innerHTML = `
            <div class="card h-100 shadow-sm">
                ${point.image_path ? 
                    `<img src="${point.image_path}" class="card-img-top" style="height: 200px; object-fit: cover;">` : 
                    `<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="bi bi-lightning-charge text-muted" style="font-size: 3rem;"></i>
                    </div>`
                }
                <div class="card-body">
                    <h5 class="card-title">${escapeHtml(point.address)}</h5>
                    <div class="card-text">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${escapeHtml(point.postcode)}</span>
                            <span class="badge bg-${point.is_available ? 'success' : 'danger'}">
                                ${point.is_available ? 'Available' : 'Unavailable'}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">£${point.price_per_kwh.toFixed(2)}/kWh</span>
                            <small class="text-muted">Lat: ${point.latitude}, Lng: ${point.longitude}</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <a href="#" class="btn btn-primary w-100"
                       data-bs-toggle="modal"
                       data-bs-target="#bookingModal"
                       data-charge-point-id="${point.id}"
                       data-price="${point.price_per_kwh}"
                       data-address="${escapeHtml(point.address)}"
                       data-postcode="${escapeHtml(point.postcode)}"
                       data-lat="${point.latitude}"
                       data-lng="${point.longitude}">
                        <i class="bi bi-calendar-check"></i> Book Now
                    </a>
                </div>
            </div>
        `;
            list.appendChild(col);
        });
    }

    // Update Map Markers
    function updateMapMarkers(points) {
        // Clear existing markers
        markerLayer.clearLayers();
        markers = [];

        // Add new markers
        points.forEach(point => {
            const latLng = [parseFloat(point.latitude), parseFloat(point.longitude)];
            const marker = L.marker(latLng, {
                icon: chargingIcon,
                title: point.address
            });
            
            // Create popup content
            const popupContent = `
                <div class="popup-content">
                    <h6>${escapeHtml(point.address)}</h6>
                    <p class="mb-1">${escapeHtml(point.postcode)}</p>
                    <p class="mb-1">£${point.price_per_kwh.toFixed(2)}/kWh</p>
                    <button class="btn btn-sm btn-primary mt-2 book-from-map" 
                            data-charge-point-id="${point.id}"
                            data-price="${point.price_per_kwh}"
                            data-address="${escapeHtml(point.address)}"
                            data-postcode="${escapeHtml(point.postcode)}"
                            data-lat="${point.latitude}"
                            data-lng="${point.longitude}">
                        Book Now
                    </button>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            
            // Add click event to "Book Now" button in popup
            marker.on('popupopen', function() {
                document.querySelectorAll('.book-from-map').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
                        
                        // Set data for the modal
                        document.getElementById('modalChargePointId').value = this.dataset.chargePointId;
                        document.getElementById('modalChargePointPrice').textContent = parseFloat(this.dataset.price).toFixed(2);
                        document.getElementById('modalChargePointAddress').textContent = this.dataset.address;
                        document.getElementById('modalChargePointPostcode').textContent = this.dataset.postcode;
                        
                        const bookingModal = document.getElementById('bookingModal');
                        bookingModal.dataset.pricePerKwh = this.dataset.price;
                        
                        // Set initial start and end times (1 hour apart)
                        const now = new Date();
                        const endTime = new Date(now.getTime() + 3600000); // 1 hour later
                        document.getElementById('startTime').value = formatDateTime(now);
                        document.getElementById('endTime').value = formatDateTime(endTime);
                        
                        // Calculate total cost
                        calculateTotalCost();
                        
                        // Show the modal
                        modal.show();
                    });
                });
            });
            
            markerLayer.addLayer(marker);
            markers.push(marker);
        });

        // Center map on markers if available
        if (points.length > 0) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1)); // Add 10% padding
        } else {
            map.setView([currentLocation.lat, currentLocation.lng], 12);
        }
    }

    // Add modal show handler
    document.getElementById('bookingModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        if (!button) return; // Skip if opened programmatically

        // Read details from button's data attributes
        const chargePointId = button.dataset.chargePointId;
        const pricePerKwh = parseFloat(button.dataset.price) || 0;
        const address = button.dataset.address;
        const postcode = button.dataset.postcode;
        const lat = button.dataset.lat;
        const lng = button.dataset.lng;

        // Set hidden input and display fields in the modal
        this.querySelector('#modalChargePointId').value = chargePointId;
        // Use the correct element ID from your markup:
        this.querySelector('#modalChargePointPrice').textContent = pricePerKwh.toFixed(2);
        this.dataset.pricePerKwh = pricePerKwh;
        this.querySelector('#modalChargePointAddress').textContent = address;
        this.querySelector('#modalChargePointPostcode').textContent = postcode;

        // Set initial start and end times (1 hour apart)
        const now = new Date();
        const endTime = new Date(now.getTime() + 3600000); // 1 hour later
        this.querySelector('#startTime').value = formatDateTime(now);
        this.querySelector('#endTime').value = formatDateTime(endTime);

        // Recalculate the total cost
        calculateTotalCost();
    });

    // Add time change listeners
    document.querySelectorAll('#startTime, #endTime').forEach(input => {
        input.addEventListener('change', calculateTotalCost);
    });

    // Helper function to format date as YYYY-MM-DDTHH:MM for datetime-local input
    function formatDateTime(date) {
        const pad = num => String(num).padStart(2, '0');
        return date.getFullYear() + '-' +
            pad(date.getMonth() + 1) + '-' +
            pad(date.getDate()) + 'T' +
            pad(date.getHours()) + ':' +
            pad(date.getMinutes());
    }

    function calculateTotalCost() {
        const modal = document.getElementById('bookingModal');
        const pricePerKwh = parseFloat(modal.dataset.pricePerKwh);
        const start = new Date(modal.querySelector('#startTime').value);
        const end = new Date(modal.querySelector('#endTime').value);

        if (!start || !end || end <= start) {
            modal.querySelector('#totalCost').value = 'Invalid time range';
            return;
        }

        const hours = (end - start) / 3600000; // Convert milliseconds to hours
        const totalCost = pricePerKwh * hours; // Cost calculation: price per kWh * hours

        modal.querySelector('#totalCost').value = `£${totalCost.toFixed(2)}`;
        modal.querySelector('#totalCostValue').value = totalCost.toFixed(2);
    }

    // HTML Escape Function
    function escapeHtml(str) {
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Add event listener for the location input field
    document.getElementById('location').addEventListener('input', function () {
        const location = this.value;

        if (location.trim() !== '') {
            // Use Leaflet Geocoder to find the location
            L.Control.Geocoder.nominatim().geocode(location, function (results) {
                if (results.length > 0) {
                    const { center } = results[0];
                    document.getElementById('latitude').value = center.lat;
                    document.getElementById('longitude').value = center.lng;

                    // Center the map on the searched location
                    map.setView([center.lat, center.lng], 12);

                    // Load charge points near the searched location
                    loadChargePoints();
                } else {
                    console.error('Location not found.');
                }
            });
        }
    });

    // Make clearAllFilters available globally
    window.clearAllFilters = clearAllFilters;

    // Initialize the map
    initMap();
});
