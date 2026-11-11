<?php
/**
 * Photo details: name, description, replacing or turning the file, the
 * galleries the photo is in, and delete.
 *
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );

require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeImage.php');

global $gBitSystem;

include_once( FISHEYE_PKG_INCLUDE_PATH.'image_lookup_inc.php' );

if( $gContent->isValid() ) {
	$gContent->verifyUpdatePermission();
} else {
	bit_redirect( FISHEYE_PKG_URL.'?user_id='.$gBitUser->mUserId );
}

//Utility function, maybe should be moved for use elsewhere. Seems like it has a multitude of possible hook points
function convertSmartQuotes($string)
{
//UTF-8
$search = array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6");
$replace= array("'", "'", '"', '"', '-', '--', '...');

$string = str_replace($search, $replace, $string);

//Windows
$search = array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133));
$replace=  array("'", "'", '"', '"', '-', '--', '...');

$string = str_replace($search, $replace, $string);

return $string;
}
if( !empty($_REQUEST['edit'])){
	global $gBitContent;
	$_REQUEST['edit'] = convertSmartQuotes($_REQUEST['edit']);
}

if( !empty($_REQUEST['saveImage']) || !empty($_REQUEST['regenerateThumbnails'] ) ) {
	$gBitUser->verifyTicket();

	if (empty($_REQUEST['gallery_id']) && empty($_REQUEST['image_id'])) {
		// We have no way to know what gallery to add an image to or what image to edit!
		$gBitSmarty->assign( 'msg', tra( "No gallery or image was specified" ) );
		$gBitSystem->display( "error.tpl" , NULL, array( 'display_mode' => 'edit' ));
		die;
	}

	// Store/Update the image
	if (isset($_FILES['imageFile']) && is_uploaded_file($_FILES['imageFile']['tmp_name'])) {
		$_REQUEST['_files_override'] = array( $_FILES['imageFile'] );
		$_REQUEST['_files_override']['process_storage'] = STORAGE_IMAGE;
		$replaceOriginal=$gContent->getSourceFile();
		if( file_exists( dirname( $replaceOriginal ).'/original.jpg' ) ) {
			unlink( dirname( $replaceOriginal ).'/original.jpg' );
		}
	}

	$_REQUEST['purge_from_galleries'] = TRUE;
	if( $gContent->store($_REQUEST) ) {
		// refresh all hashes
		$gContent->load();

		// if user uploaded a file with a different name, delete the previous original file
		if( !empty( $replaceOriginal ) && $replaceOriginal != $gContent->getSourceFile() && file_exists( $replaceOriginal ) ) {
			unlink( $replaceOriginal );
		}

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
		// with nothing ticked the photo stays where it is; a photo is never left in no gallery from this form
		if( !empty( $_REQUEST['gallery_additions'] ) && is_array( $_REQUEST['gallery_additions'] ) ) {
			$gContent->addToGalleries( array_filter( $_REQUEST['gallery_additions'], array( 'BitBase', 'verifyId' ) ) );
		}
		if( !empty( $_REQUEST['generate_thumbnails'] ) ) {
			$gContent->generateThumbnails();
		}
		if( empty( $gContent->mErrors ) ) {
			// add a refresh parameter to the URL so the thumbnails will properly refresh first go reload
			header( 'Location: '.$gContent->getDisplayUrl().($gBitSystem->isFeatureActive( 'pretty_urls' ) ? '?' : '&' ).'refresh=1' );
			die;
		}
	}
} elseif( !empty($_REQUEST['ajax_edit']) ) {
	if( !empty( $_REQUEST['rotate_image'] ) ) {
		$gContent->rotateImage( $_REQUEST['rotate_image'], TRUE );
	}
} elseif( !empty($_REQUEST['delete']) ) {
	$gContent->verifyUserPermission( tra( "You do not have permission to delete this image." ) );

	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		// the package's own confirmation page; with the page script a dialog asks instead and this is not reached
		$gContent->loadParentGalleries();
		$gBitSmarty->assign( 'feParentCount', !empty( $gContent->mInfo['parent_galleries'] ) ? count( $gContent->mInfo['parent_galleries'] ) : 0 );
		$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
		$gBitSystem->display( 'bitpackage:fisheye/delete_image.tpl', tra( 'Delete' ).': '.$gContent->getTitle(), array( 'display_mode' => 'edit' ) );
		die;
	} else {
		$gBitUser->verifyTicket();
		if( $gContent->expunge() ) {
			$url = ( is_object( $gGallery ) ? $gGallery->getDisplayUrl() : FISHEYE_PKG_URL );
			header( "Location: $url" );
			die;
		}
	}
}

$errors = $gContent->mErrors;
$gBitSmarty->assignByRef('errors', $errors);

$gContent->loadParentGalleries();

// The galleries the photo is in. Others are added through the gallery picker, so no tree of every gallery is built here.
$gBitSmarty->assign( 'feParents', !empty( $gContent->mInfo['parent_galleries'] ) ? $gContent->mInfo['parent_galleries'] : array() );

$gBitSmarty->assign('requested_gallery', !empty($_REQUEST['gallery_id']) ? $_REQUEST['gallery_id'] : NULL);

$gContent->invokeServices( 'content_edit_function' );

if( !empty( $_REQUEST['ajax'] ) ) {
	echo $gBitSmarty->fetch( 'bitpackage:fisheye/edit_image_inc.tpl', tra('Edit Image: ').$gContent->getTitle() );
} else {
	$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
	$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'js/fisheye.js', FALSE, 600, FALSE, FALSE );
	$gBitSystem->display( 'bitpackage:fisheye/edit_image.tpl', tra('Edit Image: ').$gContent->getTitle() , array( 'display_mode' => 'edit' ));
}
?>
