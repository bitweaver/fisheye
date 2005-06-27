<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/gallery_tree.php,v 1.1.1.1.2.1 2005/06/27 10:55:45 lsces Exp $
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

$gBitSystem->display("bitpackage:fisheye/gallery_tree.tpl");

?>
