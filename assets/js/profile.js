// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    // Email validation function
    function validateEmail(email) {
        const regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        return regex.test(email);
    }
    
    // Username/Email validation
    username.addEventListener('input', function() {
        if (this.value) {
            if (!validateEmail(this.value)) {
                this.setCustomValidity('Please enter a valid email address');
            } else {
                this.setCustomValidity('');
            }
        } else {
            this.setCustomValidity('Email address is required');
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
        // Username/Email validation
        if (!validateEmail(username.value)) {
            e.preventDefault();
            username.setCustomValidity('Please enter a valid email address');
            username.reportValidity();
            return;
        }
        
        // If password is provided, validate both fields
        if (password.value || confirmPassword.value) {
            // Check password length
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
        }
    });
});
