<?php include('./html/header.html.php'); ?>
<?php include('./html/component/menu.html.php'); ?>
<div class="top">
<?php include('./html/component/pager.html.php'); ?>
</div>
<table id="links">
	<thead>
		<tr>
			<td class="nick">Nick</td>
			<td class="url">Link</td>
			<td class="time">Posted</td>
		</tr>
	</thead>
<?php foreach (array_reverse($items) as $item): ?>
	<tr>
		<td class="nick"><?php print htmlspecialchars($item->nick); ?></td>
		<td class="url"><a href="<?php print htmlspecialchars($item->url); ?>"><?php print htmlspecialchars($item->title); ?></a></td>
		<td class="time"><?php print htmlspecialchars($item->time); ?></td>
	</tr>
<?php endforeach; ?>
</table>
<div class="bottom">
<?php include('./html/component/pager.html.php'); ?>
</div>
<?php include('./html/footer.html.php'); ?>
