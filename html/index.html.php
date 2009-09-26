<?php include('./html/header.html.php'); ?>
<?php include('./html/component/menu.html.php'); ?>
<div class="top">
<?php include('./html/component/pager.html.php'); ?>
</div>
<div id="images">
<?php foreach (array_reverse($items) as $item): ?>
	<div class="image">
		<a class="thumb" href="<?php print $item->path; ?>"><img src="<?php print $item->thumb; ?>"/></a>
		<span class="nick"><?php print htmlspecialchars($item->nick); ?></span>
		<span class="time"><?php print htmlspecialchars($item->time); ?></span>
		<?php if (trim($item->comment) !== ''): ?>
		<span class="comment"><?php print htmlspecialchars($item->comment); ?></span>
		<?php endif; ?>
		<a class="orig_url" href="<?php print htmlspecialchars($item->original_url); ?>">original link</a>
	</div>
<?php endforeach; ?>
</div>
<div class="bottom">
<?php include('./html/component/pager.html.php'); ?>
</div>
<?php include('./html/footer.html.php'); ?>
