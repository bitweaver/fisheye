<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/view.php,v 1.1.1.1.2.2 2006/03/09 17:47:34 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem, $fisheyeErrors, $fisheyeWarnings, $fisheyeSuccess;

$gBitSystem->verifyPermission( 'bit_p_view_fisheye' );

include_once( FISHEYE_PKG_PATH.'gallery_lookup_inc.php' );

global $gHideModules;
$gHideModules = $gBitSystem->isFeatureActive( 'fisheye_gallery_hide_modules' );

if ( !$gContent->isValid() ) {
	// No gallery was indicated so we will redirect to the browse galleries page
	header("location: list_galleries.php");
	die;
}

require_once( FISHEYE_PKG_PATH.'display_fisheye_gallery_inc.php' );

?>
