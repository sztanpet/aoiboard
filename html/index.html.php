<?php include(APPROOT.'/html/header.html.php'); ?>
<div id="top">
	<?php include(APPROOT.'/html/component/menu.html.php'); ?>
</div>
<?php $query_params = array_filter(array_merge($urlparams, array('page' => null, 'magic' => MAGIC_VERSION_NUMBER_AGAINST_CACHE_PROBLEMS)), 'strlen')?>
<div id="images" data-query='<?php print !empty($query_params) ? json_encode($query_params) : '{}'?>' data-page="<?php print $page ?>" data-source="index.php" class="paged_content">
	<?php foreach ($items as $item): ?>
		<?php include(APPROOT.'/html/component/image.html.php'); ?>
	<?php endforeach; ?>
</div>
<?php include(APPROOT.'/html/footer.html.php'); ?>
