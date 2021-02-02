<?php
/**
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem;
global $fisheyeErrors, $fisheyeWarnings, $fisheyeSuccess, $gFisheyeUploads;

include_once( FISHEYE_PKG_INCLUDE_PATH.'gallery_lookup_inc.php' );
require_once( FISHEYE_PKG_INCLUDE_PATH.'upload_inc.php');

$gBitSystem->verifyPermission( 'p_fisheye_upload' );

if( !empty( $_FILES ) ) {
	$upErrors = fisheye_handle_upload( $_FILES );
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

require_once( LIBERTY_PKG_INCLUDE_PATH.'calculate_max_upload_inc.php' );

$gContent->invokeServices( 'content_edit_function' );

// Get a list of all existing galleries
$gFisheyeGallery = new FisheyeGallery();
$getHash = array(
	'user_id'       => $gBitUser->mUserId,
);
// modify listHash according to global preferences
if( $gBitSystem->isFeatureActive( 'fisheye_show_all_to_admins' ) && $gBitUser->hasPermission( 'p_fisheye_admin' ) ) {
	unset( $getHash['user_id'] );
} elseif( $gBitSystem->isFeatureActive( 'fisheye_show_public_on_upload' ) ) {
//	$getHash['show_public'] = TRUE; THis should be handled with a content_status, disabled for now
}

$galleryTree = $gContent->generateList( $getHash,  array( 'name' => "gallery_id", 'id' => "gallerylist", 'item_attributes' => array( 'class'=>'listingtitle'), 'radio_checkbox' => TRUE, ), true );

$gBitSmarty->assignByRef( 'galleryTree', $galleryTree );

if( $gLibertySystem->hasService( 'upload' ) ) {
	$gContent->invokeServices( "content_pre_upload_function", $_REQUEST );
} else {
	$gBitThemes->loadJavascript( UTIL_PKG_PATH.'javascript/multifile.js', TRUE );
}

if( $gBitThemes->isAjaxRequest() ) {
	if( !empty( $upErrors ) ) {
		print json_encode( $upErrors );
	}
} else {
	$displayMode = !empty($_REQUEST['display_mode']) ? $_REQUEST['display_mode'] : 'edit';
	$gBitSystem->display( 'bitpackage:fisheye/upload_fisheye.tpl', 'Upload Images' , array( 'display_mode' => $displayMode ));
}

?>
