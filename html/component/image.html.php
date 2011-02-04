<div class="image <?php if (isset($item_i) && $item_i === 0) print "page_start"; if (isset($item_i) && $item_i === $limit - 1) print "page_end" ?>" data-id="<?php print $item->id?>" data-ctime="<?php print substr($item->ctime, 0, 10)?>" data-page="<?php print $page?>">

	<?php if (!$item->deleted): ?>
		<a class="thumb" href="<?php print preg_replace('#/+#', '/', $item->path); ?>"><img alt="" src="<?php print $item->html_thumb(); ?>"/></a>
	<?php else: ?>
		<a class="thumb" href="<?php print $item->original_url; ?>"><img alt="" src="<?php print $item->html_thumb(); ?>"/></a>
	<?php endif; ?>

	<a href="<?php print '?'.http_build_query(array_merge($urlparams, array('nick' => $item->nick, 'page' => null))) ?>" class="nick"><?php print htmlspecialchars($item->nick); ?></a>
	<a href="#" class="hide">HOLYSHITHIDETHIS</a>
	<span class="time"><?php print htmlspecialchars($item->ctime); ?></span>

	<?php if (trim($item->comment) !== ''): ?>
		<span class="comment"><?php print htmlspecialchars($item->comment); ?></span>
	<?php endif; ?>

	<a class="orig_url" href="<?php print htmlspecialchars($item->original_url); ?>">original link</a> |

	<?php if (!$item->deleted): ?>
		<a class="pic_url" href="<?php print 'show.php?p='.$item->id; ?>">show</a>
	<?php endif; ?>

	<div class="border">&nbsp;</div>
</div>
