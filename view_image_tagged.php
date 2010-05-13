<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/Attic/view_image_tagged.php,v 1.5 2010/05/13 23:09:37 lsces Exp $
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

if( !empty( $_REQUEST['size'] ) ) {
	// nuke old values if set
	$_COOKIE['fisheyeviewsize'] = NULL;
	setcookie( 'fisheyeviewsize', $_REQUEST['size'], 0, $gBitSystem->getConfig( 'cookie_path', BIT_ROOT_URL ), $gBitSystem->getConfig( 'cookie_domain', '.'.$_SERVER['SERVER_NAME'] ) );
}

include_once( FISHEYE_PKG_PATH.'image_lookup_inc.php' );
//vd($gContent);
if( !empty( $_REQUEST['mode'] ) ) {
	if ( !empty( $_REQUEST['save'] ) and $_REQUEST['save'] == 'yes' ) {
		// save tag record
		if( $gContent->verifyId( $_REQUEST['image_id'] ) ) {
			$gContent->mDb->StartTrans();
			$storeComment = new LibertyComment( @BitBase::verifyId( $_REQUEST['comment_id'] ) ? $_REQUEST['comment_id'] : NULL );
			if ( $gContent->verifyId( $storeComment->mCommentId ) ) {
				$gContent->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_attachment_tags` WHERE `content_id`=? and `comment_id`=?", array( $gContent->mContentId, $storeComment->mCommentId ) );
			}
			$_REQUEST['content_id'] = $gContent->mContentId;
			$_REQUEST['comments_parent_id'] = $gContent->mContentId;
			$_REQUEST['comment_title'] = $_REQUEST['description'];
			$_REQUEST['comment_data'] = $_REQUEST['description'];
			if( $storeComment->storeComment( $_REQUEST )) {
				// store successful
				$storeComment->loadComment();
				$gContent->mDb->query( "INSERT INTO `".BIT_DB_PREFIX."liberty_attachment_tags` ( `attachment_id`, `comment_id`, `tag_top`, `tag_left`, `tag_width`, `tag_height` )
					VALUES ( ?, ?, ?, ?, ?, ? )", 
					array( $gContent->mContentId, $storeComment->mCommentId, $_REQUEST['top'], $_REQUEST['left'], $_REQUEST['width'], $_REQUEST['height'] ) );
			}

			$gContent->mDb->CompleteTrans();
		}
	} 
	$gBitSmarty->assign( 'mode', $_REQUEST['mode'] );
}

if( !empty( $_REQUEST['delete'] ) ) {
	// delete tag record 
}

$tagSql = "SELECT lat.`comment_id` as tag_no, lc.`title` as description, lat.*
			FROM `".BIT_DB_PREFIX."liberty_attachment_tags` lat 
			JOIN `".BIT_DB_PREFIX."liberty_comments` lcm ON lcm.`comment_id` = lat.`comment_id`
			JOIN `".BIT_DB_PREFIX."liberty_content` lc ON lc.`content_id` = lcm.`content_id`
			WHERE lat.`attachment_id` = ?";
$gContent->mInfo['tags'] = $gContent->mDb->getAssoc( $tagSql, array( $gContent->mContentId ) );
//vd($gContent->mContentId);
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

$gBitThemes->loadCss( UTIL_PKG_PATH.'javascript/libs/jquery/themes/base/ui.all.css', TRUE );
$gBitThemes->loadCss( THEMES_PKG_PATH.'css/imagetag.css', TRUE );
$gBitThemes->loadAjax( 'jquery' );
$gBitThemes->loadJavascript( UTIL_PKG_PATH.'javascript/libs/jquery/full/ui/jquery.ui.all.js', FALSE, 500, FALSE );
$gBitThemes->loadJavascript( LIBERTY_PKG_PATH.'scripts/imagetag.js', FALSE, 500, FALSE );

// this will let LibertyMime know that we want to display the original image
$gContent->mInfo['image_file']['original'] = TRUE;

if( !$gContent->isValid() ) {
	$gBitSystem->setHttpStatus( 404 );
	$gBitSystem->fatalError( tra( "No image exists with the given ID" ) ,'error.tpl', '' );
}

$displayHash = array( 'perm_name' => 'p_fisheye_view' );
$gContent->invokeServices( 'content_display_function', $displayHash );

// Get the proper thumbnail size to display on this page
if( empty( $_REQUEST['size'] )) {
	$_REQUEST['size'] = 'original';
}

$gBitSystem->setBrowserTitle( $gContent->getTitle() );
$gBitSystem->display( 'bitpackage:fisheye/view_image_tagged.tpl' , NULL, array( 'display_mode' => 'display' ));?>