<?php

require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');
global $gBitSystem, $smarty;

$gFisheyeGallery = new FisheyeGallery();
$galleryList = $gFisheyeGallery->getList( $_REQUEST );
$smarty->assign_by_ref('galleryList', $galleryList);

$gBitSystem->display("bitpackage:fisheye/browse_galleries.tpl");

?>
