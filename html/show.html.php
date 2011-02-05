<?php include(APPROOT.'/html/header.html.php'); ?>
<div id="bigpic">
	<div class="image">
		<span class="nick"><?php print htmlspecialchars($pic->nick); ?></span>
		<span class="time"><?php print htmlspecialchars($pic->ctime); ?></span>
		<?php if (trim($pic->comment) !== ''): ?>
			<span class="comment"><?php print htmlspecialchars($pic->comment); ?></span>
		<?php endif; ?>
		<a class="bigpic" href="<?php print $pic->original_url; ?>"><img alt="" src="<?php print $pic->path; ?>"/></a>
	</div>
</div>
<?php include(APPROOT.'/html/footer.html.php'); ?>
