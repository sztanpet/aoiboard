	<script src="js/jquery-1.4.4.min.js?<?php print filemtime(APPROOT.'/js/jquery-1.4.4.min.js')?>"></script>
	<script src="js/jquery.cookie.js?<?php print filemtime(APPROOT.'/js/jquery.cookie.js')?>"></script>
	<?php if (isset($js_files) && is_array($js_files)): ?>
		<?php foreach ($js_files as $file): ?>
		<script src="<?php print htmlspecialchars($file).'?'.filemtime(APPROOT.'/'.trim($file, './')); ?>"></script>
		<?php endforeach; ?>
	<?php endif; ?>
</body>
</html>
