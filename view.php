<?php
/**
 * @version $Header$
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

$gBitSystem->verifyPackage( 'fisheye' );
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeImage.php');

global $gBitSystem, $fisheyeErrors, $fisheyeWarnings, $fisheyeSuccess;

//$gDebug = TRUE;

include_once( FISHEYE_PKG_INCLUDE_PATH.'gallery_lookup_inc.php' );

if( $gContent && $gContent->isValid() ) {
	$gBitSystem->setCanonicalLink( $gContent->getDisplayUrl() );
}

global $gHideModules;
$gHideModules = $gBitSystem->isFeatureActive( 'fisheye_gallery_hide_modules' );

if ( !$gContent->isValid() ) {
	// No gallery was indicated so we will redirect to the browse galleries page
	bit_redirect( FISHEYE_PKG_URL."list_galleries.php", '404' );
	die;
}

if( $gContent->isCommentable() ) {
	$commentsParentId = $gContent->mContentId;
	$comments_vars = Array('fisheyegallery');
	$comments_prefix_var='fisheyegallery:';
	$comments_object_var='fisheyegallery';
	$comments_return_url = $_SERVER['SCRIPT_NAME']."?gallery_id=".$gContent->mGalleryId;
	include_once( LIBERTY_PKG_INCLUDE_PATH.'comments_inc.php' );
}

if (!empty($_REQUEST['download'])){			
	// Checked against global users group assignment so that feature can be restricted on a group level. 
	// If content was checked, user would always have permission to do this.
	$gContent->verifyUserPermission('p_fisheye_download_gallery_arc');
	$gContent->download();
} else {
	require_once( FISHEYE_PKG_INCLUDE_PATH.'display_fisheye_gallery_inc.php' );
}

?>
