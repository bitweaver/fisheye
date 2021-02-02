<?php
/**
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

$gBitSystem->verifyPackage( 'fisheye' );
require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem, $gDebug;

if( !empty( $_REQUEST['size'] ) ) {
	// nuke old values if set
	$_COOKIE['fisheyeviewsize'] = NULL;
	setcookie( 'fisheyeviewsize', $_REQUEST['size'], 0, $gBitSystem->getConfig( 'cookie_path', BIT_ROOT_URL ), $gBitSystem->getConfig( 'cookie_domain', '.'.$_SERVER['SERVER_NAME'] ) );
}

if( !empty( $_REQUEST['refresh'] ) ) {
	$gBitSmarty->assign( 'refresh', '?refresh='.time() );
}

include_once( FISHEYE_PKG_INCLUDE_PATH.'image_lookup_inc.php' );

if( $gContent && $gContent->isValid() ) {
	$gBitSystem->setCanonicalLink( $gContent->getDisplayUrl() );
}

global $gHideModules;
$gHideModules = $gBitSystem->isFeatureActive( 'fisheye_image_hide_modules' );

if( is_object( $gGallery ) && $gGallery->isCommentable() ) {
	$commentsParentId = $gContent->mContentId;
	$comments_vars = Array('fisheyeimage');
	$comments_prefix_var='fisheyeimage:';
	$comments_object_var='fisheyeimage';
	$comments_return_url = $_SERVER['SCRIPT_NAME']."?image_id=".$gContent->mImageId;
	include_once( LIBERTY_PKG_INCLUDE_PATH.'comments_inc.php' );
}

$gContent->addHit();

// this will let LibertyMime know that we want to display the original image
if( $gContent->hasUpdatePermission() || $gGallery && $gGallery->getPreference( 'link_original_images' )) {
	$gContent->mInfo['image_file']['original'] = TRUE;
}

if( $gContent->hasUpdatePermission() ) {
	if( !empty( $_REQUEST['rethumb'] ) ) {
		$gContent->generateThumbnails( FALSE, TRUE );
	}
}

require_once( FISHEYE_PKG_INCLUDE_PATH.'display_fisheye_image_inc.php' );
