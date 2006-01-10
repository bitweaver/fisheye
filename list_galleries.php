<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/list_galleries.php,v 1.5 2006/01/10 21:11:46 squareing Exp $
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

// pagination
$offset = !empty( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
$gBitSmarty->assign( 'curPage', $page = !empty( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1 );
$offset = ( $page - 1 ) * $gBitSystem->mPrefs['maxRecords'];

// calculate page number
$numPages = ceil( $galleryList['cant'] / $gBitSystem->mPrefs['maxRecords'] );
$gBitSmarty->assign( 'numPages', $numPages );

$gBitSmarty->assign( 'galleryList', $galleryList['data'] );

// Display the template
$gDefaultCenter = "bitpackage:fisheye/$template";
$gBitSmarty->assign_by_ref( 'gDefaultCenter', $gDefaultCenter );
$gBitSystem->display( 'bitpackage:kernel/dynamic.tpl', 'List Galleries' );

?>
