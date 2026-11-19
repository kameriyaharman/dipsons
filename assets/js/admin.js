// Admin: WhatsApp templates, reminder logging
(function () {
  var wa = document.getElementById('wa');
  if (wa) {
    var sel = document.getElementById('watpl'), txt = document.getElementById('watext');
    sel.addEventListener('change', function () {
      var o = sel.options[sel.selectedIndex];
      if (o.value !== 'custom') txt.value = o.dataset.text; else { txt.value = ''; txt.focus(); }
    });
    document.getElementById('wasend').addEventListener('click', function () {
      var msg = txt.value.trim();
      window.open('https://wa.me/' + wa.dataset.mobile + (msg ? '?text=' + encodeURIComponent(msg) : ''), '_blank', 'noopener');
      var fd = new FormData();
      fd.append('_csrf', wa.dataset.csrf); fd.append('action', 'wa_log');
      fd.append('template', sel.options[sel.selectedIndex].text);
      fetch(location.pathname, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function () { setTimeout(function () { location.reload(); }, 800); });
    });
  }

  // reminders: clicking WhatsApp marks reminder as sent
  var csrf = document.querySelector('input[name=_csrf]');
  document.querySelectorAll('[data-remind]').forEach(function (a) {
    a.addEventListener('click', function () {
      var d = JSON.parse(a.dataset.remind), fd = new FormData();
      fd.append('_csrf', csrf ? csrf.value : ''); fd.append('ajax', '1');
      fd.append('vid', d.vid); fd.append('kind', d.kind); fd.append('due', d.due);
      fetch('/admin/reminders', { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) {
        if (r.ok) { var c = a.closest('tr').querySelector('[data-sent]'); if (c) c.textContent = 'Sent now ✓'; }
      });
    });
  });
})();
