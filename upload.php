<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/upload.php,v 1.1.1.1.2.19 2005/11/04 09:25:58 squareing Exp $
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

if( !empty( $_REQUEST['save_image'] ) ) {
	// first of all set the execution time for this process to unlimited
	set_time_limit(0);

	$upImages = array();
	$upArchives = array();
	$upErrors = array();
	foreach( array_keys( $_FILES ) as $key ) {
		if( preg_match( '/(^image|pdf)/i', $_FILES[$key]['type'] ) ) {
			$upImages[$key] = $_FILES[$key];
		} elseif( !empty( $_FILES[$key]['size'] ) ) {
			$upArchives[$key] = $_FILES[$key];
		}
	}

	$galleryAdditions = array();

	// No gallery was specified, let's try to find one or create one.
	if( empty( $_REQUEST['galleryAdditions'] ) ) {
		$_REQUEST['galleryAdditions'] = array( fisheye_get_default_gallery_id( $gBitUser->mUserId, $gBitUser->getDisplayName()."'s Gallery" ) );
	}

	$newGalleries = array();
	foreach( array_keys( $upArchives ) as $key ) {
		$upErrors = fisheye_process_archive( $upArchives[$key], $gContent, TRUE );
	}

	$order = 100;
	foreach( array_keys( $upImages ) as $key ) {
		fisheye_store_upload( $upImages[$key], $order );
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

// settings that are useful to know about at upload time
$postMax = str_replace( 'M', '', ini_get( 'post_max_size' ));
$uploadMax = str_replace( 'M', '', ini_get( 'upload_max_filesize' ) );

if( $postMax < $uploadMax ) {
	$uploadMax = $postMax;
}


if( $gBitSystem->isPackageActive( 'quota' ) ) {
	require_once( QUOTA_PKG_PATH.'LibertyQuota.php' );
	$quota = new LibertyQuota();
	if( !$gBitUser->isAdmin() && !$quota->isUserUnderQuota( $gBitUser->mUserId ) ) {
		$gBitSystem->display( 'bitpackage:quota/over_quota.tpl', tra( 'You are over your quota.' ) );
		die;
	}
	if( !$gBitUser->isAdmin() ) {
		// Prevent people from uploading more than there quota
		$q = $quota->getUserQuota( $gBitUser->mUserId );
		$u = $quota->getUserUsage( $gBitUser->mUserId );
		$gBitSmarty->assign('quotaMessage', tra( 'Your remaining disk quota is' ).' '.round(($q-$u)/1000000, 2).' '.tra('Megabytes') );
		$qMegs = round( $q / 1000000 );
		if( $qMegs < $uploadMax ) {
			$uploadMax = $qMegs;
		}
	}
}

// Get a list of all existing galleries
$gFisheyeGallery = new FisheyeGallery();
$listHash = array( 'user_id' => $gBitUser->mUserId, 'show_empty' => true, 'max_records'=>-1, 'no_thumbnails'=>TRUE, 'sort_mode'=>'title_asc', 'show_empty' => TRUE );
$galleryList = $gFisheyeGallery->getList( $listHash );
$gBitSmarty->assign_by_ref( 'galleryList', $galleryList['data'] );

$gBitSmarty->assign( 'uploadMax', $uploadMax );

$gBitSystem->display( 'bitpackage:fisheye/upload_fisheye.tpl', 'Upload Images' );
?>
