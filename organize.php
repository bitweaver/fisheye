<?php
/**
 * Organize actions for the gallery and photo views: reorder, move, copy,
 * remove, delete, rotate, new previews, reduce, set cover, favorite, rename,
 * new gallery. POST only, answers in JSON.
 *
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );

$gBitSystem->verifyPackage( 'fisheye' );
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeImage.php');

function fisheye_organize_reply( $pOk, $pMessage, $pExtra = array(), $pStatus = 200 ) {
	if( !$pOk && $pStatus == 200 ) {
		$pStatus = 400;
	}
	http_response_code( $pStatus );
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Cache-Control: no-store' );
	print json_encode( array_merge( array( 'ok' => (bool)$pOk, 'message' => $pMessage ), $pExtra ) );
	die;
}

global $gBitUser;

if( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
	fisheye_organize_reply( FALSE, tra( 'This address only accepts posted actions.' ), array(), 405 );
}
// every action changes something, so each needs the session ticket
if( !$gBitUser->isRegistered() || empty( $_POST['tk'] ) || !is_string( $_POST['tk'] ) || !hash_equals( (string)$gBitUser->mTicket, $_POST['tk'] ) ) {
	fisheye_organize_reply( FALSE, tra( 'Your session has expired. Reload the page and try again.' ), array(), 403 );
}

$action = ( !empty( $_POST['action'] ) && is_string( $_POST['action'] ) ) ? $_POST['action'] : '';
$itemIds = array();
if( !empty( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
	foreach( $_POST['items'] as $itemId ) {
		if( BitBase::verifyId( $itemId ) ) {
			$itemIds[(int)$itemId] = (int)$itemId;
		}
	}
}
$itemIds = array_slice( array_values( $itemIds ), 0, 500 );

// The gallery the visitor is working in. Membership actions need permission to update it.
$gallery = NULL;
if( BitBase::verifyIdParameter( $_POST, 'gallery_id' ) ) {
	$gallery = FisheyeGallery::lookup( array( 'gallery_id' => $_POST['gallery_id'] ) );
	if( !is_object( $gallery ) || !$gallery->isValid() ) {
		fisheye_organize_reply( FALSE, tra( 'That gallery no longer exists.' ), array(), 404 );
	}
}

/** Narrow a list of content ids to those that are items of the gallery */
function fisheye_organize_members( $pGallery, $pItemIds ) {
	$ret = array();
	if( $pItemIds ) {
		$bindVars = array_merge( array( $pGallery->mContentId ), $pItemIds );
		$query = "SELECT `item_content_id` FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map`
				WHERE `gallery_content_id` = ? AND `item_content_id` IN ( ".implode( ',', array_fill( 0, count( $pItemIds ), '?' ) )." )";
		if( $rs = $pGallery->mDb->query( $query, $bindVars ) ) {
			while( $row = $rs->fetchRow() ) {
				$ret[] = (int)$row['item_content_id'];
			}
		}
	}
	return $ret;
}

/** Load the images among the ids that the user may change */
function fisheye_organize_images( $pItemIds ) {
	$ret = array();
	foreach( $pItemIds as $contentId ) {
		$image = LibertyBase::getLibertyObject( $contentId );
		if( is_a( $image, 'FisheyeImage' ) && $image->isValid() && $image->hasUpdatePermission() ) {
			$ret[$contentId] = $image;
		}
	}
	return $ret;
}

function fisheye_organize_count( $pCount, $pOne, $pMany ) {
	return $pCount.' '.tra( $pCount == 1 ? $pOne : $pMany );
}

$galleryActions = array( 'reorder', 'remove', 'move', 'copy', 'cover', 'favorite' );
if( in_array( $action, $galleryActions ) ) {
	if( !$gallery ) {
		fisheye_organize_reply( FALSE, tra( 'No gallery was given.' ) );
	}
	if( !$gallery->hasUpdatePermission() ) {
		fisheye_organize_reply( FALSE, tra( 'You do not have permission to change this gallery.' ), array(), 403 );
	}
	$itemIds = fisheye_organize_members( $gallery, $itemIds );
}
if( $action != 'galleries' && $action != 'newgallery' && !$itemIds ) {
	fisheye_organize_reply( FALSE, tra( 'Nothing was selected.' ) );
}

switch( $action ) {
	case 'galleries':
		// destinations for move and copy: the user's own galleries
		$find = ( !empty( $_POST['find'] ) && is_string( $_POST['find'] ) ) ? trim( $_POST['find'] ) : NULL;
		$lister = new FisheyeGallery();
		fisheye_organize_reply( TRUE, '', array( 'galleries' => $lister->getPickerList( $gBitUser->mUserId, $find ) ) );
		break;

	case 'newgallery':
		// a new top-level gallery, made from the gallery picker and chosen at once
		$title = ( isset( $_POST['title'] ) && is_string( $_POST['title'] ) ) ? trim( $_POST['title'] ) : '';
		$newGallery = new FisheyeGallery();
		if( !$newGallery->hasCreatePermission( FALSE ) ) {
			fisheye_organize_reply( FALSE, tra( 'You do not have permission to create a gallery.' ), array(), 403 );
		}
		if( $title === '' ) {
			fisheye_organize_reply( FALSE, tra( 'Give the new gallery a name.' ) );
		}
		$storeHash = array( 'title' => substr( $title, 0, 160 ) );
		if( $newGallery->store( $storeHash ) ) {
			$newGallery->storePreference( 'allow_comments', 'y' );
			fisheye_organize_reply( TRUE, tra( 'Gallery created.' ), array( 'gallery' => array( 'gallery_id' => (int)$newGallery->mGalleryId, 'title' => $storeHash['title'] ) ) );
		}
		fisheye_organize_reply( FALSE, tra( 'The gallery could not be created.' ) );
		break;

	case 'reorder':
		$before = BitBase::verifyIdParameter( $_POST, 'before' ) ? (int)$_POST['before'] : NULL;
		$after = BitBase::verifyIdParameter( $_POST, 'after' ) ? (int)$_POST['after'] : NULL;
		if( $gallery->moveItems( $itemIds, $before, $after ) ) {
			fisheye_organize_reply( TRUE, tra( 'Order saved.' ) );
		}
		fisheye_organize_reply( FALSE, tra( 'The order could not be saved.' ) );
		break;

	case 'cover':
		if( $gallery->storeGalleryThumbnail( $itemIds[0] ) ) {
			fisheye_organize_reply( TRUE, tra( 'Cover set.' ), array( 'cover' => $itemIds[0] ) );
		}
		fisheye_organize_reply( FALSE, tra( 'The cover could not be set.' ) );
		break;

	case 'favorite':
		// the signed-in user's own favorite mark on each item
		$on = !empty( $_POST['on'] );
		foreach( $itemIds as $contentId ) {
			if( $on ) {
				$gBitUser->storeFavorite( $contentId );
			} else {
				$gBitUser->expungeFavorite( $contentId );
			}
		}
		fisheye_organize_reply( TRUE, '', array( 'on' => $on, 'items' => $itemIds ) );
		break;

	case 'remove':
		// a photo that lives only here would be orphaned, so it stays; deleting is a separate action
		$removed = $kept = 0;
		foreach( $itemIds as $contentId ) {
			$parents = $gallery->getParentGalleries( $contentId );
			if( $parents && count( $parents ) > 1 && $gallery->removeItem( $contentId ) ) {
				$removed++;
			} else {
				$kept++;
			}
		}
		$message = $removed ? fisheye_organize_count( $removed, 'item removed from this gallery.', 'items removed from this gallery.' ).' ' : '';
		if( $kept ) {
			$message .= fisheye_organize_count( $kept, 'item is only in this gallery and was kept. Delete it instead.', 'items are only in this gallery and were kept. Delete them instead.' );
		}
		fisheye_organize_reply( $removed > 0, $message, array( 'changed' => $removed ) );
		break;

	case 'move':
	case 'copy':
		$destination = BitBase::verifyIdParameter( $_POST, 'destination' ) ? FisheyeGallery::lookup( array( 'gallery_id' => $_POST['destination'] ) ) : NULL;
		if( !is_object( $destination ) || !$destination->isValid() ) {
			fisheye_organize_reply( FALSE, tra( 'Choose a gallery.' ) );
		}
		if( !$destination->hasUpdatePermission() ) {
			fisheye_organize_reply( FALSE, tra( 'You do not have permission to add to that gallery.' ), array(), 403 );
		}
		if( $destination->mContentId == $gallery->mContentId ) {
			fisheye_organize_reply( FALSE, tra( 'Those items are already in this gallery.' ) );
		}
		$done = $skipped = 0;
		$present = array_flip( fisheye_organize_members( $destination, $itemIds ) );
		foreach( $itemIds as $contentId ) {
			if( isset( $present[$contentId] ) || $destination->addItem( $contentId ) ) {
				// already there counts as done for a move, so the item still leaves this gallery
				if( $action == 'move' ) {
					$gallery->removeItem( $contentId );
					$done++;
				} elseif( isset( $present[$contentId] ) ) {
					$skipped++;
				} else {
					$done++;
				}
			} else {
				$skipped++;
			}
		}
		$message = fisheye_organize_count( $done, ( $action == 'move' ? 'item moved to' : 'item copied to' ), ( $action == 'move' ? 'items moved to' : 'items copied to' ) ).' '.$destination->getTitle().'.';
		if( $skipped ) {
			$message .= ' '.fisheye_organize_count( $skipped, 'item was skipped.', 'items were skipped.' );
		}
		fisheye_organize_reply( $done > 0, $message, array( 'changed' => $done ) );
		break;

	case 'rotate':
		$degrees = ( !empty( $_POST['degrees'] ) && in_array( (int)$_POST['degrees'], array( 90, 180, 270 ) ) ) ? (int)$_POST['degrees'] : 0;
		if( !$degrees ) {
			fisheye_organize_reply( FALSE, tra( 'Unknown rotation.' ) );
		}
		$images = fisheye_organize_images( $itemIds );
		// a few photos are redrawn at once so the page can show them; a large batch goes to the thumbnail queue
		$immediate = count( $images ) <= 12;
		$done = 0;
		foreach( $images as $image ) {
			if( $image->rotateImage( $degrees, $immediate ) ) {
				$done++;
			}
		}
		$message = fisheye_organize_count( $done, 'photo rotated.', 'photos rotated.' );
		if( $done && !$immediate ) {
			$message .= ' '.tra( 'New previews will appear shortly.' );
		}
		fisheye_organize_reply( $done > 0, $message, array( 'changed' => $done, 'items' => array_keys( $images ), 'immediate' => $immediate ) );
		break;

	case 'rethumb':
		// make the previews again; a few at once so the page can show them, a large batch through the queue
		$images = fisheye_organize_images( $itemIds );
		$immediate = count( $images ) <= 12;
		$done = 0;
		foreach( $images as $image ) {
			$image->generateThumbnails( NULL, $immediate );
			if( empty( $image->mErrors ) ) {
				$done++;
			}
		}
		$message = $immediate ? fisheye_organize_count( $done, 'photo has fresh previews.', 'photos have fresh previews.' ) : tra( 'New previews will appear shortly.' );
		fisheye_organize_reply( $done > 0, $done ? $message : tra( 'The previews could not be made.' ), array( 'changed' => $done, 'items' => array_keys( $images ), 'immediate' => $immediate ) );
		break;

	case 'resize':
		// reduce one original to a longest side; only a larger photo changes, and it cannot be undone
		$size = ( !empty( $_POST['size'] ) && in_array( (int)$_POST['size'], array( 1024, 1600, 2048, 2560, 3264 ) ) ) ? (int)$_POST['size'] : 0;
		$images = fisheye_organize_images( array( $itemIds[0] ) );
		if( !$size || !$images ) {
			fisheye_organize_reply( FALSE, tra( 'The photo could not be reduced.' ) );
		}
		$image = current( $images );
		if( max( (int)$image->getField( 'width' ), (int)$image->getField( 'height' ) ) <= $size ) {
			fisheye_organize_reply( FALSE, tra( 'This photo is already that size or smaller.' ) );
		}
		if( $image->resizeOriginal( $size ) ) {
			$image->load();
			fisheye_organize_reply( TRUE, tra( 'Original reduced.' ), array( 'width' => (int)$image->getField( 'width' ), 'height' => (int)$image->getField( 'height' ) ) );
		}
		fisheye_organize_reply( FALSE, tra( 'The photo could not be reduced.' ) );
		break;

	case 'delete':
		$images = fisheye_organize_images( $itemIds );
		$done = 0;
		foreach( $images as $image ) {
			if( $image->expunge() ) {
				$done++;
			}
		}
		$message = fisheye_organize_count( $done, 'photo deleted.', 'photos deleted.' );
		if( $done < count( $itemIds ) ) {
			$message .= ' '.fisheye_organize_count( count( $itemIds ) - $done, 'item could not be deleted.', 'items could not be deleted.' );
		}
		fisheye_organize_reply( $done > 0, $message, array( 'changed' => $done ) );
		break;

	case 'rename':
		$title = ( isset( $_POST['title'] ) && is_string( $_POST['title'] ) ) ? trim( $_POST['title'] ) : '';
		$images = fisheye_organize_images( array( $itemIds[0] ) );
		if( $title === '' || !$images ) {
			fisheye_organize_reply( FALSE, tra( 'The name could not be saved.' ) );
		}
		$image = current( $images );
		// keep the description: a store without it would clear it
		$storeHash = array( 'title' => substr( $title, 0, 160 ), 'edit' => $image->getField( 'data' ) );
		if( $image->store( $storeHash ) ) {
			fisheye_organize_reply( TRUE, tra( 'Name saved.' ), array( 'title' => $storeHash['title'] ) );
		}
		fisheye_organize_reply( FALSE, tra( 'The name could not be saved.' ) );
		break;

	default:
		fisheye_organize_reply( FALSE, tra( 'Unknown action.' ) );
		break;
}
