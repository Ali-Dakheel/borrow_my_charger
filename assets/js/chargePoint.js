document.addEventListener('DOMContentLoaded', function() {
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
            document.getElementById('editAvailable').checked = (button.dataset.available === '1' || button.dataset.available === 'true');

            // Set the homeowner value
            const homeownerSelect = document.getElementById('editHomeownerId');
            if (homeownerSelect) {
                // For both hidden input and select, setting the value is the same:
                homeownerSelect.value = button.dataset.homeowner;
            }

            // Handle image display
            const imageContainer = document.getElementById('currentImage');
            if (button.dataset.image) {
                imageContainer.innerHTML = `
                    <div class="card mt-2 p-2 bg-light">
                        <div class="d-flex align-items-center">
                            <img src="${button.dataset.image}" class="img-thumbnail me-3" style="max-width: 100px; max-height: 100px;">
                            <div class="text-muted">
                                <small>Current image will be kept unless you upload a new one</small>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                imageContainer.innerHTML = '<div class="form-text">No current image</div>';
            }
        });
    }

    // Delete Modal Handler
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('deleteId').value = button.dataset.id;
        });
    }
});
