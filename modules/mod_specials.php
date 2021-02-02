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
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php' );

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
	
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable( $moduleTitle );
	$_template->tpl_vars['modImages'] = new Smarty_variable( $images );
	$_template->tpl_vars['module_params'] = new Smarty_variable( $module_params );
	$_template->tpl_vars['maxlen'] = new Smarty_variable( isset( $module_params["maxlen"] ) ? $module_params["maxlen"] : 0 );
	$_template->tpl_vars['maxlendesc'] = new Smarty_variable( isset( $module_params["maxlendesc"] ) ? $module_params["maxlendesc"] : 0 );
}
?>
