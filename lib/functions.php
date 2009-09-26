<?php
function create_thumb($path, $thumb_path, $width = THUMB_WIDTH, $height = THUMB_HEIGHT) {
	$image_info = getImageSize($path); // see EXIF for faster way
	switch ($image_info['mime']) {
		case 'image/gif':
			if(imagetypes() & IMG_GIF){ // not the same as IMAGETYPE
				$o_im = @imageCreateFromGIF($path) ;
			}else{
				throw new Exception('GIF images are not supported');
			}
			break;
		case 'image/jpeg':
			if (imagetypes() & IMG_JPG){
				$o_im = @imageCreateFromJPEG($path) ;
			}else{
				throw new Exception('JPEG images are not supported');
			}
			break;
		case 'image/png':
			if(imagetypes() & IMG_PNG){
				$o_im = @imageCreateFromPNG($path) ;
			}else{
				throw new Exception('PNG images are not supported');
			}
			break;
		case 'image/wbmp':
			if(imagetypes() & IMG_WBMP){
				$o_im = @imageCreateFromWBMP($path) ;
			}else{
				throw new Exception('WBMP images are not supported');
			}
			break;
		default:
			throw new Exception($image_info['mime'].' images are not supported');
			break;
	}

	list($o_wd, $o_ht, $html_dimension_string) = $image_info;

	$ratio = $o_wd / $o_ht;
	$t_ht  = $width;
	$t_wd  = $height;

	if(1 > $ratio){
		$t_wd = round($o_wd * $t_wd / $o_ht);
	}else{
		$t_ht = round($o_ht * $t_ht / $o_wd);
	}


	$t_wd = ($t_wd < 1) ? 1 : $t_wd;
	$t_ht = ($t_ht < 1) ? 1 : $t_ht;

	$t_im = imageCreateTrueColor($t_wd, $t_ht);

	imageCopyResampled($t_im, $o_im, 0, 0, 0, 0, $t_wd, $t_ht, $o_wd, $o_ht);

	imagejpeg($t_im, $thumb_path, 85);

	chmod($thumb_path, 0664);
	imageDestroy($o_im);
	imageDestroy($t_im);
	return true;
}
