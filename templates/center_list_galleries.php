<?php
global $gQueryUser;
extract( $moduleParams );
$gFisheyeGallery = new FisheyeGallery();

/* Get a list of galleries which matches the imput paramters (default is to list every gallery in the system) */
$listHash['root_only'] = TRUE;
$listHash['get_thumbnails'] = TRUE;
/*	Not supported in FisheyeGallery::getList
if( !empty( $module_params['gallery_id'] ) && is_numeric( $module_params['gallery_id'] ) ) {
	$listHash['gallery_id'] = $module_params['gallery_id'];
}*/
if ($gQueryUserId) {
	$listHash['user_id'] = $gQueryUserId;
} elseif( !empty( $module_params['user_id'] ) && BitBase::verifyId( $module_params['user_id'] ) ) {
	$listHash['user_id'] = $module_params['user_id'];
}
if( !empty( $module_params['contain_item'] ) && BitBase::verifyId( $module_params['contain_item'] ) ) {
	$listHash['contain_item'] = $module_params['contain_item'];
}
if ( !empty( $module_params['sort_mode'] ) ) {
	$listHash['sort_mode'] = $module_params['sort_mode'];
} else {
	$listHash['sort_mode'] = 'created_desc';
}
if( !empty( $module_params['nav_bar'] ) ){
	$gBitSmarty->assign('navBar', $module_params['nav_bar']);
}else{
	$gBitSmarty->assign('navBar',true);
}
if( !empty( $module_params['max_records'] ) ){
	$listHash['max_records'] = $module_params['max_records'];
}


$galleryList = $gFisheyeGallery->getList( $listHash );
// support for div/ul/li listing of galleries
$gBitSmarty->assign_by_ref( 'galleryList', $galleryList );



/* Process the input parameters this page accepts */
if (!empty($gQueryUser) && $gQueryUser->isRegistered()) {
	$gBitSmarty->assign_by_ref('gQueryUserId', $gQueryUser->mUserId);
	$template = 'user_galleries.tpl';
} else {
	$template = 'list_galleries.tpl';
}
if (!empty($_REQUEST['offset']) && is_numeric($_REQUEST['offset'])) {
	$gBitSmarty->assign_by_ref('iMaxRows', $iMaxRows);
}
if (!empty($_REQUEST['sort_mode'])) {
	$gBitSmarty->assign_by_ref('iSortMode', $_REQUEST['sort_mode']);
}
if (!empty($_REQUEST['search'])) {
	$gBitSmarty->assign_by_ref('iSearchString', $iSearchtring);
}
?>
