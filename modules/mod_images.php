<?php
// $Header: /cvsroot/bitweaver/_bit_fisheye/modules/mod_images.php,v 1.1 2005/06/21 10:24:40 squareing Exp $
global $gQueryUserId, $module_rows, $module_params;

require_once( FISHEYE_PKG_PATH.'FisheyeImage.php' );

$image = new FisheyeImage();

$listHash = $module_params;
$listHash['max_records'] = $module_rows;
$listHash['user_id'] = $gQueryUserId;
$listHash['sort_mode'] = !empty( $module_params['sort_mode'] ) ? $module_params['sort_mode'].'_asc' : 'random';

$images = $image->getList( $listHash );

$smarty->assign( 'modImages', $images );
$smarty->assign( 'module_params', $module_params );
$smarty->assign( 'maxlen', isset( $module_params["maxlen"] ) ? $module_params["maxlen"] : 0 );
$smarty->assign( 'maxlendesc', isset( $module_params["maxlendesc"] ) ? $module_params["maxlendesc"] : 0 );
?>
