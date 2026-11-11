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
global $fisheyeErrors, $fisheyeWarnings, $fisheyeSuccess, $gFisheyeUploads;

include_once( FISHEYE_PKG_INCLUDE_PATH.'gallery_lookup_inc.php' );
require_once( FISHEYE_PKG_INCLUDE_PATH.'upload_inc.php');

$gBitSystem->verifyPermission( 'p_fisheye_upload' );

// The page script sends one file per request and reads the answer as JSON
$feAjaxUpload = $gBitThemes->isAjaxRequest();

if( !empty( $_FILES ) ) {
	if( $feAjaxUpload ) {
		// a fatal error page is no use to a script; answer the ticket check in kind
		if( empty( $_REQUEST['tk'] ) || !is_string( $_REQUEST['tk'] ) || !hash_equals( (string)$gBitUser->mTicket, $_REQUEST['tk'] ) ) {
			http_response_code( 403 );
			header( 'Content-Type: application/json; charset=utf-8' );
			print json_encode( array( 'ok' => FALSE, 'errors' => array( tra( 'Your session has expired. Reload the page and try again.' ) ) ) );
			die;
		}
	} else {
		$gBitUser->verifyTicket();
	}
	// One photo sent on its own gets its thumbnails made now, so it shows at once.
	// A batch is left to the background thumbnailer when the site uses one. Only this script may set the flag.
	unset( $_REQUEST['thumbnail_immediate'] );
	if( $feAjaxUpload && isset( $_POST['fe_pending'] ) && (int)$_POST['fe_pending'] === 1 && count( $_FILES ) == 1 ) {
		$_REQUEST['thumbnail_immediate'] = TRUE;
	}
	$upErrors = fisheye_handle_upload( $_FILES, $_REQUEST );
	if( $feAjaxUpload ) {
		$uploaded = array();
		if( !empty( $gFisheyeUploads ) ) {
			foreach( $gFisheyeUploads as $upImage ) {
				$uploaded[] = array( 'image_id' => (int)$upImage->getField( 'image_id' ), 'content_id' => (int)$upImage->getField( 'content_id' ), 'title' => (string)$upImage->getTitle() );
			}
		}
		$errors = array();
		foreach( (array)$upErrors as $upError ) {
			$errors[] = is_scalar( $upError ) ? (string)$upError : json_encode( $upError );
		}
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Cache-Control: no-store' );
		print json_encode( array( 'ok' => empty( $errors ), 'errors' => $errors, 'uploaded' => $uploaded ) );
		die;
	}
	if( empty( $upErrors ) ) {
		bit_redirect( $gContent->getDisplayUrl() );
	} else {
		$gBitSmarty->assign( 'errors', $upErrors );
	}
} elseif( $feAjaxUpload && $_SERVER['REQUEST_METHOD'] == 'POST' ) {
	// a post that arrives without its file was larger than the server accepts
	http_response_code( 413 );
	header( 'Content-Type: application/json; charset=utf-8' );
	print json_encode( array( 'ok' => FALSE, 'errors' => array( tra( 'The file is larger than the server accepts.' ) ) ) );
	die;
}

if ( !empty($_REQUEST['on_complete'])){
	if($_REQUEST['on_complete'] == 'refreshparent'){
		$gBitSmarty->assign('onComplete','refreshparent');
	}

}

require_once( LIBERTY_PKG_INCLUDE_PATH.'calculate_max_upload_inc.php' );

$gContent->invokeServices( 'content_edit_function' );

// Where the photos go: the gallery in the request, else the user's newest root gallery.
// With neither, the upload handler creates a first gallery for the user.
$feTarget = NULL;
if( $gContent->isValid() ) {
	$feTarget = array( 'gallery_id' => $gContent->mGalleryId, 'title' => $gContent->getTitle(), 'display_url' => $gContent->getDisplayUrl() );
} else {
	$rail = $gContent->getRailList( $gBitUser->mUserId, 1 );
	if( $newest = current( $rail['galleries'] ) ) {
		$feTarget = array( 'gallery_id' => $newest['gallery_id'], 'title' => $newest['title'], 'display_url' => $newest['display_url'] );
	}
}
$gBitSmarty->assign( 'feTarget', $feTarget );
$gBitSmarty->assign( 'feNewGalleryName', $gBitUser->getDisplayName()."'s Gallery" );
// largest file the page should try to send, in bytes
$gBitSmarty->assign( 'feUploadMaxBytes', (int)( (float)$uploadMax * 1048576 ) );

if( $gLibertySystem->hasService( 'upload' ) ) {
	$gContent->invokeServices( "content_pre_upload_function", $_REQUEST );
}

$displayMode = !empty($_REQUEST['display_mode']) ? $_REQUEST['display_mode'] : 'edit';
$gBitSystem->setRequestConfig( 'layout-body', '-fluid' );
$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'js/fisheye.js', FALSE, 600, FALSE, FALSE );
$gBitSystem->display( 'bitpackage:fisheye/upload_fisheye.tpl', tra( 'Upload Photos' ), array( 'display_mode' => $displayMode ));
