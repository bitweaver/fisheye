<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/list_galleries.php,v 1.3 2005/08/01 18:40:07 squareing Exp $
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

$_REQUEST['thumbnail_size'] = $gBitSystem->getPreference( 'fisheye_list_thumbnail_size', 'small' );
$galleryList = $gFisheyeGallery->getList( $_REQUEST );
$gBitSmarty->assign_by_ref('galleryList', $galleryList);

if (!empty($_REQUEST['offset']) && is_numeric($_REQUEST['offset'])) {
	$gBitSmarty->assign_by_ref('iMaxRows', $iMaxRows);
}
if (!empty($_REQUEST['sort_mode'])) {
	$gBitSmarty->assign_by_ref('iSortMode', $_REQUEST['sort_mode']);
}
if (!empty($_REQUEST['search'])) {
	$gBitSmarty->assign_by_ref('iSearchString', $iSearchtring);
}

$gBitSystem->display("bitpackage:fisheye/$template", "List Galleries" );

?>
