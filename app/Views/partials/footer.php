<footer class="site-footer" aria-label="<?= e(t('Phiên bản')) ?>"><span><?= e($settings['site_name']) ?> · v<?= UI_VERSION ?></span>
<?php if (!$user) require __DIR__.'/language.php'; ?>
</footer>
<?php if ($view === 'scanner'): ?>
<script src="<?= asset('js/scanner.js') ?>" defer></script>
<?php endif ?>
</body>
</html>
