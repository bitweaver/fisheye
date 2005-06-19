<?php

require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');
global $gBitSystem, $smarty, $gFisheyeGallery;

$gFisheyeGallery = new FisheyeGallery();

/* Get a list of galleries which matches the imput paramters (default is to list every gallery in the system) */
$_REQUEST['root_only'] = TRUE;
/* Process the input parameters this page accepts */
if (!empty($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id'])) {
	if( $_REQUEST['user_id'] == $gBitUser->mUserId ) {
		$_REQUEST['show_empty'] = TRUE;
	}
	$smarty->assign_by_ref('gQueryUserId', $_REQUEST['user_id']);
	$template = 'user_galleries.tpl';
} else {
	$template = 'list_galleries.tpl';
}

$galleryList = $gFisheyeGallery->getList( $_REQUEST );
$smarty->assign_by_ref('galleryList', $galleryList);

if (!empty($_REQUEST['offset']) && is_numeric($_REQUEST['offset'])) {
	$smarty->assign_by_ref('iMaxRows', $iMaxRows);
}
if (!empty($_REQUEST['sort_mode'])) {
	$smarty->assign_by_ref('iSortMode', $_REQUEST['sort_mode']);
}
if (!empty($_REQUEST['search'])) {
	$smarty->assign_by_ref('iSearchString', $iSearchtring);
}

$gBitSystem->display("bitpackage:fisheye/$template", "List Galleries" );

?>
