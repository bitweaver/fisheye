<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/gallery_tree.php,v 1.4 2008/06/25 22:21:09 spiderr Exp $
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

$gBitSystem->display("bitpackage:fisheye/gallery_tree.tpl", NULL, array( 'display_mode' => 'display' ));

?>
