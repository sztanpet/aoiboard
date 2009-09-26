<div class="pager">
	<?php if ($maxpage > 0): 
	
		if ($urlparams['page'] == 0): ?>
			<span class="left">&laquo;</span>
		<?php else: ?>
			<a class="left" href="?<?php print http_build_query(array_merge($urlparams, array('page' => $urlparams['page'] - 1))); ?>">&laquo;</a>
		<?php endif; 
	
		if ($urlparams['page'] == $maxpage): ?>
			<span class="right">&raquo;</span>
		<?php else: ?>
			<a class="right" href="?<?php print http_build_query(array_merge($urlparams, array('page' => $urlparams['page'] + 1))); ?>">&raquo;</a>
		<?php endif;

		for ($i = 0; $i <= $maxpage; ++$i): 
			if ($i != $page):?>
				<a class="page" href="?<?php print http_build_query(array_merge($urlparams, array('page' => $i))); ?>"><?php print $i+1; ?></a>
			<?php else: ?>
				<span class="page"><?php print $i+1; ?></span>
			<?php endif; 
		endfor; 
	
	endif; ?>
</div>
