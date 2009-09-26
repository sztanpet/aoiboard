<?php 
$base = dirname($_SERVER['SCRIPT_NAME']);
$file = substr($_SERVER['SCRIPT_NAME'], strlen($base)+1); // +1 for tailing '/'
?>
<ul id="menu">
	<li>
		<?php if ($file != 'index.php'): ?>
		<a href="<?php print $base; ?>">Pics</a>
		<?php else: ?>
		<span>Pics</span>
		<?php endif; ?>
	</li>
	<li>
		<?php if ($file != 'links.php'): ?>
		<a href="<?php print $base; ?>/links.php">Links</a>
		<?php else: ?>
		<span>Links</span>
		<?php endif; ?>
	</li>
</ul>
