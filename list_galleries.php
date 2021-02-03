<?php
/**
 * @version $Header$
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );

require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php');
global $gBitSystem, $gBitSmarty, $gFisheyeGallery;

//$gBitSystem->verifyPermission( 'p_fisheye_list_galleries' );

$gFisheyeGallery = new FisheyeGallery();

/* Get a list of galleries which matches the input parameters (default is to list every gallery in the system) */
$_REQUEST['root_only'] = TRUE;
/* Process the input parameters this page accepts */
if (!empty($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id'])) {
	if( $_REQUEST['user_id'] == $gBitUser->mUserId ) {
		$_REQUEST['show_empty'] = TRUE;
	}
	$gBitSmarty->assignByRef('gQueryUserId', $_REQUEST['user_id']);
	$template = 'user_galleries.tpl';
} else {
	$template = 'list_galleries.tpl';
}

$_REQUEST['thumbnail_size'] = $gBitSystem->getConfig( 'fisheye_list_thumbnail_size', 'small' );

$galleryList = $gFisheyeGallery->getList( $_REQUEST );
$gFisheyeGallery->invokeServices( 'content_list_function', $_REQUEST );
// Pagination Data
$gBitSmarty->assignByRef( 'listInfo', $_REQUEST['listInfo'] );
$gBitSmarty->assign( 'galleryList', $galleryList );

// Display the template
$gDefaultCenter = "bitpackage:fisheye/$template";
$gBitSmarty->assignByRef( 'gDefaultCenter', $gDefaultCenter );
$gBitSystem->display( 'bitpackage:kernel/dynamic.tpl', 'List Galleries' , array( 'display_mode' => 'list' ));

?>
