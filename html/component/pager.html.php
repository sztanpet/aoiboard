<?php
$to_left    = (int)(PAGER_LIMIT / 2);
$to_right   = (int)(PAGER_LIMIT / 2);
$page       = $urlparams['page'];
$left_dots  = $maxpage > PAGER_LIMIT ? true : false;
$right_dots = $maxpage > PAGER_LIMIT ? true : false;

$start = $page - $to_left;
$end   = $page + $to_right;

if ($start < 0) {
	$start     = 0;
	$end       = min(PAGER_LIMIT, $maxpage);
	$left_dots = false;
}
if ($end > $maxpage) {
	$end        = $maxpage;
	$start      = max($maxpage - PAGER_LIMIT, 0);
	$right_dots = false;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title> recece </title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php if (isset($css_files) && is_array($css_files)): ?>
		<?php foreach ($css_files as $file): ?>
		<link rel="stylesheet" type="text/css" href="<?php print htmlspecialchars($file).'?'.filemtime(APPROOT.'/'.trim($file, './')); ?>" />
		<?php endforeach; ?>
	<?php endif; ?>
</head>
<body>
<div class="pager" data-source="pager.php">
	<?php if ($maxpage > 0): ?>

		<?php if ($urlparams['page'] == 0): ?>
			<span class="left">&laquo;</span>
		<?php else: ?>
			<a target="_parent" class="left index_target" href="<?php print base_url().'/pager.php?'.http_build_query(array_merge($urlparams, array('page' => $urlparams['page'] - 1)), '', '&amp;'); ?>">&laquo;</a>
		<?php endif; ?>

		<?php if ($urlparams['page'] == $maxpage): ?>
			<span class="right">&raquo;</span>
		<?php else: ?>
			<a target="_parent" class="right index_target" href="<?php print base_url().'/pager.php?'.http_build_query(array_merge($urlparams, array('page' => $urlparams['page'] + 1)), '', '&amp;'); ?>">&raquo;</a>
		<?php endif; ?>

		<?php if ($left_dots): ?>
			<span class="dots">...</span>
		<?php endif; ?>

		<?php for ($i = $start; $i <= $end; ++$i): ?>
			<?php if ($i != $page): ?>
				<a target="_parent" class="page index_target" href="<?php print base_url().'/pager.php?'.http_build_query(array_merge($urlparams, array('page' => $i)), '', '&amp;'); ?>"><?php print $i; ?></a>
			<?php else: ?>
				<span class="page"><?php print $i; ?></span>
			<?php endif; ?>
		<?php endfor; ?>
		<?php if ($right_dots): ?>
			<span class="dots">...</span>
		<?php endif; ?>

<?php endif; ?>
</div>
<script src="js/jquery-1.4.4.min.js?<?php print filemtime(APPROOT.'/js/jquery-1.4.4.min.js')?>"></script>
<script src="js/pager.js?<?php print filemtime(APPROOT.'/js/pager.js')?>"></script>
</body>
