<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/upload.php,v 1.45 2009/09/25 19:51:44 tylerbello Exp $
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
global $fisheyeErrors, $fisheyeWarnings, $fisheyeSuccess, $gFisheyeUploads;

include_once( FISHEYE_PKG_PATH.'gallery_lookup_inc.php' );
require_once( FISHEYE_PKG_PATH.'upload_inc.php');

$gBitSystem->verifyPermission( 'p_fisheye_upload' );

if( !empty( $_REQUEST['save_image'] ) ) {
	// first of all set the execution time for this process to unlimited
	set_time_limit(0);

	$upImages = array();
	$upArchives = array();
	$upErrors = array();
	$upData = array();

	$i = 0;
	usort( $_FILES, 'fisheye_sort_uploads' );
	foreach( array_keys( $_FILES ) as $key ) {
		if( preg_match( '/(^image|pdf)/i', $_FILES[$key]['type'] ) ) {
			$upImages[$key] = $_FILES[$key];
			// clone the request data so edit service values are passed into store process
			$upData[$key] = $_REQUEST;
			// add the form data for each upload
			if( !empty( $_REQUEST['imagedata'][$i] ) ) {
				array_merge( $upData[$key], $_REQUEST['imagedata'][$i] );
			}
		} elseif( !empty( $_FILES[$key]['tmp_name'] ) && !empty( $_FILES[$key]['name'] ) ) {
			$upArchives[$key] = $_FILES[$key];
		}
		$i++;
	}

	$gallery_additions = array();

	// No gallery was specified, let's try to find one or create one.
	if( empty( $_REQUEST['gallery_additions'] ) ) {
		if( $gBitUser->hasPermission( 'p_fisheye_create' )) {
			$_REQUEST['gallery_additions'] = array( fisheye_get_default_gallery_id( $gBitUser->mUserId, $gBitUser->getDisplayName()."'s Gallery" ) );
		} else {
			$gBitSystem->fatalError( tra( "You don't have permissions to create a new gallery. Please select an existing one to insert your images to." ));
		}
	}

	foreach( array_keys( $upArchives ) as $key ) {
		$upErrors = fisheye_process_archive( $upArchives[$key], $gContent, TRUE );
	}

	foreach( array_keys( $upImages ) as $key ) {
		// resize original if we the user requests it
		if( !empty( $_REQUEST['resize'] ) ) {
			$upImages[$key]['resize'] = $_REQUEST['resize'];
		}
		$upErrors = array_merge( $upErrors, fisheye_store_upload( $upImages[$key], $upData[$key], !empty( $_REQUEST['rotate_image'] )));
	}

	if( !is_object( $gContent ) || !$gContent->isValid() ) {
		$gContent = new FisheyeGallery( $_REQUEST['gallery_additions'][0] );
		$gContent->load();
	}

	if( !empty( $gFisheyeUploads ) ){
		$_REQUEST['uploaded_objects'] = &$gFisheyeUploads;
		$gContent->invokeServices( "content_post_upload_function", $_REQUEST );
	}

	if( empty( $upErrors ) ) {
		bit_redirect( $gContent->getDisplayUrl() );
	} else {
		$gBitSmarty->assign( 'errors', $upErrors );
	}
}

if ( !empty($_REQUEST['on_complete'])){
	if($_REQUEST['on_complete'] == 'refreshparent'){	
		$gBitSmarty->assign('onComplete','window.opener.location.reload(true);self.close();');
	}

}

require_once( LIBERTY_PKG_PATH.'calculate_max_upload_inc.php' );

$gContent->invokeServices( 'content_edit_function' );

// Get a list of all existing galleries
$gFisheyeGallery = new FisheyeGallery();
$listHash = array(
	'user_id'       => $gBitUser->mUserId,
	'max_records'   => -1,
	'no_thumbnails' => TRUE,
	'sort_mode'     => 'title_asc',
	'show_empty'    => TRUE,
);
// modify listHash according to global preferences
if( $gBitSystem->isFeatureActive( 'fisheye_show_all_to_admins' ) && $gBitUser->hasPermission( 'p_fisheye_admin' ) ) {
	unset( $listHash['user_id'] );
} elseif( $gBitSystem->isFeatureActive( 'fisheye_show_public_on_upload' ) ) {
	$listHash['show_public'] = TRUE;
}
$galleryList = $gFisheyeGallery->getList( $listHash );

if( @BitBase::verifyId( $_REQUEST['gallery_id'] ) && empty( $galleryList[$_REQUEST['gallery_id']] ) ) {
	if( $addGallery = new FisheyeGallery( $_REQUEST['gallery_id'] ) ) {
		if( $addGallery->load() && $addGallery->hasUpdatePermission() ) {
			$galleryList[$_REQUEST['gallery_id']] = $addGallery->mInfo;
		}
	}
}

$gBitSmarty->assign_by_ref( 'galleryList', $galleryList );

if( $gLibertySystem->hasService( 'upload' ) ) {
	$gContent->invokeServices( "content_pre_upload_function", $_REQUEST );
} elseif( $gBitSystem->isFeatureActive( 'fisheye_extended_upload_slots' ) ) {
	$gBitThemes->loadAjax( 'mochikit' );
} else {
	$gBitThemes->loadJavascript( UTIL_PKG_PATH.'javascript/libs/multifile.js', TRUE );
}

$displayMode = !empty($_REQUEST['display_mode']) ? $_REQUEST['display_mode'] : 'edit';

$gBitSystem->display( 'bitpackage:fisheye/upload_fisheye.tpl', 'Upload Images' , array( 'display_mode' => $displayMode ));

?>
