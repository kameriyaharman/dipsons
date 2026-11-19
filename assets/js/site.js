// Show selected file names under upload boxes; disable submit buttons after click.
document.querySelectorAll('.upload input[type=file]').forEach(function (inp) {
  inp.addEventListener('change', function () {
    var box = inp.closest('form').querySelector('.filelist');
    if (!box) return;
    box.textContent = Array.from(inp.files).map(function (f) { return '📎 ' + f.name; }).join('   ');
  });
});
document.querySelectorAll('form').forEach(function (f) {
  f.addEventListener('submit', function () {
    var b = f.querySelector('button:not([type=button])');
    if (b && f.checkValidity()) { setTimeout(function () { b.disabled = true; b.style.opacity = .7; }, 0); }
  });
});
document.querySelectorAll('.nav a').forEach(function (a) {
  a.addEventListener('click', function () { document.body.classList.remove('menu-open'); });
});
