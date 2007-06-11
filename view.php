<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/view.php,v 1.5 2007/06/11 20:16:23 squareing Exp $
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
	header("location: ".FISHEYE_PKG_URL."list_galleries.php");
	die;
}

if( $gContent->isCommentable() ) {
	$commentsParentId = $gContent->mContentId;
	$comments_vars = Array('fisheyegallery');
	$comments_prefix_var='fisheyegallery:';
	$comments_object_var='fisheyegallery';
	$comments_return_url = $_SERVER['PHP_SELF']."?gallery_id=".$gContent->mGalleryId;
	include_once( LIBERTY_PKG_PATH.'comments_inc.php' );
}

require_once( FISHEYE_PKG_PATH.'display_fisheye_gallery_inc.php' );

?>
