<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/edit_image.php,v 1.14 2006/12/27 14:27:40 squareing Exp $
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

if ( (!empty($gContent->mImageId)) && ($gContent->getField( 'user_id' ) != $gBitUser->mUserId && !$gBitUser->isAdmin()) ) {
	// This user does not own this image and they are not an Administrator
	$gBitSmarty->assign( 'msg', tra( "You do not own this image!" ) );
	$gBitSystem->display( "error.tpl" );
	die;
}

if( !empty($_REQUEST['saveImage']) || !empty($_REQUEST['regenerateThumbnails'] ) ) {
	if (empty($_REQUEST['gallery_id']) && empty($_REQUEST['image_id'])) {
		// We have no way to know what gallery to add an image to or what image to edit!
		$gBitSmarty->assign( 'msg', tra( "No gallery or image was specified" ) );
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
		// maybe we need to resize the original and generate thumbnails
		if( !empty( $_REQUEST['resize'] ) ) {
			$gContent->resizeOriginal( $_REQUEST['resize'] );
		}
		// This needs to happen after the store, else the image width/hieght are screwed for people using the background thumbnailer
		if( !empty( $_REQUEST['rotate_image'] ) ) {
			$gContent->rotateImage( $_REQUEST['rotate_image'] );
		}
		if( !empty( $_REQUEST['ajax'] ) ) {
			// we need to refresh the images in the page after saving - not working yet - xing
			header( 'Location: '.FISHEYE_PKG_URL."image_order.php?refresh=1&gallery_id=".$_REQUEST['gallery_id'] );
			die;
		}
		$gContent->addToGalleries( $_REQUEST['galleryAdditions'] );
		if( !empty( $_REQUEST['generate_thumbnails'] ) ) {
			$gContent->generateThumbnails();
		}
		if( empty( $gContent->mErrors ) ) {
			// add a refresh parameter to the URL so the thumbnails will properly refresh first go reload
			header( 'Location: '.$gContent->getDisplayUrl().($gBitSystem->isFeatureActive( 'pretty_urls' ) ? '?' : '&' ).'refresh=1' );
			die;
		}
	}
} elseif( !empty($_REQUEST['delete']) ) {
	$gContent->hasUserPermission( 'p_fisheye_admin', TRUE, tra( "You do not have permission to delete this image." ) );

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

$errors = $gContent->mErrors;
$gBitSmarty->assign_by_ref('errors', $errors);

$gContent->loadParentGalleries();

// Get a list of all existing galleries
$gFisheyeGallery = new FisheyeGallery();
$listHash = array(
	'user_id'       => $gContent->isValid() ? $gContent->getField( 'user_id' ) : $gBitUser->mUserId,
	'max_records'   => -1,
	'no_thumbnails' => TRUE,
	'sort_mode'     => 'title_asc',
	'show_empty'    => TRUE,
);
// modify listHash according to global preferences
if( $gBitSystem->isFeatureActive( 'fisheye_show_all_to_editors' ) && $gBitUser->hasPermission( 'p_fisheye_edit' ) ) {
	unset( $listHash['user_id'] );
} elseif( $gBitSystem->isFeatureActive( 'fisheye_show_public_on_upload' ) ) {
	$listHash['show_public'] = TRUE;
}
$galleryList = $gFisheyeGallery->getList( $listHash );
$gBitSmarty->assign_by_ref( 'galleryList', $galleryList['data'] );

$gBitSmarty->assign('requested_gallery', !empty($_REQUEST['gallery_id']) ? $_REQUEST['gallery_id'] : NULL);

$gContent->invokeServices( 'content_edit_function' );

if( !empty( $_REQUEST['ajax'] ) ) {
	echo $gBitSmarty->fetch( 'bitpackage:fisheye/edit_image_inc.tpl', 'Edit Image: '.$gContent->getTitle() );
} else {
	$gBitSystem->display( 'bitpackage:fisheye/edit_image.tpl', 'Edit Image: '.$gContent->getTitle() );
}
?>
