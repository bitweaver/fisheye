<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/gallery_tree.php,v 1.5 2010/02/08 21:27:22 wjames5 Exp $
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

$gBitSystem->display("bitpackage:fisheye/gallery_tree.tpl", NULL, array( 'display_mode' => 'display' ));

?>
