<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/browse.php,v 1.5 2007/10/10 12:10:20 nickpalmer Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');
global $gBitSystem, $gBitSmarty;

$gFisheyeGallery = new FisheyeGallery();
$galleryList = $gFisheyeGallery->getList( $_REQUEST );
$gBitSmarty->assign_by_ref( 'galleryList', $galleryList );

$gBitSystem->display( "bitpackage:fisheye/browse_galleries.tpl" );

?>
