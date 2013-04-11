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

if( !empty( $gContent ) && $gContent->getField( 'content_type_guid' ) == FISHEYEGALLERY_CONTENT_TYPE_GUID ) {
	$displayCount = empty( $gContent->mItems ) ? 0 : count( $gContent->mItems );
	$thumbCount = $gContent->mInfo['rows_per_page'] * $gContent->mInfo["cols_per_page"];
	$listHash['gallery_id'] = $gContent->mGalleryId;
	$display = $displayCount >= $thumbCount;
}


if( $display ) {
	$listHash['max_records'] = $module_rows;
	if( $gQueryUserId ) {
		$listHash['user_id'] = $gQueryUserId;
	} elseif( !empty( $_REQUEST['user_id'] ) ) {
		$_template->tpl_vars['userGallery'] = new Smarty_variable( $_REQUEST['user_id'] );
		$listHash['user_id'] = $_REQUEST['user_id'];
	} elseif( !empty( $module_params['recent_users'] ) ) {
		$listHash['recent_users'] = TRUE;
	}

	// this is needed to avoid wrong sort_modes entered resulting in db errors
	$sort_options = array( 'hits', 'created' );
	if( !empty( $module_params['sort_mode'] ) && in_array( $module_params['sort_mode'], $sort_options ) ) {
		$sort_mode = $module_params['sort_mode'].'_desc';
	} else {
		$sort_mode = 'random';
	}
	$listHash['sort_mode'] = $sort_mode;

	$images = $image->getList( $listHash );

	if( empty( $title ) && $images ) {
		$moduleTitle = '';
		if( !empty( $module_params['sort_mode'] ) ) {
			if( $module_params['sort_mode'] == 'random' ) {
				$moduleTitle = 'Random';
			} elseif( $module_params['sort_mode'] == 'created' ) {
				$moduleTitle = 'Recent';
			} elseif( $module_params['sort_mode'] == 'hits' ) {
				$moduleTitle = 'Popular';
			}
		} else {
			$moduleTitle = 'Random';
		}

		$moduleTitle .= ' Images';
		$moduleTitle = tra( $moduleTitle );

		if( !empty( $listHash['user_id'] ) ) {
			$moduleTitle .= ' '.tra('by').' '.BitUser::getDisplayNameFromHash( TRUE, current( $images ) );
		} elseif( !empty( $listHash['recent_users'] ) ) {
			$moduleTitle .= ' '.tra( 'by' ).' <a href="'.USERS_PKG_URL.'">'.tra( 'New Users' ).'</a>';
		}

		$listHash['sort_mode'] = $sort_mode;
		$_template->tpl_vars['moduleTitle'] = new Smarty_variable( $moduleTitle );
	} else {
		$_template->tpl_vars['moduleTitle'] = new Smarty_variable( $title );
	}

	$_template->tpl_vars['imageSort'] = new Smarty_variable( $sort_mode );
	$_template->tpl_vars['modImages'] = new Smarty_variable( $images );
	$_template->tpl_vars['module_params'] = new Smarty_variable( $module_params );
	$_template->tpl_vars['maxlen'] = new Smarty_variable( isset( $module_params["maxlen"] ) );
	$_template->tpl_vars['maxlendesc'] = new Smarty_variable( isset( $module_params["maxlendesc"] ) );
}
?>
