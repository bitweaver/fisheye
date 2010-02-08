<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/browse.php,v 1.7 2010/02/08 21:27:22 wjames5 Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');
global $gBitSystem, $gBitSmarty;

$gFisheyeGallery = new FisheyeGallery();
$galleryList = $gFisheyeGallery->getList( $_REQUEST );
$gBitSmarty->assign_by_ref( 'galleryList', $galleryList );

$gBitSystem->display( "bitpackage:fisheye/browse_galleries.tpl" , NULL, array( 'display_mode' => 'display' ));

?>
