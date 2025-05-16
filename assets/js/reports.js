document.addEventListener('DOMContentLoaded', function() {
    // Copy query buttons
    document.querySelectorAll('.copy-query').forEach(button => {
        button.addEventListener('click', function() {
            const queryText = this.closest('.card-body').querySelector('code').textContent;
            navigator.clipboard.writeText(queryText).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check2"></i> Copied!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    });
});
