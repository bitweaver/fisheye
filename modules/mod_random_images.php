<?php
global $gQueryUserId, $module_rows, $module_params, $gGallery;

require_once( FISHEYE_PKG_PATH.'FisheyeImage.php' );

$image = new FisheyeImage();

$listHash = $module_params;
$listHash['max_records'] = $module_rows;
$listHash['user_id'] = $gQueryUserId;
$listHash['sort_mode'] = 'random';

if( empty( $module_title ) ) {
	if( !empty( $module_params['content_type_guid'] ) && !empty( $gLibertySystem->mContentTypes[$module_params['content_type_guid']] ) ) {
		$title = tra( "Random Images" ).': '.tra( $gLibertySystem->mContentTypes[$module_params['content_type_guid']]['content_description'] );
	} else {
		$smarty->assign( 'showContentType', TRUE );
		$title = tra( "Random Images" );
	}
	$smarty->assign( 'moduleTitle', $title );
}

$images = $image->getList( $listHash );

$smarty->assign('modImages', $images );
$smarty->assign('maxlen', isset($module_params["maxlen"]) ? $module_params["maxlen"] : 0);
$smarty->assign('nonums', isset($module_params["nonums"]) ? $module_params["nonums"] : 'y');
?>
