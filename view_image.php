<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/view_image.php,v 1.12 2008/09/15 01:51:28 spiderr Exp $
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
	// nuke old values if set
	$_COOKIE['fisheyeviewsize'] = NULL;
	setcookie( 'fisheyeviewsize', $_REQUEST['size'], 0, $gBitSystem->getConfig( 'cookie_path', BIT_ROOT_URL ), $gBitSystem->getConfig( 'cookie_domain', '.'.$_SERVER['SERVER_NAME'] ) );
}

if( !empty( $_REQUEST['refresh'] ) ) {
	$gBitSmarty->assign( 'refresh', '?refresh='.time() );
}

include_once( FISHEYE_PKG_PATH.'image_lookup_inc.php' );

global $gHideModules;
$gHideModules = $gBitSystem->isFeatureActive( 'fisheye_image_hide_modules' );

if( is_object( $gGallery ) && $gGallery->isCommentable() ) {
	$commentsParentId = $gContent->mContentId;
	$comments_vars = Array('fisheyeimage');
	$comments_prefix_var='fisheyeimage:';
	$comments_object_var='fisheyeimage';
	$comments_return_url = $_SERVER['PHP_SELF']."?image_id=".$gContent->mImageId;
	include_once( LIBERTY_PKG_PATH.'comments_inc.php' );
}

$gContent->addHit();

$gContent->mInfo['original'] = TRUE;
require_once( FISHEYE_PKG_PATH.'display_fisheye_image_inc.php' );
?>
