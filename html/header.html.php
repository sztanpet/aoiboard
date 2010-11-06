<!DOCTYPE html PUBLIC
	"-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
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
