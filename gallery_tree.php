<?php
/**
 * @version $Header$
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeImage.php');
global $gBitSystem, $gBitSmarty;

$gBitSystem->display("bitpackage:fisheye/gallery_tree.tpl", NULL, array( 'display_mode' => 'display' ));

?>
