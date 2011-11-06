<?php include(APPROOT.'/html/header.html.php'); ?>
<?php include(APPROOT.'/html/component/menu.html.php'); ?>
<?php include(APPROOT.'/html/component/pager.html.php'); ?>
<ul id="links" class="paged_content" data-source="links.php" data-query='<?php print !empty($query_params) ? json_encode($query_params) : '{}'?>' data-page="<?php print $page ?>">
	<?php foreach ($items as $item): ?>
	<li class="link">
		<div class="id">
			<?php print $item->id; ?>
		</div>
		<div class="url">
			<a href="<?php print htmlspecialchars($item->url); ?>">
				<?php print htmlspecialchars($item->title); ?>
			</a>
			by <span class="nick"><?php print htmlspecialchars($item->nick); ?></span>
		</div>
		<span class="time"><?php print htmlspecialchars($item->ctime); ?></span>
	</li>
	<?php endforeach; ?>
</ul>
<?php include(APPROOT.'/html/footer.html.php'); ?>
