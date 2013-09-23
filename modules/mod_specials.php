<?php
/**
 * @version $Header$
 * @package fisheye
 * @subpackage modules
 */

global $gQueryUserId, $gContent, $moduleParams;

// makes things in older modules easier
extract( $moduleParams );

/**
 * required setup
 */
require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php' );

$image = new FisheyeImage();

$display = TRUE;

$listHash = $module_params;
$listHash['gallery_id'] = 3;

if( $display ) {
	$listHash['size'] = 'medium';
	$listHash['max_records'] = 5;
	$listHash['sort_mode'] = 'random';
	$images = $image->getList( $listHash );

	$moduleTitle = 'Specials';
	$moduleTitle = tra( $moduleTitle );

	$gBitSmarty->assign( 'moduleTitle', $moduleTitle );
	$gBitSmarty->assign( 'modImages', $images );
	$gBitSmarty->assign( 'module_params', $module_params );
	$gBitSmarty->assign( 'maxlen', isset( $module_params["maxlen"] ) ? $module_params["maxlen"] : 0 );
	$gBitSmarty->assign( 'maxlendesc', isset( $module_params["maxlendesc"] ) ? $module_params["maxlendesc"] : 0 );
}
?>
