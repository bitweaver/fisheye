<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/edit.php,v 1.2.2.4 2005/07/07 16:48:22 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem;

include_once( FISHEYE_PKG_PATH.'gallery_lookup_inc.php' );

// Ensure the user has the permission to create new image galleries
if (empty($_REQUEST['gallery_id'])) {
	$gBitSystem->verifyPermission('bit_p_create_fisheye');
} elseif( !$gContent->hasUserPermission( 'bit_p_edit_fisheye' ) ) {
	// This user does not own this gallery and they have not been granted the permission to edit this gallery
	$gBitSystem->fatalError( tra( "You cannot edit this image gallery" ) );
}

if( $gBitUser->hasPermission( 'bit_p_change_thumbnail_size' ) ) {
	$thumbnailSizes = array(
		'xsmall' => 'Avatar (100x75)',
		'small' => 'Small (160x120)',
		'medium' => 'Medium (400x300)',
		'large' => 'Large (800x600)',
	);
	$smarty->assign( 'thumbnailSizes', $thumbnailSizes );
}

if( !empty($_REQUEST['savegallery']) ) {
	if( $gContent->store( $_REQUEST ) ) {
		// make sure var is fully stuffed with current data
		$gContent->load();
		// set the mappings, or if nothing checked, nuke them all
		$gContent->addToGalleries( !empty( $_REQUEST['galleryAdditions'] ) ? $_REQUEST['galleryAdditions'] : NULL );

		if( !empty( $_REQUEST['generate_thumbnails'] ) ) {
			$gContent->generateThumbnails();
		}

		// nexus menu item storage
		if( $gBitSystem->isPackageActive( 'nexus' ) && $gBitUser->hasPermission( 'bit_p_insert_nexus_item' ) ) {
			$nexusHash['title'] = ( isset( $_REQUEST['title'] ) ? $_REQUEST['title'] : NULL );
			$nexusHash['hint'] = ( isset( $_REQUEST['edit'] ) ? $_REQUEST['edit'] : NULL );
			include_once( NEXUS_PKG_PATH.'insert_menu_item_inc.php' );
		}
		if ( $gBitSystem->isPackageActive('categories') ) {
			$cat_desc = $gLibertySystem->mContentTypes[FISHEYEGALLERY_CONTENT_TYPE_GUID]['content_description'].' by '.$gBitUser->getDisplayName( FALSE, array( 'real_name' => $gContent->mInfo['creator_real_name'], 'user' => $gContent->mInfo['creator_user'], 'user_id'=>$gContent->mInfo['user_id'] ) );
			$cat_name = $gContent->getTitle();
			$cat_href = $gContent->getDisplayUrl();
			$cat_objid = $gContent->mContentId;
			$cat_content_id = $gContent->mContentId;
			$cat_obj_type = FISHEYEGALLERY_CONTENT_TYPE_GUID;
			include_once( CATEGORIES_PKG_PATH.'categorize_inc.php' );
		}
		header("location: ".$gContent->getDisplayUrl() );
		die();
	}
}elseif( !empty($_REQUEST['delete']) ) {
	$gContent->hasUserPermission( 'bit_p_admin_fisheye', TRUE, tra( "You do not have permission to delete this image gallery" ) );

	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['delete'] = TRUE;
		$formHash['gallery_id'] = $gContent->mGalleryId;
		$formHash['input'] = array(
			'<input name="recurse" value="" type="radio" checked="checked" />'.tra( 'Delete only images in this gallery. Sub-galleries will not be removed.' ),
			'<input name="recurse" value="all" type="radio" /> '.tra( 'Permanently delete all contents, even if they appear in other galleries.' ),
			);
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete the gallery '.$gContent->getTitle().'?', 'error' => 'This cannot be undone!' ) );
	} else {
		$recurseDelete = (!empty( $_REQUEST['recurse'] ) && ($_REQUEST['recurse'] == 'all') );

		if( $gContent->expunge( $recurseDelete ) ) {
			header( "Location: ".FISHEYE_PKG_URL );
		}
	}

} elseif( !empty($_REQUEST['cancelgallery'] ) ) {
	header("location:".FISHEYE_PKG_URL."view.php?gallery_id=".$gContent->mGalleryId);
	die();
}

// Nexus menus
if( $gBitSystem->isPackageActive( 'nexus' ) && $gBitUser->hasPermission( 'bit_p_insert_nexus_item' ) ) {
	include_once( NEXUS_PKG_PATH.'insert_menu_item_inc.php' );
}

if ( $gBitSystem->isPackageActive('categories') ) {
	$cat_type = FISHEYEGALLERY_CONTENT_TYPE_GUID;
	$cat_objid = $gContent->mContentId;
	include_once( CATEGORIES_PKG_PATH.'categorize_list_inc.php' );
}


// Initalize the errors list which contains any errors which occured during storage
$errors = (!empty($gContent->mErrors) ? $gContent->mErrors : array());
$smarty->assign_by_ref('errors', $errors);

$smarty->assign_by_ref( 'parentGalleries', $gContent->getParentGalleries() );
$getHash = array( 'user_id' => $gBitUser->mUserId, 'contain_item' => $gContent->mContentId, 'max_records' => -1, 'no_thumbnails' => TRUE, 'sort_mode'=>'title_asc' );
$galleryList = $gContent->getList( $getHash );
$smarty->assign_by_ref('galleryList', $galleryList);

if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
	global $gGatekeeper;
	$smarty->assign( 'securities', $gGatekeeper->getSecurityList( $gBitUser->mUserId ) );
}

$gBitSystem->display( 'bitpackage:fisheye/edit_gallery.tpl', 'Edit Gallery: '.$gContent->getTitle() );

?>
