<?php
// $Header: /cvsroot/bitweaver/_bit_fisheye/modules/mod_images.php,v 1.2 2005/06/21 18:17:51 squareing Exp $
global $gQueryUserId, $module_rows, $module_params;

require_once( FISHEYE_PKG_PATH.'FisheyeImage.php' );

$image = new FisheyeImage();

$listHash = $module_params;
$listHash['max_records'] = $module_rows;
$listHash['user_id'] = $gQueryUserId;

// this is needed to avoid wrong sort_modes entered resulting in db errors
$sort_options = array( 'hits', 'created' );
if( !empty( $module_params['sort_mode'] ) && in_array( $module_params['sort_mode'], $sort_options ) ) {
	$sort_mode = $module_params['sort_mode'].'_asc';
} else {
	$sort_mode = 'random';
}
$listHash['sort_mode'] = $sort_mode;

$images = $image->getList( $listHash );

$smarty->assign( 'modImages', $images );
$smarty->assign( 'module_params', $module_params );
$smarty->assign( 'maxlen', isset( $module_params["maxlen"] ) ? $module_params["maxlen"] : 0 );
$smarty->assign( 'maxlendesc', isset( $module_params["maxlendesc"] ) ? $module_params["maxlendesc"] : 0 );
?>
