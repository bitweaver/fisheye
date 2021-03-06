<?php
/**
 * @version $Header$
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );

require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeImage.php');
global $gBitSystem, $gBitSmarty;

$gFisheyeGallery = new FisheyeGallery();
$galleryList = $gFisheyeGallery->getList( $_REQUEST );
$gBitSmarty->assignByRef( 'galleryList', $galleryList );

$gBitSystem->display( "bitpackage:fisheye/browse_galleries.tpl" , NULL, array( 'display_mode' => 'display' ));

?>
