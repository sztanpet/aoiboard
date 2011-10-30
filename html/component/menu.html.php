<?php
$base = dirname($_SERVER['SCRIPT_NAME']);
$file = substr($_SERVER['SCRIPT_NAME'], strlen($base)+1); // +1 for tailing '/'
$autofillable = (isset($autofillable) && $autofillable) ? true : false;
$autofill_enabled = setting_enabled('autofill');
?>

<div class="topbar" data-dropdown="dropdown">
	<div class="topbar-inner">
		<div class="container fullwidth">
			<ul class="nav">
				<li class="<?php print ($file == 'index.php') ? 'active' : ''?>" >
				<a class="menu_item" href="<?php print base_url(); ?>">pics</a>
				</li>

				<li class="<?php print ($file == 'links.php') ? 'active' : ''?>" >
				<a class="menu_item" href="<?php print base_url().'links.php'; ?>">Links</a>
				</li>
			</ul>

			<ul class="nav secondary-nav">
				<li class="dropdown">
				<span class="dropdown-toggle"> settings </span>
				<ul class="dropdown-menu">
					<li id="autofill_settings">
					<?php if ($autofillable): ?>
					<a href="#">
						<input type="checkbox" id="autofill" <?php if ($autofill_enabled) { print 'checked'; }?>>
						<label for="autofill">	autofill pages </label>
					</a>
					<?php endif;?>
					</li>
				</ul>
				</li>
			</ul>
		</div>
	</div>
	<!-- /topbar-inner -->
	<?php include(APPROOT.'/html/component/pager.html.php'); ?>
</div>
