<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/image_order.php,v 1.23 2007/07/10 18:59:40 squareing Exp $
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

if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
	global $gGatekeeper;
	$gBitSmarty->assign( 'securities', $gGatekeeper->getSecurityList( $gBitUser->mUserId ) );
}

// Ensure the user has the permission to create new image galleries
if( !$gContent->hasUserPermission( 'p_fisheye_edit' ) ) {
	// This user does not own this gallery and they have not been granted the permission to edit this gallery
	$gBitSystem->fatalError( tra( "You cannot edit this image gallery" ) );
}

if (!empty($_REQUEST['cancel'])) {
	header( 'Location: '.$gContent->getDisplayUrl() );
	die;
} elseif (!empty($_REQUEST['updateImageOrder'])) {
	if( !empty( $_REQUEST['batch'] ) ) {
		// flip so we can do instant hash lookup
		$batchCon = array_flip( $_REQUEST['batch'] );
		// increment the first element from 0 to 1 (element 0 index before flip) so any conditional tests will pass, particularly in the .tpl
		$batchCon[key($batchCon)]++;
		$gBitSmarty->assign_by_ref( 'batchEdit', $batchCon );
	}

	if( !empty( $_REQUEST['is_favorite'] ) ) {
		// flip so we can do instant hash lookup
		$favoriteCon = array_flip( $_REQUEST['is_favorite'] );
		// increment the first element from 0 to 1 (element 0 index before flip) so any conditional tests will pass, particularly in the .tpl
		$favoriteCon[key($favoriteCon)]++;
	}

	$gContent->loadImages();

	$feedback = NULL;

	if( !empty( $_REQUEST['reorder_gallery'] ) ) {
		switch( $_REQUEST['reorder_gallery'] ){
			case 'upload_date':
				foreach( array_keys( $gContent->mItems ) as $imageId ) {
					$reorder[$gContent->mItems[$imageId]->mContentId] = $gContent->mItems[$imageId]->mInfo['created'];
				}
				break;
			case 'caption':
				foreach( array_keys( $gContent->mItems ) as $imageId ) {
					$reorder[$gContent->mItems[$imageId]->mContentId] = $gContent->mItems[$imageId]->mInfo['title'];
				}
				break;
			case 'file_name':
				foreach( array_keys( $gContent->mItems ) as $imageId ) {
					$reorder[$gContent->mItems[$imageId]->mContentId] = $gContent->mItems[$imageId]->mInfo['image_file']['filename'];
				}
				break;
			case 'random':
				foreach( array_keys( $gContent->mItems ) as $imageId ) {
					$reorder[$gContent->mItems[$imageId]->mContentId] = rand( 0, 9999999 );
				}
				break;
		}
		asort( $reorder );
		$sortPos = 100;
		foreach( $reorder as $conId => $sortVal ) {
			$newOrder[$conId] = $sortPos;
			$sortPos += 10;
		}
	}

	if( !empty( $gContent->mItems ) ) {
		foreach( array_keys( $gContent->mItems ) as $itemConId ) {
			if( $gContent->mItems[$itemConId]->getField( 'is_favorite' ) && empty( $favoriteCon[$itemConId] ) ) {
				$gBitUser->expungeFavorite( $itemConId );
			} elseif( !$gContent->mItems[$itemConId]->getField('is_favorite') && !empty( $favoriteCon[$itemConId] ) ) {
				$gBitUser->storeFavorite( $itemConId );
			}
		}
	}

	foreach ($_REQUEST['imagePosition'] as $contentId=>$newPos) {
		if( $galleryItem = $gLibertySystem->getLibertyObject( $contentId ) ) {
			$galleryItem->load();
			if( isset( $batchCon[$contentId] ) ) {
				if( !empty( $_REQUEST['batch_command'] ) ) {
					@list( $batchCommand, $batchParam ) = @split( ':', $_REQUEST['batch_command'] );
					switch( $batchCommand ) {
						case 'delete':
							$galleryItem->expunge();
							$galleryItem = NULL;
							break;
						case 'remove':
							$parents = $galleryItem->getParentGalleries();
							if( $galleryItem->isContentType( FISHEYEGALLERY_CONTENT_TYPE_GUID ) || count( $parents ) > 1 ) {
								$gContent->removeItem( $contentId );
							} else {
								$galleryItem->expunge();
							}
							$galleryItem = NULL;
							break;
						case 'rotate':
							$galleryItem->rotateImage( $batchParam );
							$feedback['success'] = tra( "Images rotated" );
							break;
						case 'thumbnail':
							$galleryItem->generateThumbnails();
							$feedback['success'] = tra( "Thumbnail regeneration queued" );
							break;
						case 'grayscale':
							$galleryItem->convertColorspace( 'grayscale' );
							break;
						case 'security':
							$storageHash['security_id'] = $batchParam;
							$feedback['success'] = tra( "Items security assigned" );
							break;
						case 'gallerymove':
							if( empty( $destGallery ) ) {
								$destGallery = new FisheyeGallery( NULL, $batchParam );
								$destGallery->load();
							}
							if( $batchParam != $contentId ) {
								$gContent->removeItem( $contentId );
							}
						case 'gallerycopy':
							if( empty( $destGallery ) ) {
								$destGallery = new FisheyeGallery( NULL, $batchParam );
								$destGallery->load();
							}
							if( $destGallery->addItem( $contentId ) ) {
								$feedback['success'][] = $galleryItem->getTitle().' '.tra( "added to" ).' '.$destGallery->getTitle();
							} else {
								$feedback['error'][] = $galleryItem->getTitle().' '.tra( "could not be added to" ).' '.$destGallery->getTitle();
							}
							break;
					}
				}
			}
			if( is_object( $galleryItem ) ) {
				if( !empty( $_REQUEST['batch_security_id'] ) ) {
				}
				// if we are reordered, that takes precident
				$newPos = preg_replace( '/[^\d\.]/', '', (!empty( $newOrder[$contentId] ) ? $newOrder[$contentId] : $newPos) );
				if ($galleryItem->mInfo['title'] != $_REQUEST['image_title'][$contentId]) {
					$storageHash = array('title' => $_REQUEST['image_title'][$contentId]);
					// make sure we don't delete the 'data' field on en masse title updating
					$storageHash['edit'] = $galleryItem->getField( 'data' );
				}
				if( !empty( $storageHash ) ) {
					$galleryItem->store($storageHash);
				}
				$galleryItem->updatePosition($gContent->mContentId, $newPos);
			}
		}
		unset( $storageHash );
	}

	if ($gContent->storeGalleryThumbnail($_REQUEST['gallery_preview_content_id'])) {
		$gContent->mInfo['preview_content_id'] = $_REQUEST['gallery_preview_content_id'];
	}

	$_SESSION['image_order_feedback'] = $feedback;

	// Redirect so reload does not cause double-batch processing
	bit_redirect( FISHEYE_PKG_URL.'image_order.php?gallery_id='.$gContent->getField( 'gallery_id' ) );
}

if( !empty( $_SESSION['image_order_feedback'] ) ) {
	$feedback = $_SESSION['image_order_feedback'];
	unset( $_SESSION['image_order_feedback'] );
}

// Get a list of usable galleries
$listHash = array(
	'user_id'       => $gBitUser->mUserId,
	'max_records'   => -1,
	'no_thumbnails' => TRUE,
	'sort_mode'     => 'title_asc',
	'show_empty'    => TRUE
);
// modify listHash according to global preferences
if( $gBitSystem->isFeatureActive( 'fisheye_show_all_to_admins' ) && $gBitUser->hasPermission( 'p_fisheye_admin' ) ) {
	unset( $listHash['user_id'] );
} elseif( $gBitSystem->isFeatureActive( 'fisheye_show_public_on_upload' ) ) {
	$listHash['show_public'] = TRUE;
}
$galleryList = $gContent->getList( $listHash );
$gBitSmarty->assign_by_ref( 'galleryList', $galleryList['data'] );
$gContent->loadImages();

$gBitSmarty->assign_by_ref('formfeedback', $feedback);

$gBitThemes->loadAjax( 'prototype' );
$gBitSystem->display( 'bitpackage:fisheye/image_order.tpl', tra( 'Edit Gallery Images' ).': '.$gContent->getTitle() );
?>
