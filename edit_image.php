<?php
/**
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
		if( !empty( $_REQUEST['gallery_additions'] ) ) {
			$gContent->addToGalleries( $_REQUEST['gallery_additions'] );
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
		$formHash['delete'] = TRUE;
		$formHash['image_id'] = $gContent->mImageId;
		$gBitSystem->confirmDialog( $formHash,
			array(
				'warning' => tra('Are you sure you want to delete this image?') . ' (' . $gContent->getTitle() . ') ' . tra('It will be removed from all galleries to which it belongs.'),
				'error' => tra('This cannot be undone!'),
			)
		);
	} else {
		if( $gContent->expunge() ) {
			$url = ( is_object( $gGallery ) ? $gGallery->getDisplayUrl() : FISHEYE_PKG_URL );
			header( "Location: $url" );
		}
	}
}

$errors = $gContent->mErrors;
$gBitSmarty->assignByRef('errors', $errors);

$gContent->loadParentGalleries();

// Get a list of all existing galleries
$gFisheyeGallery = new FisheyeGallery();
$getHash = array(
	'user_id'       => $gBitUser->mUserId,
);
if( $gContent->mContentId ) {
	$getHash['contain_item'] = $gContent->mContentId;
}
// modify listHash according to global preferences
if( $gBitSystem->isFeatureActive( 'fisheye_show_all_to_admins' ) && $gBitUser->hasPermission( 'p_fisheye_admin' ) ) {
	unset( $getHash['user_id'] );
} elseif( $gBitSystem->isFeatureActive( 'fisheye_show_public_on_upload' ) ) {
//	$getHash['show_public'] = TRUE;
}
$galleryTree = $gFisheyeGallery->generateList( $getHash,  array( 'name' => "gallery_id", 'id' => "gallerylist", 'item_attributes' => array( 'class'=>'listingtitle'), 'radio_checkbox' => TRUE, ), true );
$gBitSmarty->assignByRef( 'galleryTree', $galleryTree );

$gBitSmarty->assign('requested_gallery', !empty($_REQUEST['gallery_id']) ? $_REQUEST['gallery_id'] : NULL);

$gContent->invokeServices( 'content_edit_function' );

if( !empty( $_REQUEST['ajax'] ) ) {
	echo $gBitSmarty->fetch( 'bitpackage:fisheye/edit_image_inc.tpl', tra('Edit Image: ').$gContent->getTitle() );
} else {
	$gBitSystem->display( 'bitpackage:fisheye/edit_image.tpl', tra('Edit Image: ').$gContent->getTitle() , array( 'display_mode' => 'edit' ));
}
?>
