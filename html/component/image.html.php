<?php

$thumb_path = $item->html_thumb();
$hidden = false;
if (in_array($item->id, $hidden_images)) {
	$hidden = true;
	$thumb_path = './img/hidden.png';
}

?>

<div id="image_<?php print $item->id?>" class="image <?php if (isset($item_i) && $item_i === 0) print "page_start"; if (isset($item_i) && $item_i === $limit - 1) print "page_end" ?>" data-id="<?php print $item->id?>" data-ctime="<?php print substr($item->ctime, 0, 10)?>" data-page="<?php print $page?>">

	<?php if (!$item->deleted): ?>
		<a class="thumb" href="<?php print $item->path; ?>"><img alt="" src="<?php print $thumb_path; ?>"/></a>
	<?php else: ?>
		<a class="thumb" href="<?php print $item->original_url; ?>"><img alt="" src="<?php print $thumb_path; ?>"/></a>
	<?php endif; ?>

	<div class="metadata">
		<div>
			<a href="<?php print '?'.http_build_query(array_merge($urlparams, array('nick' => $item->nick, 'page' => null))) ?>" class="nick"><?php print htmlspecialchars($item->nick); ?></a>
			<?php if (!$hidden): ?>
			<span class="hide">hide</span>
			<?php endif; ?>
		</div>
		<span class="time"><?php print htmlspecialchars($item->ctime); ?></span>

		<?php if (trim($item->comment) !== ''): ?>
			<span class="comment"><?php print htmlspecialchars($item->comment); ?></span>
		<?php endif; ?>

		<a class="orig_url" href="<?php print htmlspecialchars($item->original_url); ?>">original link</a> |

		<?php if (!$item->deleted): ?>
			<a class="pic_url" href="<?php print 'show.php?p='.$item->id; ?>">show</a>
		<?php endif; ?>
	</div>
	<div class="border">&nbsp;</div>
</div>
