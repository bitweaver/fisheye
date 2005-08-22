<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/modules/mod_images.php,v 1.2.2.10 2005/08/22 05:52:17 spiderr Exp $
 * @package fisheye
 * @subpackage modules
 */

global $gQueryUserId, $module_rows, $module_params, $gContent;

/**
 * required setup
 */
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php' );

$image = new FisheyeImage();

$display = TRUE;

$listHash = $module_params;
if( !empty( $gContent ) && $gContent->mInfo['content_type_guid'] == FISHEYEGALLERY_CONTENT_TYPE_GUID ) {
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
		$gBitSmarty->assign( 'userGallery', $_REQUEST['user_id'] );
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

	if( empty( $module_title ) && $images ) {
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
			$moduleTitle .= ' '.tra('by').' '.BitUser::getDisplayName( TRUE, current( $images ) );
		} elseif( !empty( $listHash['recent_users'] ) ) {
			$moduleTitle .= ' '.tra( 'by' ).' <a href="'.USERS_PKG_URL.'">'.tra( 'New Users' ).'</a>';
		}

		$listHash['sort_mode'] = $sort_mode;
		$gBitSmarty->assign( 'moduleTitle', $moduleTitle );
	}

	$gBitSmarty->assign( 'imageSort', $sort_mode );
	$gBitSmarty->assign( 'modImages', $images );
	$gBitSmarty->assign( 'module_params', $module_params );
	$gBitSmarty->assign( 'maxlen', isset( $module_params["maxlen"] ) ? $module_params["maxlen"] : 0 );
	$gBitSmarty->assign( 'maxlendesc', isset( $module_params["maxlendesc"] ) ? $module_params["maxlendesc"] : 0 );
}
?>
