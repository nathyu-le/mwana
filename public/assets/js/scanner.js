(() => {
  const form = document.getElementById('scanner');
  if (!form) return;
  const input = form.querySelector('[type=file]');
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
  async function fileDigest(file) {
    if (file.size <= 4 * 1024 * 1024) {
      const digest = await crypto.subtle.digest('SHA-256', await file.arrayBuffer());
      return Array.from(new Uint8Array(digest), b => b.toString(16).padStart(2, '0')).join('');
    }
    const worker = new Worker(form.dataset.hashWorker);
    try {
      return await new Promise((resolve, reject) => {
        worker.onerror = reject;
        worker.onmessage = ({data}) => {
          if (data.failed) reject(new Error('File read failed'));
          else if (data.digest) resolve(data.digest);
          else hint.textContent = file.name + ' · ' + data.percent + '%';
        };
        worker.postMessage(file);
      });
    } finally {
      worker.terminate();
    }
  }
  async function scan(file) {
    if (sending) return;
    if (!file) return;
    if (!file.size || file.size > Number(form.dataset.maxBytes)) {
      reset(form.dataset.size);
      return;
    }
    sending = true;
    form.classList.add('busy');
    form.setAttribute('aria-busy', 'true');
    title.textContent = form.dataset.scanning;
    hint.textContent = file.name;
    try {
      form.elements.sha256.value = await fileDigest(file);
      form.elements.filename.value = file.name;
      form.elements.size.value = String(file.size);
      form.submit();
    } catch {
      reset(form.dataset.failed);
    }
  }
  form.addEventListener('submit', event => event.preventDefault());
  if (!window.crypto?.subtle) {
    hint.textContent = form.dataset.failed;
    return;
  }
  input.disabled = false;
  input.addEventListener('change', () => scan(input.files[0]));
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
        scan(event.dataTransfer.files[0]);
      }
    });
  }
  window.addEventListener('pageshow', event => { if (event.persisted) reset(); });
  form.classList.add('enhanced');
})();
