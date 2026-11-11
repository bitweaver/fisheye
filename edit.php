<?php
/**
 * Gallery settings: name, the view it opens in, the galleries it sits in,
 * sharing options, and delete. Also creates a new gallery.
 *
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );

require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeImage.php');

global $gBitSystem;

include_once( FISHEYE_PKG_INCLUDE_PATH.'gallery_lookup_inc.php' );

// Ensure the user has the permission to create new image galleries
if( $gContent->isValid() ){
	$gContent->verifyUpdatePermission();
}else{
	$gContent->verifyCreatePermission();
}

$feLayouts = FisheyeGallery::getAllLayouts();
$feWarnings = array();

if( !empty( $_REQUEST['savegallery'] ) ) {
	$gBitUser->verifyTicket();
	$wasValid = $gContent->isValid();
	$oldLayout = $gContent->getLayout();
	if( $gContent->store( $_REQUEST ) ) {
		$gContent->storePreference( 'is_public', ( !empty( $_REQUEST['is_public'] ) && $gBitUser->hasPermission( 'p_fisheye_create_public_gal' ) ) ? 'y' : NULL );
		$gContent->storePreference( 'allow_comments', !empty( $_REQUEST['allow_comments'] ) ? 'y' : NULL );
		$gContent->storePreference( 'link_original_images', !empty( $_REQUEST['link_original_images'] ) ? 'y' : NULL );
		// An untouched choice is not stored, so a gallery that still holds a legacy layout value keeps it
		$layout = ( !empty( $_REQUEST['gallery_pagination'] ) && is_string( $_REQUEST['gallery_pagination'] ) && isset( $feLayouts[$_REQUEST['gallery_pagination']] ) ) ? $_REQUEST['gallery_pagination'] : NULL;
		if( $layout && ( !$wasValid || $layout != $oldLayout ) ) {
			$gContent->storePreference( 'gallery_pagination', $layout );
		}
		// make sure var is fully stuffed with current data
		$gContent->load();

		// The checked galleries become the parents; unchecked ones are let go.
		$parentIds = array();
		$requested = BitBase::getParameter( $_REQUEST, 'gallery_additions' );
		if( is_array( $requested ) ) {
			$nested = NULL;
			$current = (array)$gContent->getParentGalleries();
			foreach( $requested as $parentId ) {
				if( !BitBase::verifyId( $parentId ) || $parentId == $gContent->mGalleryId ) {
					continue;
				}
				if( empty( $current[$parentId] ) ) {
					// a new parent must not be one of this gallery's own nested galleries
					if( $nested === NULL ) {
						$nested = $gContent->getNestedGalleryIds();
					}
					if( isset( $nested[(int)$parentId] ) ) {
						$feWarnings[] = tra( 'A gallery cannot be placed inside one of its own nested galleries. That choice was skipped.' );
						continue;
					}
				}
				$parentIds[] = (int)$parentId;
			}
		}
		$gContent->addToGalleries( $parentIds );

		if( !empty( $_REQUEST['generate_thumbnails'] ) ) {
			$gContent->generateThumbnails();
		}
		if( empty( $feWarnings ) && empty( $gContent->mErrors ) ) {
			header("location: ".$gContent->getDisplayUrl() );
			die();
		}
	}
} elseif( !empty( $_REQUEST['delete'] ) ) {
	if( !$gContent->isValid() || !$gContent->hasUserPermission( 'p_fisheye_admin' ) ) {
		$gBitSystem->fatalError( tra( "You do not have permission to delete this image gallery" ), 'error.tpl', '', HttpStatusCodes::HTTP_FORBIDDEN );
	}

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
		$gBitUser->verifyTicket();
		$userId = $gContent->getField( 'user_id' );

		$recurseDelete = (!empty( $_REQUEST['recurse'] ) && ($_REQUEST['recurse'] == 'all') );

		if( $gContent->expunge( $recurseDelete ) ) {
			header( "Location: ".FISHEYE_PKG_URL.'?user_id='.$userId );
			die();
		}
	}

} elseif( !empty($_REQUEST['cancelgallery'] ) ) {
	header( 'Location: '.( $gContent->isValid() ? $gContent->getDisplayUrl() : FISHEYE_PKG_URL.'?user_id='.$gBitUser->mUserId ) );
	die();
}

// Initalize the errors list which contains any errors which occured during storage
$errors = (!empty($gContent->mErrors) ? $gContent->mErrors : array());
$gBitSmarty->assignByRef('errors', $errors);
$gBitSmarty->assign( 'feWarnings', $feWarnings );

// The four views, with the line shown under each on the form
$feLayoutNotes = array(
	FISHEYE_LAYOUT_ORGANIZE  => tra( 'Whole photos with names. Best for sorting and picking.' ),
	FISHEYE_LAYOUT_JUSTIFIED => tra( 'Full-width rows on a dark stage.' ),
	FISHEYE_LAYOUT_MASONRY   => tra( 'For mixed portrait and landscape.' ),
	FISHEYE_LAYOUT_DETAILS   => tra( 'Sortable rows with dates and sizes.' ),
);
$gBitSmarty->assign( 'feLayouts', $feLayouts );
$gBitSmarty->assign( 'feLayoutNotes', $feLayoutNotes );
$feLayout = ( !empty( $_REQUEST['gallery_pagination'] ) && is_string( $_REQUEST['gallery_pagination'] ) && isset( $feLayouts[$_REQUEST['gallery_pagination']] ) ) ? $_REQUEST['gallery_pagination'] : $gContent->getLayout();
$gBitSmarty->assign( 'feLayout', $feLayout );

// The galleries this one sits in. Others are added through the gallery picker, so no tree of every gallery is built here.
$feParents = $gContent->isValid() ? (array)$gContent->getParentGalleries() : array();
$gBitSmarty->assign( 'feParents', $feParents );

$feCover = NULL;
if( $gContent->isValid() && $gContent->getField( 'num_images' ) ) {
	$feCover = $gContent->getThumbnailUri( 'small' );
}
$gBitSmarty->assign( 'feCover', $feCover );
$gBitSmarty->assign( 'feCanDelete', $gContent->isValid() && $gContent->hasUserPermission( 'p_fisheye_admin' ) );

$gContent->invokeServices( 'content_edit_function' );

$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'js/fisheye.js', FALSE, 600, FALSE, FALSE );
$gBitSystem->display( 'bitpackage:fisheye/edit_gallery.tpl', ( $gContent->isValid() ? tra( 'Gallery settings' ).': '.$gContent->getTitle() : tra( 'New gallery' ) ), array( 'display_mode' => 'edit' ));
