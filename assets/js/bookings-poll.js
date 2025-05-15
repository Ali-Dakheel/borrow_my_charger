(function () {
  var POLL_INTERVAL = 10000; // milliseconds

  function escapeHtml(s) {
    return (s + "").replace(/[&<>"']/g, function (c) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[c];
    });
  }

  function capitalize(s) {
    return s.charAt(0).toUpperCase() + s.slice(1);
  }

  function statusColor(s) {
    return s === "approved"
      ? "success"
      : s === "pending"
      ? "warning"
      : s === "cancelled"
      ? "secondary"
      : "danger";
  }

  function formatDate(dtString, opts) {
    var dt = new Date(dtString);
    return dt.toLocaleDateString("en-GB", opts);
  }

  function formatTime(dtString, opts) {
    var dt = new Date(dtString);
    return dt.toLocaleTimeString("en-GB", opts);
  }

  function computeDuration(start, end) {
    var diff = new Date(end) - new Date(start);
    var mins = Math.floor(diff / 60000);
    var h = Math.floor(mins / 60),
      m = mins % 60;
    return h + "h " + m + "m";
  }

  function renderBookings(bookings) {
    var tbody = document.querySelector("table.table-hover tbody");
    if (!tbody) return;

    tbody.innerHTML = bookings
      .map(function (b) {
        return (
          "" +
          '<tr data-id="' +
          b.id +
          '">' +
          "<td>" +
          '<div class="fw-bold">' +
          escapeHtml(b.charge_point_address) +
          "</div>" +
          '<small class="text-muted">' +
          escapeHtml(b.charge_point_postcode) +
          "</small><br>" +
          '<div class="text-muted small">£' +
          parseFloat(b.price_per_kwh).toFixed(2) +
          "/kWh</div>" +
          "</td>" +
          "<td>" +
          "<div>" +
          formatDate(b.start_time, {
            weekday: "short",
            month: "short",
            day: "numeric",
          }) +
          "</div>" +
          '<div class="text-muted small">' +
          formatTime(b.start_time, {
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
          }) +
          " – " +
          formatTime(b.end_time, {
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
          }) +
          "</div>" +
          "</td>" +
          "<td>" +
          computeDuration(b.start_time, b.end_time) +
          "</td>" +
          '<td class="fw-bold">£' +
          parseFloat(b.total_cost).toFixed(2) +
          "</td>" +
          '<td><span class="badge rounded-pill bg-' +
          statusColor(b.status) +
          '">' +
          capitalize(b.status) +
          "</span></td>" +
          "<td>" +
          '<div class="d-flex gap-2">' +
          // View Details Button
          '<a href="booking_detail.php?id=' + b.id + '" class="btn btn-sm btn-primary">' +
          '<i class="bi bi-eye"></i> View Details' +
          '</a>' +
          
          // Cancel/Uncancel Button (only show if pending or cancelled)
          ((b.status === 'pending' || b.status === 'cancelled') ? 
          '<button class="btn btn-sm ' + (b.status === 'cancelled' ? 'btn-outline-success' : 'btn-outline-danger') + '"' +
          ' data-bs-toggle="modal" data-bs-target="#statusModal' + b.id + '">' +
          '<i class="bi ' + (b.status === 'cancelled' ? 'bi-arrow-counterclockwise' : 'bi-x-circle') + '"></i> ' +
          (b.status === 'cancelled' ? 'Uncancel' : 'Cancel') +
          '</button>' : '') +
          
          // Review Button
          '<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal' + b.id + '">' +
          '<i class="bi bi-chat-left-text"></i> Review' +
          '</button>' +
          '</div>' +
          
          // Status Modal (Cancel/Uncancel)
          '<div class="modal fade" id="statusModal' + b.id + '" tabindex="-1">' +
          '<div class="modal-dialog modal-dialog-centered">' +
          '<div class="modal-content">' +
          '<div class="modal-header">' +
          '<h5 class="modal-title">' + (b.status === 'cancelled' ? 'Restore Booking' : 'Cancel Booking') + '</h5>' +
          '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
          '</div>' +
          '<div class="modal-body">' +
          'Are you sure you want to ' + (b.status === 'cancelled' ? 'restore' : 'cancel') + ' this booking?' +
          '</div>' +
          '<div class="modal-footer">' +
          '<form action="booking.php" method="post">' +
          '<input type="hidden" name="action" value="cancel_booking">' +
          '<input type="hidden" name="booking_id" value="' + b.id + '">' +
          '<input type="hidden" name="current_status" value="' + b.status + '">' +
          '<button type="submit" class="btn ' + (b.status === 'cancelled' ? 'btn-success' : 'btn-danger') + '">' +
          'Yes, ' + (b.status === 'cancelled' ? 'Restore' : 'Cancel') +
          '</button>' +
          '</form>' +
          '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>' +
          '</div>' +
          '</div>' +
          '</div>' +
          '</div>' +
          
          // Review Modal
          '<div class="modal fade" id="reviewModal' + b.id + '" tabindex="-1">' +
          '<div class="modal-dialog modal-dialog-centered">' +
          '<div class="modal-content">' +
          '<div class="modal-header">' +
          '<h5 class="modal-title">Submit Review</h5>' +
          '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
          '</div>' +
          '<div class="modal-body">' +
          '<form action="booking.php" method="post">' +
          '<input type="hidden" name="action" value="submit_review">' +
          '<input type="hidden" name="booking_id" value="' + b.id + '">' +
          '<div class="mb-3">' +
          '<label for="rating' + b.id + '" class="form-label">Rating (1-5)</label>' +
          '<input type="number" min="1" max="5" class="form-control" id="rating' + b.id + '" name="rating" required>' +
          '</div>' +
          '<div class="mb-3">' +
          '<label for="reviewText' + b.id + '" class="form-label">Review</label>' +
          '<textarea name="comment" id="reviewText' + b.id + '" class="form-control" placeholder="Write your review..." required></textarea>' +
          '</div>' +
          '<button type="submit" class="btn btn-primary">Submit</button>' +
          '</form>' +
          '</div>' +
          '<div class="modal-footer">' +
          '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
          '</div>' +
          '</div>' +
          '</div>' +
          '</div>' +
          "</td>" +
          "</tr>"
        );
      })
      .join("");

    // Rebind Bootstrap modals if needed
    if (typeof bootstrap !== 'undefined') {
      var modals = document.querySelectorAll('.modal');
      modals.forEach(function(modal) {
        new bootstrap.Modal(modal);
      });
    }
  }

  function fetchBookings() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/booking.php?ajax=1&_=" + new Date().getTime(), true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          try {
            var data = JSON.parse(xhr.responseText);
            renderBookings(data);
          } catch (e) {
            console.error("Invalid JSON", e);
          }
        } else {
          console.error("Polling error:", xhr.status, xhr.statusText);
        }
      }
    };
    xhr.send();
  }

  // start polling
  document.addEventListener("DOMContentLoaded", function () {
    fetchBookings();
    setInterval(fetchBookings, POLL_INTERVAL);
  });
})();