document.addEventListener('DOMContentLoaded', function() {
    // Get isApproved value from the data attribute set in the HTML
    const isApproved = document.getElementById('homeowner-data').dataset.approved === 'true';
    
    // Map for creating new charge point
    const createMap = document.getElementById('create-map');
    if (createMap) {
        const map = L.map('create-map').setView([51.505, -0.09], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
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
            
            // If GeoCoder is available
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
        
        document.getElementById('address').addEventListener('blur', searchAddress);
        document.getElementById('postcode').addEventListener('blur', searchAddress);
        
        // Make sure map renders correctly
        setTimeout(function() {
            map.invalidateSize();
        }, 100);
    }
    
    // Map for viewing existing charge point
    const viewMap = document.getElementById('view-map');
    if (viewMap) {
        // Get charge point data from the data attribute
        const chargePointEl = document.getElementById('charge-point-data');
        if (chargePointEl) {
            const lat = parseFloat(chargePointEl.dataset.lat);
            const lng = parseFloat(chargePointEl.dataset.lng);
            const address = chargePointEl.dataset.address;
            
            const map = L.map('view-map').setView([lat, lng], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`<b>${address}</b><br>Your Charge Point`).openPopup();
            
            // Make sure map renders correctly
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        }
    }
    
    // Edit modal map
    let editMap = null;
    let editMarker = null;
    
    // Edit Modal Handler
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            // Populate form fields with data from the button's data attributes
            document.getElementById('editId').value = button.dataset.id;
            document.getElementById('editAddress').value = button.dataset.address;
            document.getElementById('editPostcode').value = button.dataset.postcode;
            document.getElementById('editLat').value = button.dataset.lat;
            document.getElementById('editLng').value = button.dataset.lng;
            document.getElementById('editPrice').value = button.dataset.price;
            document.getElementById('editExistingImage').value = button.dataset.image;
            
            // Set availability checkbox and handle approval status
            const availableCheckbox = document.getElementById('editAvailable');
            availableCheckbox.checked = (button.dataset.available === '1' || button.dataset.available === 'true');
            
            if (!isApproved) {
                availableCheckbox.checked = false;
                // Add a hidden input to ensure 'is_available' is sent as 0
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'is_available';
                hiddenInput.value = '0';
                availableCheckbox.parentNode.appendChild(hiddenInput);
            }

            // Set the homeowner value
            document.getElementById('editHomeownerId').value = button.dataset.homeowner;

            // Handle image display
            const imageContainer = document.getElementById('currentImage');
            if (button.dataset.image) {
                imageContainer.innerHTML = `
                    <p>Current Image:</p>
                    <img src="${button.dataset.image}" class="img-thumbnail" style="max-width: 200px;">
                `;
            } else {
                imageContainer.innerHTML = '<p>No current image</p>';
            }
            
            // Initialize edit map
            setTimeout(() => {
                const lat = parseFloat(button.dataset.lat);
                const lng = parseFloat(button.dataset.lng);
                const mapContainer = document.getElementById('edit-map');
                
                if (mapContainer && !editMap) {
                    editMap = L.map('edit-map').setView([lat, lng], 15);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(editMap);
                    
                    editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);
                    
                    // Update coordinates when marker is dragged
                    editMarker.on('dragend', function() {
                        const pos = editMarker.getLatLng();
                        document.getElementById('editLat').value = pos.lat.toFixed(8);
                        document.getElementById('editLng').value = pos.lng.toFixed(8);
                    });
                    
                    // Update marker position when map is clicked
                    editMap.on('click', function(e) {
                        editMarker.setLatLng(e.latlng);
                        document.getElementById('editLat').value = e.latlng.lat.toFixed(8);
                        document.getElementById('editLng').value = e.latlng.lng.toFixed(8);
                    });
                } else if (editMap) {
                    // If map already exists, update view and marker position
                    editMap.setView([lat, lng], 15);
                    editMarker.setLatLng([lat, lng]);
                }
                
                // Make sure map renders correctly after modal is shown
                editMap.invalidateSize();
            }, 300);
        });
        
        // Clean up map when modal is hidden
        editModal.addEventListener('hidden.bs.modal', function() {
            if (editMap) {
                // Don't destroy the map, just make sure it's ready for next time
                editMap.invalidateSize();
            }
        });
    }
});
