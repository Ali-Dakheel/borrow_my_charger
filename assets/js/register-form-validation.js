document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    // Username validation
    username.addEventListener('input', function() {
        if (this.value) {
            if (this.value.length < 4) {
                this.setCustomValidity('Username must be at least 4 characters');
            } else {
                this.setCustomValidity('');
            }
        } else {
            this.setCustomValidity('Username is required');
        }
    });
    
    // Password validation
    password.addEventListener('input', function() {
        if (this.value.length > 0 && this.value.length < 6) {
            this.setCustomValidity('Password must be at least 6 characters');
        } else {
            this.setCustomValidity('');
            // Update confirm password validation
            if (confirmPassword.value) {
                if (this.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
        }
    });
    
    // Confirm password validation
    confirmPassword.addEventListener('input', function() {
        if (password.value !== this.value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Form submit validation
    form.addEventListener('submit', function(e) {
        // Username validation
        if (username.value.length < 4) {
            e.preventDefault();
            username.setCustomValidity('Username must be at least 4 characters');
            username.reportValidity();
            return;
        }
        
        // Password validation
        if (password.value.length < 6) {
            e.preventDefault();
            password.setCustomValidity('Password must be at least 6 characters');
            password.reportValidity();
            return;
        }
        
        // Check if passwords match
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            confirmPassword.setCustomValidity('Passwords do not match');
            confirmPassword.reportValidity();
            return;
        }
    });
});
