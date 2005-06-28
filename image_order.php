<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/image_order.php,v 1.2 2005/06/28 07:45:42 spiderr Exp $
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
	$smarty->assign( 'securities', $gGatekeeper->getSecurityList( $gBitUser->mUserId ) );
}

// Ensure the user has the permission to create new image galleries
if( !$gContent->hasUserPermission( 'bit_p_edit_fisheye' ) ) {
	// This user does not own this gallery and they have not been granted the permission to edit this gallery
	$gBitSystem->fatalError( tra( "You cannot edit this image gallery" ) );
}

if (!empty($_REQUEST['updateImageOrder'])) {
	if( !empty( $_REQUEST['batch'] ) ) {
		// flip so we can do instant has lookup
		$batchCon = array_flip( $_REQUEST['batch'] );
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
		}
		asort( $reorder );
		$sortPos = 100;
		foreach( $reorder as $conId => $sortVal ) {
			$newOrder[$conId] = $sortPos;
			$sortPos += 10;
		}
	}

	foreach ($_REQUEST['imagePosition'] as $contentId=>$newPos) {
		$galleryItem = $gLibertySystem->getLibertyObject( $contentId );
		if( $galleryItem->load() ) {
			if( isset( $batchCon[$contentId] ) ) {
				if( !empty( $_REQUEST['batch_command'] ) ) {
					list( $batchCommand, $batchParam ) = split( ':', $_REQUEST['batch_command'] );
					switch( $batchCommand ) {
						case 'delete':
							$galleryItem->expunge();
							$galleryItem = NULL;
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
				$newPos = (!empty( $newOrder[$contentId] ) ? $newOrder[$contentId] : $newPos);
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
$listHash = array( 'user_id'=>$gBitUser->mUserId );
$galleryList = $gContent->getList( $listHash );
$smarty->assign_by_ref('galleryList', $galleryList);
$gContent->loadImages();
$smarty->assign_by_ref('galleryImages', $gContent->mItems);

$smarty->assign_by_ref('formfeedback', $feedback);

$gBitSystem->display( 'bitpackage:fisheye/image_order.tpl', 'Edit Gallery Images: '.$gContent->getTitle() );

?>
