<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/upload.php,v 1.22 2006/11/16 15:14:04 squareing Exp $
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
global $fisheyeErrors, $fisheyeWarnings, $fisheyeSuccess;

include_once( FISHEYE_PKG_PATH.'gallery_lookup_inc.php' );
require_once( FISHEYE_PKG_PATH.'upload_inc.php');

$gBitSystem->verifyPermission( 'p_fisheye_upload' );

if( !empty( $_REQUEST['save_image'] ) ) {
	// first of all set the execution time for this process to unlimited
	set_time_limit(0);

	$upImages = array();
	$upArchives = array();
	$upErrors = array();

	$i = 0;
	foreach( array_keys( $_FILES ) as $key ) {
		if( preg_match( '/(^image|pdf)/i', $_FILES[$key]['type'] ) ) {
			$upImages[$key] = $_FILES[$key];
			if( !empty( $_REQUEST['imagedata'][$i] ) ) {
				$upData[$key] = $_REQUEST['imagedata'][$i];
			} else {
				$upData[$key] = array();
			}
		} elseif( !empty( $_FILES[$key]['tmp_name'] ) && !empty( $_FILES[$key]['name'] ) ) {
			$upArchives[$key] = $_FILES[$key];
		}
		$i++;
	}

	$galleryAdditions = array();

	// No gallery was specified, let's try to find one or create one.
	if( empty( $_REQUEST['galleryAdditions'] ) ) {
		$_REQUEST['galleryAdditions'] = array( fisheye_get_default_gallery_id( $gBitUser->mUserId, $gBitUser->getDisplayName()."'s Gallery" ) );
	}

	foreach( array_keys( $upArchives ) as $key ) {
		$upErrors = fisheye_process_archive( $upArchives[$key], $gContent, TRUE );
	}

	$order = 100;
	foreach( array_keys( $upImages ) as $key ) {
		fisheye_store_upload( $upImages[$key], $order, $upData[$key] );
		$order += 10;
	}

	if( !is_object( $gContent ) || !$gContent->isValid() ) {
		$gContent = new FisheyeGallery( $_REQUEST['galleryAdditions'][0] );
		$gContent->load();
	}
	if( empty( $upErrors ) ) {
		header( 'Location: '.$gContent->getDisplayUrl() );
		die;
	} else {
		$gBitSmarty->assign( 'errors', $upErrors );
	}
}

require_once( LIBERTY_PKG_PATH.'calculate_max_upload_inc.php' );

$gContent->invokeServices( 'content_edit_function' );

// Get a list of all existing galleries
$gFisheyeGallery = new FisheyeGallery();
$listHash = array(
	'user_id' => $gBitUser->mUserId,
	'max_records'=>-1,
	'no_thumbnails'=>TRUE,
	'sort_mode'=>'title_asc',
	'show_empty' => TRUE,
);
if( $gBitSystem->isFeatureActive( 'fisheye_show_public_on_upload' ) ) {
	$listHash['show_public'] = TRUE;
}
$galleryList = $gFisheyeGallery->getList( $listHash );
$gBitSmarty->assign_by_ref( 'galleryList', $galleryList['data'] );

if( $gBitSystem->isPackageActive( 'gigaupload' ) ) {
	gigaupload_smarty_setup( FISHEYE_PKG_URL.'upload.php' );
} elseif( $gBitSystem->isFeatureActive( 'fisheye_extended_upload_slots' ) ) {
	$uploadSlots = array();
	$uploadSlots = array_pad( $uploadSlots, 9, 0 );
	$gBitSmarty->assign( 'uploadSlots', $uploadSlots );
} else {
	$gBitSmarty->assign( 'loadMultiFile', TRUE );
}

$gBitSystem->display( 'bitpackage:fisheye/upload_fisheye.tpl', 'Upload Images' );
?>
