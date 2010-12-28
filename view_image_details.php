<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/view_image_details.php,v 1.1 2010/11/11 22:58:23 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem, $gDebug;

include_once( FISHEYE_PKG_PATH.'image_lookup_inc.php' );

$gContent->invokeServices( 'content_display_function', $displayHash );

if( is_object( $gGallery ) && $gGallery->isCommentable() ) {
	$commentsParentId = $gContent->mContentId;
	$comments_vars = Array('fisheyeimage');
	$comments_prefix_var='fisheyeimage:';
	$comments_object_var='fisheyeimage';
	$comments_return_url = FISHEYE_PKG_URL."view_image.php?image_id=".$gContent->mImageId;
	include_once( LIBERTY_PKG_PATH.'comments_inc.php' );
}

$gBitSmarty->display( 'bitpackage:fisheye/view_image_details.tpl' );
