<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/view_image.php,v 1.4 2006/03/01 20:16:07 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem, $gDebug;

//$gDebug = TRUE;

if( !empty( $_REQUEST['size'] ) ) {
	setcookie( 'fisheyeviewsize', $_REQUEST['size'], 0, $gBitSystem->getConfig('cookie_path'), $gBitSystem->getConfig('cookie_domain') );
}

if( !empty( $_REQUEST['refresh'] ) ) {
	$gBitSmarty->assign( 'refresh', '?refresh='.time() );
}

include_once( FISHEYE_PKG_PATH.'image_lookup_inc.php' );

global $gHideModules;
$gHideModules = $gBitSystem->isFeatureActive( 'fisheye_image_hide_modules' );

require_once( FISHEYE_PKG_PATH.'display_fisheye_image_inc.php' );

?>
