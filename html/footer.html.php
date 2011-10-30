	<?php if (isset($js_files) && is_array($js_files)): ?>
		<?php foreach ($js_files as $file): ?>
		<script src="<?php print htmlspecialchars($file).'?'.filemtime(APPROOT.'/'.trim($file, './')); ?>"></script>
		<?php endforeach; ?>
	<?php endif; ?>
</body>
</html>
