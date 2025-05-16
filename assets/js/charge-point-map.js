document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map centered on Bahrain by default
    const defaultLocation = [26.0667, 50.5577]; // Bahrain coordinates
    const map = L.map('location-map').setView(defaultLocation, 13);
    
    // Add the OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Create location button
    const locationButton = L.control({ position: 'topleft' });
    locationButton.onAdd = function() {
        const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
        div.innerHTML = '<a href="#" title="Find my location" style="display:flex; align-items:center; justify-content:center; width:30px; height:30px;"><i class="bi bi-geo-alt"></i></a>';
        div.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            getUserLocation();
            return false;
        };
        return div;
    };
    locationButton.addTo(map);
    
    // Function to get user location
    function getUserLocation() {
        if (navigator.geolocation) {
            // Show loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'location-loading';
            loadingDiv.style.position = 'absolute';
            loadingDiv.style.top = '50%';
            loadingDiv.style.left = '50%';
            loadingDiv.style.transform = 'translate(-50%, -50%)';
            loadingDiv.style.background = 'rgba(255,255,255,0.8)';
            loadingDiv.style.padding = '10px';
            loadingDiv.style.borderRadius = '5px';
            loadingDiv.style.zIndex = '1000';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-2">Getting your location...</div>';
            document.getElementById('location-map').appendChild(loadingDiv);
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Remove loading indicator
                    const loadingDiv = document.getElementById('location-loading');
                    if (loadingDiv) loadingDiv.remove();
                    
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    map.setView([lat, lng], 17);
                    updateMarker(lat, lng);
                    
                    // Try to reverse geocode the position to get address
                    if (typeof L.Control.Geocoder !== 'undefined') {
                        const geocoder = L.Control.Geocoder.nominatim();
                        geocoder.reverse(
                            {lat: lat, lng: lng},
                            map.getZoom(),
                            function(results) {
                                if (results && results.length > 0) {
                                    const address = results[0].name;
                                    document.getElementById('address').value = address;
                                    
                                    // Try to extract postcode if available
                                    if (results[0].properties && results[0].properties.address) {
                                        const postcode = results[0].properties.address.postcode;
                                        if (postcode) {
                                            document.getElementById('postcode').value = postcode;
                                        }
                                    }
                                }
                            }
                        );
                    }
                },
                function(error) {
                    // Remove loading indicator
                    const loadingDiv = document.getElementById('location-loading');
                    if (loadingDiv) loadingDiv.remove();
                    
                    // Show error notification
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-warning alert-dismissible fade show';
                    errorDiv.style.position = 'absolute';
                    errorDiv.style.bottom = '10px';
                    errorDiv.style.left = '10px';
                    errorDiv.style.right = '10px';
                    errorDiv.style.zIndex = '1000';
                    errorDiv.innerHTML = `
                        <strong>Location Error:</strong> ${error.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.getElementById('location-map').appendChild(errorDiv);
                    
                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        if (errorDiv && errorDiv.parentNode) {
                            errorDiv.parentNode.removeChild(errorDiv);
                        }
                    }, 5000);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }
    
    // Initialize marker with null value
    let marker;
    
    // Function to update marker position
    function updateMarker(lat, lng) {
        // Update form fields
        document.getElementById('latitude').value = lat.toFixed(8);
        document.getElementById('longitude').value = lng.toFixed(8);
        
        // Update or create marker
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                updateMarker(position.lat, position.lng);
            });
        }
        
        // Try to reverse geocode whenever marker position changes
        if (typeof L.Control.Geocoder !== 'undefined') {
            const geocoder = L.Control.Geocoder.nominatim();
            geocoder.reverse(
                {lat: lat, lng: lng},
                map.getZoom(),
                function(results) {
                    if (results && results.length > 0) {
                        const address = results[0].name;
                        if (!document.getElementById('address').value) {
                            document.getElementById('address').value = address;
                        }
                        
                        // Try to extract postcode if available
                        if (results[0].properties && results[0].properties.address) {
                            const postcode = results[0].properties.address.postcode;
                            if (postcode && !document.getElementById('postcode').value) {
                                document.getElementById('postcode').value = postcode;
                            }
                        }
                    }
                }
            );
        }
    }
    
    // Handle map clicks to place/move marker
    map.on('click', function(e) {
        updateMarker(e.latlng.lat, e.latlng.lng);
    });
    
    // Try to use address/postcode to center map when they change
    function searchAddress() {
        const address = document.getElementById('address').value;
        const postcode = document.getElementById('postcode').value;
        
        if (!address && !postcode) return;
        
        const searchTerm = address + ' ' + postcode;
        
        // If GeoCoder is available (from control.GeoCoder.js)
        if (typeof L.Control.Geocoder !== 'undefined') {
            const geocoder = L.Control.Geocoder.nominatim();
            geocoder.geocode(searchTerm, function(results) {
                if (results && results.length > 0) {
                    const result = results[0];
                    map.setView([result.center.lat, result.center.lng], 16);
                    updateMarker(result.center.lat, result.center.lng);
                }
            });
        }
    }
    
    // Add event listeners to address and postcode fields
    document.getElementById('address').addEventListener('blur', searchAddress);
    document.getElementById('postcode').addEventListener('blur', searchAddress);
    
    // Make sure map renders correctly
    setTimeout(function() {
        map.invalidateSize();
        
        // Ask user for location automatically on page load with a small delay
        setTimeout(() => {
            showLocationPrompt();
        }, 500);
    }, 100);
    
    // Function to show a custom location prompt
    function showLocationPrompt() {
        const promptDiv = document.createElement('div');
        promptDiv.id = 'location-prompt';
        promptDiv.className = 'card position-absolute';
        promptDiv.style.top = '50%';
        promptDiv.style.left = '50%';
        promptDiv.style.transform = 'translate(-50%, -50%)';
        promptDiv.style.zIndex = '1000';
        promptDiv.style.width = '80%';
        promptDiv.style.maxWidth = '400px';
        
        promptDiv.innerHTML = `
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Allow Location Access</h5>
            </div>
            <div class="card-body">
                <p>To accurately place your charge point, we need to access your location.</p>
                <p>This will help potential customers find your charge point more easily.</p>
                <div class="d-flex justify-content-between">
                    <button id="deny-location" class="btn btn-outline-secondary">Not Now</button>
                    <button id="allow-location" class="btn btn-primary">Allow</button>
                </div>
            </div>
        `;
        
        document.getElementById('location-map').appendChild(promptDiv);
        
        // Add event listeners to buttons
        document.getElementById('allow-location').addEventListener('click', function() {
            promptDiv.remove();
            getUserLocation();
        });
        
        document.getElementById('deny-location').addEventListener('click', function() {
            promptDiv.remove();
        });
    }
    
    // Form validation
    const form = document.getElementById('chargePointForm');
    const latitude = document.getElementById('latitude');
    const longitude = document.getElementById('longitude');
    const pricePerKwh = document.getElementById('price_per_kwh');
    
    form.addEventListener('submit', function(e) {
        // Location validation
        if (!latitude.value || !longitude.value) {
            e.preventDefault();
            const errorMsg = document.createElement('div');
            errorMsg.className = 'alert alert-danger mt-2';
            errorMsg.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Please select a location on the map';
            
            // Check if error message already exists
            if (!document.querySelector('.alert-danger')) {
                document.getElementById('location-map').parentNode.appendChild(errorMsg);
                
                // Scroll to error message
                errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Remove after 5 seconds
                setTimeout(() => {
                    if (errorMsg && errorMsg.parentNode) {
                        errorMsg.parentNode.removeChild(errorMsg);
                    }
                }, 5000);
            }
            return;
        }
        
        // Price validation
        if (pricePerKwh.value <= 0) {
            e.preventDefault();
            pricePerKwh.setCustomValidity('Please enter a valid price greater than 0');
            pricePerKwh.reportValidity();
            return;
        }
    });
    
    pricePerKwh.addEventListener('input', function() {
        if (this.value <= 0) {
            this.setCustomValidity('Please enter a valid price greater than 0');
        } else {
            this.setCustomValidity('');
        }
    });
});
