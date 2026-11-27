importScripts('../vendor/js-sha256/sha256-0.11.1.js');

self.onmessage = async ({data: file}) => {
  try {
    const chunkSize = 4 * 1024 * 1024;
    const hash = self.sha256.create();
    let lastPercent = -1;
    for (let offset = 0; offset < file.size; offset += chunkSize) {
      const end = Math.min(offset + chunkSize, file.size);
      hash.update(await file.slice(offset, end).arrayBuffer());
      const percent = Math.floor(end / file.size * 100);
      if (percent !== lastPercent) {
        self.postMessage({percent});
        lastPercent = percent;
      }
    }
    self.postMessage({digest: hash.hex()});
  } catch {
    self.postMessage({failed: true});
  }
};
