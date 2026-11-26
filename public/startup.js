// The server already rendered the page. Only a browser refresh gets the entrance.
(() => {
  const navigation = performance.getEntriesByType('navigation')[0];
  if (navigation?.type !== 'reload' || matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const root = document.documentElement;
  const started = performance.now();
  root.classList.add('booting');

  function finish() {
    root.classList.remove('booting', 'revealing');
  }
  const deadline = setTimeout(finish, 2500);
  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      if (!root.classList.contains('booting')) return;
      root.classList.add('revealing');
      setTimeout(() => { clearTimeout(deadline); finish(); }, 240);
    }, Math.max(0, 650 - (performance.now() - started)));
  }, { once: true });
  window.addEventListener('pageshow', event => { if (event.persisted) finish(); });
})();
