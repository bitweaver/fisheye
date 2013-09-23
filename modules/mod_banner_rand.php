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
$listHash['max_records'] = 5;
$listHash['sort_mode'] = 'random';

$images = $image->getList( $listHash );
$moduleTitle = 'Banner Image';
$_template->tpl_vars['moduleTitle'] = new Smarty_variable( $title );
$_template->tpl_vars['modImages'] = new Smarty_variable( $images );
$_template->tpl_vars['module_params'] = new Smarty_variable( $module_params );
?>
