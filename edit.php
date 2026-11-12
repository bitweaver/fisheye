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
		// the package's own confirmation page, with what the gallery holds, so the choice is an informed one
		$mapTable = "`".BIT_DB_PREFIX."fisheye_gallery_image_map`";
		$ownerId = $gContent->getField( 'user_id' );
		// others: items another user owns, which are never deleted with the gallery. shared: the owner's photos that are also in another gallery.
		$feDelete = $gContent->mDb->getRow( "SELECT COUNT(*) AS `items`, COUNT( fg.`content_id` ) AS `galleries`,
				SUM( CASE WHEN lc.`user_id` != ? THEN 1 ELSE 0 END ) AS `others`,
				SUM( CASE WHEN lc.`user_id` = ? AND fg.`content_id` IS NULL AND EXISTS ( SELECT 1 FROM $mapTable other WHERE other.`item_content_id`=fgim.`item_content_id` AND other.`gallery_content_id`!=fgim.`gallery_content_id` ) THEN 1 ELSE 0 END ) AS `shared`
			FROM $mapTable fgim
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id`=fgim.`item_content_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."fisheye_gallery` fg ON( fg.`content_id`=fgim.`item_content_id` )
			WHERE fgim.`gallery_content_id`=?", array( $ownerId, $ownerId, $gContent->mContentId ) );
		$feDelete = array_map( 'intval', (array)$feDelete ) + array( 'items' => 0, 'galleries' => 0, 'others' => 0, 'shared' => 0 );
		$feDelete['photos'] = $feDelete['items'] - $feDelete['galleries'];
		$gBitSmarty->assign( 'feDelete', $feDelete );
		$gBitSmarty->assign( 'feCover', $feDelete['items'] ? $gContent->getThumbnailUri( 'medium' ) : NULL );
		$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
		$gBitSystem->display( 'bitpackage:fisheye/delete_gallery.tpl', tra( 'Delete' ).': '.$gContent->getTitle(), array( 'display_mode' => 'edit' ) );
		die;
	} else {
		$gBitUser->verifyTicket();
		$userId = $gContent->getField( 'user_id' );

		$recurseDelete = (!empty( $_REQUEST['recurse'] ) && ($_REQUEST['recurse'] == 'all') );

		if( $gContent->expunge( $recurseDelete ) ) {
			header( "Location: ".$gContent->getLibraryUrl( $userId ) );
			die();
		}
	}

} elseif( !empty($_REQUEST['cancelgallery'] ) ) {
	header( 'Location: '.( $gContent->isValid() ? $gContent->getDisplayUrl() : FISHEYE_PKG_URL ) );
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
