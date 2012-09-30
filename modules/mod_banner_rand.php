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

$listHash['size'] = $title;
$listHash['gallery_id'] = $module_rows;
$listHash['max_records'] = 1;
$listHash['sort_mode'] = 'random';

$images = $image->getList( $listHash );
$moduleTitle = 'Banner Image';
$gBitSmarty->assign( 'moduleTitle', $title );
$gBitSmarty->assign( 'modImages', $images );
$gBitSmarty->assign( 'module_params', $module_params );
?>
