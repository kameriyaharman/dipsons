// Dipsons app: 4-step enquiry wizard, file labels, service worker
(function () {
  // file inputs: show selected names
  document.querySelectorAll('.up input[type=file]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var box = inp.closest('.up'), st = box.querySelector('[data-st]');
      if (inp.files.length) {
        box.classList.add('has');
        st.textContent = inp.files.length === 1 ? 'Uploaded · ' + inp.files[0].name : inp.files.length + ' files selected';
      } else { box.classList.remove('has'); st.textContent = 'Tap to take photo or upload'; }
    });
  });

  var wiz = document.getElementById('wiz');
  if (wiz) {
    var steps = wiz.querySelectorAll('.step'), cur = 1;
    var next = wiz.querySelector('[data-next]'), submit = wiz.querySelector('[data-submit]');
    var bar = wiz.querySelector('.sbar i'), no = wiz.querySelector('[data-stepno]');
    function show(n) {
      cur = n;
      steps.forEach(function (s) { s.classList.toggle('on', +s.dataset.step === n); });
      bar.style.width = (n * 25) + '%'; no.textContent = n;
      next.hidden = n === 4; submit.hidden = n !== 4;
      if (n === 4) review();
      window.scrollTo(0, 0);
    }
    function review() {
      var sv = Array.from(wiz.querySelectorAll('input[name="services[]"]:checked')).map(function (i) { return i.dataset.name; });
      wiz.querySelector('[data-r=services]').textContent = sv.join(', ') || '—';
      var t = wiz.querySelector('input[name=vehicle_type]:checked'), n = wiz.querySelector('input[name=vehicle_number]').value.toUpperCase();
      wiz.querySelector('[data-r=vehicle]').textContent = [t ? t.value : '', n].filter(Boolean).join(' · ') || '—';
      var c = 0; wiz.querySelectorAll('input[type=file]').forEach(function (f) { c += f.files.length; });
      wiz.querySelector('[data-r=docs]').textContent = c ? c + ' uploaded' : 'None (send later on WhatsApp)';
    }
    next.addEventListener('click', function () {
      if (cur === 1 && !wiz.querySelector('input[name="services[]"]:checked')) { alertBox('Please select at least one service'); return; }
      show(cur + 1);
    });
    wiz.querySelector('[data-back]').addEventListener('click', function (e) {
      if (cur > 1) { e.preventDefault(); show(cur - 1); }
    });
    wiz.querySelectorAll('input[name=pick_vehicle]').forEach(function (r) {
      r.addEventListener('change', function () {
        var v = JSON.parse(r.dataset.v);
        wiz.querySelector('input[name=vehicle_number]').value = v.n;
        wiz.querySelector('input[name=vehicle_model]').value = v.m || '';
        wiz.querySelector('input[name=insurance_expiry]').value = v.i || '';
        wiz.querySelector('input[name=puc_expiry]').value = v.p || '';
        var t = wiz.querySelector('input[name=vehicle_type][value="' + v.t + '"]'); if (t) t.checked = true;
      });
    });
    wiz.addEventListener('submit', function () { submit.disabled = true; submit.textContent = 'Sending…'; });
  }

  function alertBox(msg) {
    var t = document.createElement('div'); t.className = 'toast err'; t.textContent = msg;
    document.querySelector('.phone').prepend(t); window.scrollTo(0, 0);
    setTimeout(function () { t.remove(); }, 3500);
  }

  if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(function () {});
})();
