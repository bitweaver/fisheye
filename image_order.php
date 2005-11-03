<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/image_order.php,v 1.1.1.1.2.15 2005/11/03 20:04:06 squareing Exp $
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
if( !$gContent->hasUserPermission( 'bit_p_edit_fisheye' ) ) {
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
		// increment the first element from 0 to 1 so any conditional tests will pass, particularly in the .tpl
		$batchCon[key($batchCon)]++;
		$gBitSmarty->assign_by_ref( 'batchEdit', $batchCon );
	}

	if( !empty( $_REQUEST['reorder_gallery'] ) && $gContent->loadImages() ) {
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

	foreach ($_REQUEST['imagePosition'] as $contentId=>$newPos) {
		if( $galleryItem = $gLibertySystem->getLibertyObject( $contentId ) ) {
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
				$newPos = preg_replace( '/[\D]/', '', (!empty( $newOrder[$contentId] ) ? $newOrder[$contentId] : $newPos) );
				if ($galleryItem->mInfo['title'] != $_REQUEST['imageTitle'][$contentId]) {
					$storageHash = array('title' => $_REQUEST['imageTitle'][$contentId]);
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

}

// Get a list of all existing galleries
$listHash = array( 'user_id' => $gBitUser->mUserId, 'max_records' => -1, 'no_thumbnails' => TRUE, 'sort_mode' => 'title_asc', 'show_empty' => TRUE );
$galleryList = $gContent->getList( $listHash );
$gBitSmarty->assign_by_ref( 'galleryList', $galleryList['data'] );
$gContent->loadImages();
$gBitSmarty->assign_by_ref('galleryImages', $gContent->mItems);

$gBitSmarty->assign_by_ref('formfeedback', $feedback);

$gBitSystem->display( 'bitpackage:fisheye/image_order.tpl', 'Edit Gallery Images: '.$gContent->getTitle() );

?>
