<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/edit.php,v 1.20 2007/03/23 21:31:53 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem;

include_once( FISHEYE_PKG_PATH.'gallery_lookup_inc.php' );

// Ensure the user has the permission to create new image galleries
if (empty($_REQUEST['gallery_id'])) {
	$gBitSystem->verifyPermission('p_fisheye_create');
}

$gContent->verifyPermission( 'p_fisheye_edit' );

if( $gBitUser->hasPermission( 'p_fisheye_change_thumb_size' ) ) {
	$thumbnailSizes = array(
		'avatar' => tra( 'Avatar (100x100 pixels)' ),
		'small'  => tra( 'Small (160x120 pixels)' ),
		'medium' => tra( 'Medium (400x300 pixels)' ),
		'large'  => tra( 'Large (800x600 pixels)' ),
	);
	$gBitSmarty->assign( 'thumbnailSizes', $thumbnailSizes );
}

$gBitSmarty->assign( 'galleryPaginationTypes', array( FISHEYE_PAGINATION_FIXED_GRID => 'Fixed Grid', FISHEYE_PAGINATION_AUTO_FLOW => 'Auto-Flow Images', FISHEYE_PAGINATION_POSITION_NUMBER => 'Image Order Page Number' ) );

if( !empty( $_REQUEST['savegallery'] ) ) {

	if( $_REQUEST['gallery_pagination'] == 'auto_flow' ) {
		$_REQUEST['rows_per_page'] = $_REQUEST['total_per_page'];
		$_REQUEST['cols_per_page'] = '1';
	}

	if( $gContent->store( $_REQUEST ) ) {
		$gContent->storePreference( 'is_public', !empty( $_REQUEST['is_public'] ) ? $_REQUEST['is_public'] : NULL );
		$gContent->storePreference( 'allow_comments', !empty( $_REQUEST['allow_comments'] ) ? $_REQUEST['allow_comments'] : NULL );
		$gContent->storePreference( 'gallery_pagination', !empty( $_REQUEST['gallery_pagination'] ) ? $_REQUEST['gallery_pagination'] : NULL );
		$gContent->storePreference( 'link_original_images', !empty( $_REQUEST['link_original_images'] ) ? $_REQUEST['link_original_images'] : NULL );
		// make sure var is fully stuffed with current data
		$gContent->load();
		// set the mappings, or if nothing checked, nuke them all
		$gContent->addToGalleries( !empty( $_REQUEST['galleryAdditions'] ) ? $_REQUEST['galleryAdditions'] : NULL );

		if( !empty( $_REQUEST['generate_thumbnails'] ) ) {
			$gContent->generateThumbnails();
		}

		header("location: ".$gContent->getDisplayUrl() );
		die();
	}
} elseif( !empty( $_REQUEST['delete'] ) ) {
	$gContent->hasUserPermission( 'p_fisheye_admin', TRUE, tra( "You do not have permission to delete this image gallery" ) );

	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['delete'] = TRUE;
		$formHash['gallery_id'] = $gContent->mGalleryId;
		$formHash['input'] = array(
			'<label><input name="recurse" value="" type="radio" checked="checked" /> '.tra( 'Delete only images in this gallery. Sub-galleries will not be removed.' ).'</label>',
			'<label><input name="recurse" value="all" type="radio" /> '.tra( 'Permanently delete all contents, even if they appear in other galleries.' ).'</label>',
		);
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete the gallery '.$gContent->getTitle().'?', 'error' => 'This cannot be undone!' ) );
	} else {
		$userId = $gContent->mInfo['user_id'];

		$recurseDelete = (!empty( $_REQUEST['recurse'] ) && ($_REQUEST['recurse'] == 'all') );

		if( $gContent->expunge( $recurseDelete ) ) {
			header( "Location: ".FISHEYE_PKG_URL.'?user_id='.$userId );
		}
	}

} elseif( !empty($_REQUEST['cancelgallery'] ) ) {
	header( 'Location: '.$gContent->getDisplayUrl() );
	die();
}

// Initalize the errors list which contains any errors which occured during storage
$errors = (!empty($gContent->mErrors) ? $gContent->mErrors : array());
$gBitSmarty->assign_by_ref('errors', $errors);

$gBitSystem->setOnloadScript( 'updateGalleryPagination();' );
$gBitSmarty->assign( 'loadAjax', 'prototype' );

$gallery = $gContent->getParentGalleries();
$gBitSmarty->assign_by_ref( 'parentGalleries', $gallery );
$getHash = array(
	'user_id'       => $gBitUser->mUserId,
	'contain_item'  => $gContent->mContentId,
	'max_records'   => -1,
	'no_thumbnails' => TRUE,
	'sort_mode'     => 'title_asc',
	'show_empty'    => TRUE,
);
// modify listHash according to global preferences
if( $gBitSystem->isFeatureActive( 'fisheye_show_all_to_admins' ) && $gBitUser->hasPermission( 'p_fisheye_admin' ) ) {
	unset( $getHash['user_id'] );
} elseif( $gBitSystem->isFeatureActive( 'fisheye_show_public_on_upload' ) ) {
	$getHash['show_public'] = TRUE;
}
$galleryList = $gContent->getList( $getHash );
$gBitSmarty->assign_by_ref( 'galleryList', $galleryList['data'] );

$gContent->invokeServices( 'content_edit_function' );

$gBitSystem->display( 'bitpackage:fisheye/edit_gallery.tpl', 'Edit Gallery: '.$gContent->getTitle() );

?>
