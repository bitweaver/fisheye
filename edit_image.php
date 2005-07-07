<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/edit_image.php,v 1.1.1.1.2.4 2005/07/07 16:47:04 spiderr Exp $
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
include_once( FISHEYE_PKG_PATH.'image_lookup_inc.php' );

if ( (!empty($gContent->mImageId)) && ($gContent->mInfo['user_id'] != $gBitUser->mUserId && !$gBitUser->isAdmin()) ) {
	// This user does not own this image and they are not an Administrator
	$smarty->assign( 'msg', tra( "You do not own this image!" ) );
	$gBitSystem->display( "error.tpl" );
	die;
}

if( !empty($_REQUEST['saveImage']) || !empty($_REQUEST['regenerateThumbnails'] ) ) {
	if (empty($_REQUEST['gallery_id']) && empty($_REQUEST['image_id'])) {
		// We have no way to know what gallery to add an image to or what image to edit!
		$smarty->assign( 'msg', tra( "No gallery or image was specified" ) );
		$gBitSystem->display( "error.tpl" );
		die;
	}

	// Store/Update the image
	if (isset($_FILES['imageFile']) && is_uploaded_file($_FILES['imageFile']['tmp_name'])) {
			$_REQUEST['upload'] = &$_FILES['imageFile'];
			$_REQUEST['upload']['process_storage'] = STORAGE_IMAGE;
	}
	$_REQUEST['purge_from_galleries'] = TRUE;
	if( $gContent->store($_REQUEST) ) {
		$gContent->addToGalleries( $_REQUEST['galleryAdditions'] );
		// maybe we need to resize the original and generate thumbnails
		if( !empty( $_REQUEST['resize'] ) ) {
			$gContent->resizeOriginal( $_REQUEST['resize'] );
		}
		if( !empty( $_REQUEST['generate_thumbnails'] ) ) {
			$gContent->generateThumbnails();
		}
		// This needs to happen after the store, else the image width/hieght are screwed for people using the background thumbnailer
		if( !empty( $_REQUEST['rotate_image'] ) ) {
			$gContent->rotateImage( $_REQUEST['rotate_image'] );
		}
		if ( $gBitSystem->isPackageActive('categories') ) {
			$cat_desc = $gLibertySystem->mContentTypes[FISHEYEIMAGE_CONTENT_TYPE_GUID]['content_description'].' by '.$gBitUser->getDisplayName( FALSE, array( 'real_name' => $gContent->mInfo['creator_real_name'], 'user' => $gContent->mInfo['creator_user'], 'user_id'=>$gContent->mInfo['user_id'] ) );
			$cat_name = $gContent->getTitle();
			$cat_href = $gContent->getDisplayUrl();
			$cat_objid = $gContent->mContentId;
			$cat_obj_type = FISHEYEIMAGE_CONTENT_TYPE_GUID;
			include_once( CATEGORIES_PKG_PATH.'categorize_inc.php' );
		}
		if( empty( $gContent->mErrors ) ) {
			// add a refresh parameter to the URL so the thumbnails will properly refresh first go reload
			header( 'Location: '.$gContent->getDisplayUrl().($gBitSystem->isFeatureActive( 'pretty_urls' ) ? '?' : '&' ).'refresh=1' );
			die;
		}
	}
} elseif( !empty($_REQUEST['delete']) ) {
	$gContent->hasUserPermission( 'bit_p_admin_fisheye', TRUE, tra( "You do not have permission to delete this image." ) );

	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['delete'] = TRUE;
		$formHash['image_id'] = $gContent->mImageId;
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete the image '.$gContent->getTitle().'? <p> It will be removed from all galleries to which it belongs.</p>' ) );
	} else {
		if( $gContent->expunge() ) {
			$url = ( is_object( $gGallery ) ? $gGallery->getDisplayUrl() : FISHEYE_PKG_URL );
			header( "Location: $url" );
		}
	}

}

if ( $gBitSystem->isPackageActive('categories') ) {
	$cat_type = FISHEYEGALLERY_CONTENT_TYPE_GUID;
	$cat_objid = $gContent->mContentId;
	include_once( CATEGORIES_PKG_PATH.'categorize_list_inc.php' );
}

$errors = $gContent->mErrors;
$smarty->assign_by_ref('errors', $errors);

$gContent->loadParentGalleries();

// Get a list of all existing galleries
$gFisheyeGallery = new FisheyeGallery();
$listHash = array( 'user_id'=>$gContent->mInfo['user_id'], 'max_records' => -1, 'no_thumbnails' => TRUE );
$galleryList = $gFisheyeGallery->getList( $listHash );
$smarty->assign_by_ref('galleryList', $galleryList);

$smarty->assign('requested_gallery', !empty($_REQUEST['gallery_id']) ? $_REQUEST['gallery_id'] : NULL);

if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
	global $gGatekeeper;
	$smarty->assign( 'securities', $gGatekeeper->getSecurityList( $gBitUser->mUserId ) );
}

$gBitSystem->display( 'bitpackage:fisheye/edit_image.tpl', 'Edit Image: '.$gContent->getTitle() );

?>
