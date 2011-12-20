<?php
/**
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem;

include_once( FISHEYE_PKG_PATH.'gallery_lookup_inc.php' );

// Ensure the user has the permission to create new image galleries
if( $gContent->isValid() ){
	$gContent->verifyUpdatePermission();
}else{
	$gContent->verifyCreatePermission();
}

if( $gBitUser->hasPermission( 'p_fisheye_change_thumb_size' ) ) {
	$gBitSmarty->assign( 'thumbnailSizes', get_image_size_options( NULL ));
}

$gBitSmarty->assign( 'galleryPaginationTypes',
	array(
		FISHEYE_PAGINATION_FIXED_GRID      => 'Fixed Grid',
		FISHEYE_PAGINATION_AUTO_FLOW       => 'Auto-Flow Images',
		FISHEYE_PAGINATION_POSITION_NUMBER => 'Image Order Page Number',
		FISHEYE_PAGINATION_SIMPLE_LIST     => 'Simple List',
		FISHEYE_PAGINATION_MATTEO		   => 'Matteo',
		FISHEYE_PAGINATION_GALLERIFFIC     => 'Galleriffic'
	)
);

if( !empty( $_REQUEST['savegallery'] ) ) {
	if( $_REQUEST['gallery_pagination'] == 'auto_flow' ) {
		$_REQUEST['rows_per_page'] = $_REQUEST['total_per_page'];
		$_REQUEST['cols_per_page'] = '1';
	} elseif ( $_REQUEST['gallery_pagination'] == 'simple_list' ) {
		$_REQUEST['rows_per_page'] = $_REQUEST['lines_per_page'];
		$_REQUEST['cols_per_page'] = '1';
	} elseif ( $_REQUEST['gallery_pagination'] == 'matteo' ) {
		$_REQUEST['rows_per_page'] = $_REQUEST['images_per_page'];
		$_REQUEST['cols_per_page'] = '1';
	}
	if( $gContent->store( $_REQUEST ) ) {
		$gContent->storePreference( 'is_public', !empty( $_REQUEST['is_public'] ) ? $_REQUEST['is_public'] : NULL );
		$gContent->storePreference( 'allow_comments', !empty( $_REQUEST['allow_comments'] ) ? $_REQUEST['allow_comments'] : NULL );
		$gContent->storePreference( 'gallery_pagination', !empty( $_REQUEST['gallery_pagination'] ) ? $_REQUEST['gallery_pagination'] : NULL );
		$gContent->storePreference( 'link_original_images', !empty( $_REQUEST['link_original_images'] ) ? $_REQUEST['link_original_images'] : NULL );
		// make sure var is fully stuffed with current data
		$gContent->load();
		// set the mappings, or if nothing checked, nuke them all
		$gContent->addToGalleries( !empty( $_REQUEST['gallery_additions'] ) ? $_REQUEST['gallery_additions'] : NULL );

		if( !empty( $_REQUEST['generate_thumbnails'] ) ) {
			$gContent->generateThumbnails();
		}

		header("location: ".$gContent->getDisplayUrl() );
		die();
	}
} elseif( !empty( $_REQUEST['delete'] ) ) {
	$gContent->hasUserPermission( 'p_fisheye_admin', TRUE, tra( "You do not have permission to delete this image gallery" ) );

	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['delete'] = TRUE;
		$formHash['gallery_id'] = $gContent->mGalleryId;
		$formHash['input'] = array(
			'<label><input name="recurse" value="" type="radio" checked="checked" /> '.tra( 'Delete only images in this gallery. Sub-galleries will not be removed.' ).'</label>',
			'<label><input name="recurse" value="all" type="radio" /> '.tra( 'Permanently delete all contents, even if they appear in other galleries.' ).'</label>',
		);
		$gBitSystem->confirmDialog( $formHash, 
			array( 
				'warning' => tra('Are you sure you want to delete this gallery?') . ' ' . $gContent->getTitle(),
				'error' => tra('This cannot be undone!'),
			)
		);
	} else {
		$userId = $gContent->getField( 'user_id' );

		$recurseDelete = (!empty( $_REQUEST['recurse'] ) && ($_REQUEST['recurse'] == 'all') );

		if( $gContent->expunge( $recurseDelete ) ) {
			header( "Location: ".FISHEYE_PKG_URL.'?user_id='.$userId );
		}
	}

} elseif( !empty($_REQUEST['cancelgallery'] ) ) {
	header( 'Location: '.$gContent->getDisplayUrl() );
	die();
}

// Initalize the errors list which contains any errors which occured during storage
$errors = (!empty($gContent->mErrors) ? $gContent->mErrors : array());
$gBitSmarty->assign_by_ref('errors', $errors);

$gBitSystem->setOnloadScript( 'updateGalleryPagination();' );

$gallery = $gContent->getParentGalleries();
$gBitSmarty->assign_by_ref( 'parentGalleries', $gallery );
$getHash = array(
	'user_id'       => $gBitUser->mUserId,
//	'max_records'   => -1,
//	'no_thumbnails' => TRUE,
//	'sort_mode'     => 'title_asc',
//	'show_empty'    => TRUE,
);
if( $gContent->mContentId ) {
	$getHash['contain_item'] = $gContent->mContentId;
}
// modify listHash according to global preferences
if( $gBitSystem->isFeatureActive( 'fisheye_show_all_to_admins' ) && $gBitUser->hasPermission( 'p_fisheye_admin' ) ) {
	unset( $getHash['user_id'] );
} elseif( $gBitSystem->isFeatureActive( 'fisheye_show_public_on_upload' ) ) {
//	$getHash['show_public'] = TRUE;
}
$galleryTree = $gContent->generateList( $getHash,  array( 'name' => "gallery_id", 'id' => "gallerylist", 'item_attributes' => array( 'class'=>'listingtitle'), 'radio_checkbox' => TRUE, ) );
$gBitSmarty->assign_by_ref( 'galleryTree', $galleryTree );

$gContent->invokeServices( 'content_edit_function' );

$gBitSystem->display( 'bitpackage:fisheye/edit_gallery.tpl', tra('Edit Gallery: ').$gContent->getTitle() , array( 'display_mode' => 'edit' ));

?>
