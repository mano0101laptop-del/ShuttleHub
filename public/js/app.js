// Shuttle Hub — shared front-end behaviour.
// Intentionally tiny: this app is server-rendered Blade, not an SPA, so the
// only cross-page JS need is the mobile sidebar toggle.
document.addEventListener('DOMContentLoaded', function () {
  var hamburger = document.getElementById('tb-hamburger');
  var sidebar   = document.getElementById('sidebar');
  var closeBtn  = document.getElementById('sb-close');
  var scrim     = document.getElementById('sb-scrim');

  if (!hamburger || !sidebar) return;

  function openSidebar() {
    sidebar.classList.add('open');
    if (scrim) scrim.classList.add('show');
    hamburger.setAttribute('aria-expanded', 'true');
  }

  function closeSidebar() {
    sidebar.classList.remove('open');
    if (scrim) scrim.classList.remove('show');
    hamburger.setAttribute('aria-expanded', 'false');
  }

  hamburger.addEventListener('click', openSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  if (scrim) scrim.addEventListener('click', closeSidebar);

  // Close the mobile sidebar automatically on desktop resize.
  window.addEventListener('resize', function () {
    if (window.innerWidth > 900) closeSidebar();
  });
});
