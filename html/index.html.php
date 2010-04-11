<?php include(APPROOT.'/html/header.html.php'); ?>
<?php include(APPROOT.'/html/component/menu.html.php'); ?>
<?php include(APPROOT.'/html/component/pager.html.php'); ?>
<div id="images">
<?php foreach ($items as $item): ?>
	<div class="image">
		<a class="thumb" href="<?php print preg_replace('#/+#', '/', $item->path); ?>"><img alt="" src="<?php print preg_replace('#/+#', '/', $item->thumb); ?>"/></a>
		<span class="nick"><?php print htmlspecialchars($item->nick); ?></span>
		<span class="time"><?php print htmlspecialchars($item->ctime); ?></span>
		<?php if (trim($item->comment) !== ''): ?>
		<span class="comment"><?php print htmlspecialchars($item->comment); ?></span>
		<?php endif; ?>
		<a class="orig_url" href="<?php print htmlspecialchars($item->original_url); ?>">original link</a>
	</div>
<?php endforeach; ?>
</div>
<?php include(APPROOT.'/html/footer.html.php'); ?>
