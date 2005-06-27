<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/view.php,v 1.1.1.1.2.1 2005/06/27 10:55:45 lsces Exp $
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

//$gDebug = TRUE;

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
