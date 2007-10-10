<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/list_galleries.php,v 1.11 2007/10/10 12:10:20 nickpalmer Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');
global $gBitSystem, $gBitSmarty, $gFisheyeGallery;

$gFisheyeGallery = new FisheyeGallery();

/* Get a list of galleries which matches the imput paramters (default is to list every gallery in the system) */
$_REQUEST['root_only'] = TRUE;
/* Process the input parameters this page accepts */
if (!empty($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id'])) {
	if( $_REQUEST['user_id'] == $gBitUser->mUserId ) {
		$_REQUEST['show_empty'] = TRUE;
	}
	$gBitSmarty->assign_by_ref('gQueryUserId', $_REQUEST['user_id']);
	$template = 'user_galleries.tpl';
} else {
	$template = 'list_galleries.tpl';
}

$_REQUEST['thumbnail_size'] = $gBitSystem->getConfig( 'fisheye_list_thumbnail_size', 'small' );
$galleryList = $gFisheyeGallery->getList( $_REQUEST );
$gFisheyeGallery->invokeServices( 'content_list_function', $_REQUEST );
// Pagination Data
$gBitSmarty->assign_by_ref( 'listInfo', $_REQUEST['listInfo'] );
$gBitSmarty->assign( 'galleryList', $galleryList );

// Display the template
$gDefaultCenter = "bitpackage:fisheye/$template";
$gBitSmarty->assign_by_ref( 'gDefaultCenter', $gDefaultCenter );
$gBitSystem->display( 'bitpackage:kernel/dynamic.tpl', 'List Galleries' );

?>
