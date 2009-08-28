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
} elseif( !empty( $module_params['user_id'] ) && is_numeric( $module_params['user_id'] ) ) {
	$listHash['user_id'] = $module_params['user_id'];
}
if( !empty( $module_params['contain_item'] ) && is_numeric( $module_params['contain_item'] ) ) {
	$listHash['contain_item'] = $module_params['contain_item'];
}
if ( !empty( $module_params['sort_mode'] ) ) {
	$listHash['sort_mode'] = $module_params['sort_mode'];
} else {
	$listHash['sort_mode'] = 'title';
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
if( !empty( $moduleParams['module_params']['columns'] ) ){
	// support for tabled listing of galleries
	$columnCount  = $moduleParams['module_params']['columns'];

	$num_gallery_count = count( $galleryList );

	if( $num_gallery_count < $columnCount  ) {
		$col_width = 100/$num_gallery_count;
	} else {
		$col_width = 100/$columnCount;
	}

	$gBitSmarty->assign( 'listColWidth', $col_width );

	$row = 0;
	$col = 0;

	foreach( $galleryList as $gallery ) {
		$listBoxContents[$row][$col] = $gallery;
		$col ++;
		if ($col > ($columnCount - 1)) {
			$col = 0;
			$row ++;
		}
	}
	
	$gBitSmarty->assign_by_ref( 'listBoxContents', $listBoxContents );
} else {
	// support for div/ul/li listing of galleries
	$gBitSmarty->assign_by_ref( 'galleryList', $galleryList );
}



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
