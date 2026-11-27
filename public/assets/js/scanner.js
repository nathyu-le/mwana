(() => {
  const form = document.getElementById('scanner');
  if (!form) return;
  const input = form.elements.sample;
  const title = document.getElementById('scan-title');
  const hint = document.getElementById('scan-hint');
  const defaultTitle = title.textContent;
  const defaultHint = hint.textContent;
  let sending = false;
  let dragDepth = 0;

  function reset(message = defaultHint) {
    sending = false;
    dragDepth = 0;
    form.classList.remove('busy', 'dragging');
    form.removeAttribute('aria-busy');
    input.value = '';
    title.textContent = defaultTitle;
    hint.textContent = message;
  }
  function scan() {
    if (sending) return;
    const file = input.files[0];
    if (!file) return;
    if (!file.size || file.size > 2 * 1024 * 1024) {
      reset(form.dataset.size);
      return;
    }
    form.requestSubmit();
  }
  form.addEventListener('submit', event => {
    if (sending) { event.preventDefault(); return; }
    sending = true;
    form.classList.add('busy');
    form.setAttribute('aria-busy', 'true');
    title.textContent = form.dataset.scanning;
    hint.textContent = input.files[0].name;
  });
  input.addEventListener('change', scan);
  for (const type of ['dragenter','dragover','dragleave','drop']) {
    form.addEventListener(type, event => {
      if (!event.dataTransfer.types.includes('Files')) return;
      event.preventDefault();
      if (sending) return;
      if (type === 'dragenter') {
        dragDepth++;
        form.classList.add('dragging');
        title.textContent = form.dataset.drop;
      } else if (type === 'dragleave') {
        dragDepth = Math.max(0, dragDepth - 1);
        if (!dragDepth) { form.classList.remove('dragging'); title.textContent = defaultTitle; }
      } else if (type === 'drop') {
        dragDepth = 0;
        form.classList.remove('dragging');
        if (event.dataTransfer.files.length !== 1) { reset(form.dataset.single); return; }
        try { input.files = event.dataTransfer.files; }
        catch { reset(form.dataset.picker); return; }
        scan();
      }
    });
  }
  window.addEventListener('pageshow', event => { if (event.persisted) reset(); });
  form.classList.add('enhanced');
})();
