<?php

require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');
global $gBitSystem, $smarty;

$gBitSystem->display("bitpackage:fisheye/gallery_tree.tpl");

?>
