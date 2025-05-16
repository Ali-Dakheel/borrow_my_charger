document.addEventListener('DOMContentLoaded', function() {
    // User Growth Chart
    const userCtx = document.getElementById('userGrowthChart').getContext('2d');
    const userGrowthChart = new Chart(userCtx, {
        type: 'line',
        data: {
            labels: dashboardData.userGrowthLabels,
            datasets: [{
                label: 'New Users',
                data: dashboardData.userGrowthData,
                borderColor: 'rgba(13, 110, 253, 1)',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                title: { display: true, text: 'User Growth (Last 12 Months)' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: dashboardData.revenueLabels,
            datasets: [{
                label: 'Monthly Revenue (£)',
                data: dashboardData.revenueData,
                backgroundColor: 'rgba(25, 135, 84, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                title: { display: true, text: 'Monthly Revenue (Last 6 Months)' }
            }
        }
    });
    
    // Bookings Chart
    const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
    const bookingsChart = new Chart(bookingsCtx, {
        type: 'line',
        data: {
            labels: dashboardData.bookingsLabels,
            datasets: [{
                label: 'Approved',
                data: dashboardData.approvedBookingsData,
                borderColor: 'rgba(25, 135, 84, 1)',
                backgroundColor: 'transparent'
            }, {
                label: 'Pending',
                data: dashboardData.pendingBookingsData,
                borderColor: 'rgba(255, 193, 7, 1)',
                backgroundColor: 'transparent'
            }, {
                label: 'Declined',
                data: dashboardData.declinedBookingsData,
                borderColor: 'rgba(220, 53, 69, 1)',
                backgroundColor: 'transparent'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                title: { display: true, text: 'Booking Status Trends (Last 30 Days)' }
            }
        }
    });
    
    // Refresh activity button
    document.getElementById('refreshActivity').addEventListener('click', function() {
        const button = this;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        button.disabled = true;
        
        // Simulating AJAX fetch - in production, replace with actual AJAX call
        setTimeout(() => {
            button.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh';
            button.disabled = false;
        }, 1000);
    });
});
