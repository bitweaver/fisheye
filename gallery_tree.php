<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/gallery_tree.php,v 1.1.1.1.2.2 2005/07/26 15:50:04 drewslater Exp $
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

$gBitSystem->display("bitpackage:fisheye/gallery_tree.tpl");

?>
