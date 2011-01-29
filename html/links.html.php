<?php include(APPROOT.'/html/header.html.php'); ?>
<?php include(APPROOT.'/html/component/menu.html.php'); ?>
<?php include(APPROOT.'/html/component/pager.html.php'); ?>
<table id="links" class="paged_content" data-source="links.php" data-query='<?php print !empty($query_params) ? json_encode($query_params) : '{}'?>' data-page="<?php print $page ?>">
	<thead>
		<tr>
			<td class="nick">Nick</td>
			<td class="url">Link</td>
			<td class="time">Posted</td>
		</tr>
	</thead>
<?php foreach ($items as $item): ?>
	<tr>
		<td class="nick"><?php print htmlspecialchars($item->nick); ?></td>
		<td class="url"><a href="<?php print htmlspecialchars($item->url); ?>"><?php print htmlspecialchars($item->title); ?></a></td>
		<td class="time"><?php print htmlspecialchars($item->ctime); ?></td>
	</tr>
<?php endforeach; ?>
</table>
<?php include(APPROOT.'/html/footer.html.php'); ?>
