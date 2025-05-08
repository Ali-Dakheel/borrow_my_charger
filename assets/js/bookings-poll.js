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
          "<td><!-- you can re-add your modal buttons here --></td>" +
          "</tr>"
        );
      })
      .join("");
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
