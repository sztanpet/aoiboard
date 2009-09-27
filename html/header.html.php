<html>
<head>
	<title>
	recece
	</title>
	<link rel="stylesheet" type="text/css" href="./css/index.css" />
	<link rel="stylesheet" type="text/css" href="./css/links.css" />
	<?php if (isset($css_files) && is_array($css_files)): ?>
		<?php foreach ($css_files as $file): ?>
		<link rel="stylesheet" type="text/css" href="<?php print htmlspecialchars($file); ?>" />
		<?php endforeach; ?>
	<?php endif; ?>
</head>
<body>
