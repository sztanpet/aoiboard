<?php include(APPROOT.'/html/header.html.php'); ?>
<div id="top">
	<?php include(APPROOT.'/html/component/menu.html.php'); ?>
	<iframe data-source="<?php print base_url().'/pager.php' ?>" class="pager" frameborder="0" src="<?php print base_url().'/pager.php?'.http_build_query($urlparams)?>"></iframe>
</div>
<?php $query_params = array_filter(array_merge($urlparams, array('page' => null)), 'strlen')?>
<div id="images" data-query='<?php print !empty($query_params) ? json_encode($query_params) : '{}'?>' data-page="<?php print $page ?>" data-source="index.php">
<?php foreach ($items as $item): ?>
<?php include(APPROOT.'/html/component/image.html.php'); ?>
<?php endforeach; ?>
</div>
<?php include(APPROOT.'/html/footer.html.php'); ?>
