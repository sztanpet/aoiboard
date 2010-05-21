<?php include(APPROOT.'/html/header.html.php'); ?>
<?php include(APPROOT.'/html/component/menu.html.php'); ?>
<?php include(APPROOT.'/html/component/pager.html.php'); ?>
<div id="images">
<?php foreach ($items as $item): ?>
	<div class="image">
		<a class="thumb" href="<?php print preg_replace('#/+#', '/', $item->path); ?>"><img alt="" src="<?php print $item->thumb; ?>"/></a>
		<a href="<?php print '?'.http_build_query(array_merge($urlparams, array('nick' => $item->nick, 'page' => null))) ?>" class="nick"><?php print htmlspecialchars($item->nick); ?></a>
		<span class="time"><?php print htmlspecialchars($item->ctime); ?></span>
		<?php if (trim($item->comment) !== ''): ?>
		<span class="comment"><?php print htmlspecialchars($item->comment); ?></span>
		<?php endif; ?>
		<a class="orig_url" href="<?php print htmlspecialchars($item->original_url); ?>">original link</a> | 
		<a class="pic_url" href="<?php print 'show.php?p='.$item->id; ?>">show</a>
	</div>
<?php endforeach; ?>
</div>
<?php include(APPROOT.'/html/footer.html.php'); ?>
