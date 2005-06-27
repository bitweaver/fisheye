<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/browse.php,v 1.1.1.1.2.1 2005/06/27 10:55:44 lsces Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');
global $gBitSystem, $smarty;

$gFisheyeGallery = new FisheyeGallery();
$galleryList = $gFisheyeGallery->getList( $_REQUEST );
$smarty->assign_by_ref('galleryList', $galleryList);

$gBitSystem->display("bitpackage:fisheye/browse_galleries.tpl");

?>
