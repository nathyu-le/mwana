(() => {
  const root = document.documentElement;
  const seconds = Number(root.dataset.reloadSeconds);
  const navigation = performance.getEntriesByType('navigation')[0];
  if (navigation?.type !== 'reload' || matchMedia('(prefers-reduced-motion: reduce)').matches ||
      !Number.isFinite(seconds) || seconds <= 0 || seconds > 30) return;

  const hold = seconds * 1000;
  const fade = 220;
  let revealTimer;
  let finishTimer;

  function finish() {
    clearTimeout(revealTimer);
    clearTimeout(finishTimer);
    root.classList.remove('booting', 'revealing');
  }

  root.classList.add('booting');
  // Also release the overlay if the document never finishes parsing.
  finishTimer = setTimeout(finish, hold + 2500);
  document.addEventListener('DOMContentLoaded', () => {
    if (!root.classList.contains('booting')) return;
    clearTimeout(finishTimer);
    revealTimer = setTimeout(() => {
      root.classList.add('revealing');
      finishTimer = setTimeout(finish, fade);
    }, hold);
  }, { once: true });
  window.addEventListener('pagehide', finish, { once: true });
})();
