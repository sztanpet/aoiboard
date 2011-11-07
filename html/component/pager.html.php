<?php
$to_left    = (int)(PAGER_LIMIT / 2);
$to_right   = (int)(PAGER_LIMIT / 2);
$page       = $urlparams['page'];
$left_dots  = $maxpage > PAGER_LIMIT ? true : false;
$right_dots = $maxpage > PAGER_LIMIT ? true : false;
$visited_pages = isset($visited_pages) ? $visited_pages : array();

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

<div class="pager" data-source="<?php print base_path().'/pager.php' ?>" data-query='<?php print !empty($query_params) ? json_encode($query_params) : '{}'?>' >
	<?php if ($maxpage > 0): ?>

		<?php if ($urlparams['page'] == 0): ?>
			<span class="left">&larr;</span>
		<?php else: ?>
			<a class="left <?php print in_array($urlparams['page']-1, $visited_pages) ? 'visited' : ''?>" href="?<?php print http_build_query(array_merge($urlparams, array('page' => $urlparams['page'] - 1)), '', '&amp;'); ?>">&larr;</a>
		<?php endif; ?>

		<?php if ($urlparams['page'] == $maxpage): ?>
			<span class="right">&rarr;</span>
		<?php else: ?>
			<a class="right <?php print in_array($urlparams['page']+1, $visited_pages) ? 'visited' : ''?>" href="?<?php print http_build_query(array_merge($urlparams, array('page' => $urlparams['page'] + 1)), '', '&amp;'); ?>">&rarr;</a>
		<?php endif; ?>

		<?php if ($left_dots): ?>
			<span class="dots">...</span>
		<?php endif; ?>

		<?php for ($i = $start; $i <= $end; ++$i): 	?>
			<a class="page <?php if ($i == $page): ?> active <?php endif;?> <?php print in_array($i, $visited_pages) ? 'visited' : ''?>" href="?<?php print http_build_query(array_merge($urlparams, array('page' => $i)), '', '&amp;'); ?>"><?php print $i; ?></a>
		<?php endfor; ?>
		<?php if ($right_dots): ?>
			<span class="dots">...</span>
		<?php endif; ?>

<?php endif; ?>
</div>
