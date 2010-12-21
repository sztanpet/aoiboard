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
	<link rel="alternate" type="application/rss+xml"  href="<?php print base_url().'/rss/'.PIC_THUMB_RSS_FILE?>"      title="#aoianime picture feed">
	<link rel="alternate" type="application/rss+xml"  href="<?php print base_url().'/rss/'.PIC_FULL_RSS_FILE?>"       title="#aoianime picture feed (full size)">
	<link rel="alternate" type="application/rss+xml"  href="<?php print base_url().'/rss/'.LINK_RSS_FILE?>"           title="#aoianime link feed">
	<link rel="alternate" type="application/rss+xml"  href="<?php print base_url().'/rss/'.COMBINED_THUMB_RSS_FILE?>" title="#application combined feed">
	<link rel="alternate" type="application/rss+xml"  href="<?php print base_url().'/rss/'.COMBINED_FULL_RSS_FILE?>"  title="#application combined feed (full size)">
</head>
<body>
