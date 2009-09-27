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


<div class="pager">
	<?php if ($maxpage > 0): ?>
	
		<?php if ($urlparams['page'] == 0): ?>
			<span class="left">&laquo;</span>
		<?php else: ?>
			<a class="left" href="?<?php print http_build_query(array_merge($urlparams, array('page' => $urlparams['page'] - 1))); ?>">&laquo;</a>
		<?php endif; ?>
	
		<?php if ($urlparams['page'] == $maxpage): ?>
			<span class="right">&raquo;</span>
		<?php else: ?>
			<a class="right" href="?<?php print http_build_query(array_merge($urlparams, array('page' => $urlparams['page'] + 1))); ?>">&raquo;</a>
		<?php endif; ?>

		<?php if ($left_dots): ?>
			<span class="dots">...</span>
		<?php endif; ?>

		<?php for ($i = $start; $i <= $end; ++$i): ?>
			<?php if ($i != $page): ?>
				<a class="page" href="?<?php print http_build_query(array_merge($urlparams, array('page' => $i))); ?>"><?php print $i+1; ?></a>
			<?php else: ?>
				<span class="page"><?php print $i+1; ?></span>
			<?php endif; ?>
		<?php endfor; ?>
		<?php if ($right_dots): ?>
			<span class="dots">...</span>
		<?php endif; ?>
	
<?php endif; ?>
</div>
