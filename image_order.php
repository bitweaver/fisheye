<?php
/**
 * Image order: the exact position of every image in a gallery, with titles
 * and the user's favorite marks.
 * One form, saved in one pass.
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

if( !$gContent->isValid() ) {
	$gBitSystem->fatalError( tra( "No gallery exists with the given ID" ), 'error.tpl', '', HttpStatusCodes::HTTP_GONE );
}

// Ensure the user has the permission to change this gallery
$gContent->verifyUpdatePermission();

$securities = array();
if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
	global $gGatekeeper;
	$securities = $gGatekeeper->getSecurityList( $gBitUser->mUserId );
	$gBitSmarty->assign( 'securities', $securities );
}

// Every image of the gallery in its own order. The page is one form, so it is not paged.
define( 'FISHEYE_IMAGE_ORDER_MAX', 5000 );

/** Reduce a typed position to a number: digits with at most one decimal point, or NULL when empty */
function fisheye_clean_position( $pPosition ) {
	$ret = NULL;
	if( is_scalar( $pPosition ) ) {
		$clean = preg_replace( '/[^\d\.]/', '', (string)$pPosition );
		if( ( $radix = strpos( $clean, '.' ) ) !== FALSE ) {
			$clean = substr( $clean, 0, $radix ).'.'.str_replace( '.', '', substr( $clean, $radix + 1 ) );
		}
		if( $clean !== '' && $clean !== '.' ) {
			$ret = (float)$clean;
		}
	}
	return $ret;
}

if( !empty( $_REQUEST['cancel'] ) ) {
	header( 'Location: '.$gContent->getDisplayUrl() );
	die;
} elseif( !empty( $_POST['save_order'] ) ) {
	$gBitUser->verifyTicket();
	$feedback = array();

	// a form larger than the server accepts arrives cut short; saving it would lose the tail
	if( count( $_POST, COUNT_RECURSIVE ) >= (int)ini_get( 'max_input_vars' ) ) {
		$feedback['error'][] = tra( 'This gallery has more images than can be saved in one form. Nothing was changed.' );
	} else {
		$listHash = array( 'max_records' => FISHEYE_IMAGE_ORDER_MAX, 'no_thumbnails' => TRUE );
		$rows = $gContent->getItemList( $listHash );

		$typed = ( !empty( $_POST['imagePosition'] ) && is_array( $_POST['imagePosition'] ) ) ? $_POST['imagePosition'] : array();
		$titles = ( !empty( $_POST['image_title'] ) && is_array( $_POST['image_title'] ) ) ? $_POST['image_title'] : array();
		$batch = ( !empty( $_POST['batch'] ) && is_array( $_POST['batch'] ) ) ? array_flip( $_POST['batch'] ) : array();

		// Re-order the whole gallery by a property; this replaces any typed positions
		$newOrder = array();
		$reorderKeys = array( 'photo_date' => 'event_time', 'upload_date' => 'created', 'caption' => 'title', 'file_name' => 'file_name' );
		$reorderBy = ( !empty( $_POST['reorder_gallery'] ) && is_string( $_POST['reorder_gallery'] ) ) ? $_POST['reorder_gallery'] : '';
		if( isset( $reorderKeys[$reorderBy] ) || $reorderBy == 'random' ) {
			$reorder = array();
			foreach( $rows as $contentId => $row ) {
				$reorder[$contentId] = ( $reorderBy == 'random' ) ? rand( 0, 9999999 ) : (string)$row[$reorderKeys[$reorderBy]];
			}
			natcasesort( $reorder );
			$sortPos = 10;
			foreach( array_keys( $reorder ) as $contentId ) {
				$newOrder[$contentId] = $sortPos;
				$sortPos += 10;
			}
			$feedback['success'][] = tra( 'Gallery re-ordered.' );
		}

		// The user's favorite marks: store only the rows whose mark changed
		$favorites = ( !empty( $_POST['is_favorite'] ) && is_array( $_POST['is_favorite'] ) ) ? array_flip( $_POST['is_favorite'] ) : array();
		$favoriteCount = 0;
		foreach( $rows as $contentId => $row ) {
			if( !empty( $row['is_favorite'] ) && !isset( $favorites[$contentId] ) ) {
				$gBitUser->expungeFavorite( $contentId );
				$favoriteCount++;
			} elseif( empty( $row['is_favorite'] ) && isset( $favorites[$contentId] ) ) {
				$gBitUser->storeFavorite( $contentId );
				$favoriteCount++;
			}
		}

		$positionCount = $titleCount = 0;
		$gContent->StartTrans();
		foreach( $rows as $contentId => $row ) {
			$position = isset( $newOrder[$contentId] ) ? (float)$newOrder[$contentId] : ( isset( $typed[$contentId] ) ? fisheye_clean_position( $typed[$contentId] ) : NULL );
			if( $position !== NULL && ( !is_numeric( $row['item_position'] ) || (float)$row['item_position'] != $position ) ) {
				$gContent->mDb->query( "UPDATE `".BIT_DB_PREFIX."fisheye_gallery_image_map` SET `item_position` = ? WHERE `gallery_content_id` = ? AND `item_content_id` = ?", array( $position, $gContent->mContentId, $contentId ) );
				$positionCount++;
			}
		}
		$gContent->CompleteTrans();

		// Titles and batch commands work on the image object, so only the rows that need it are loaded
		$batchCommand = $batchParam = '';
		if( !empty( $_POST['batch_command'] ) && is_string( $_POST['batch_command'] ) ) {
			@list( $batchCommand, $batchParam ) = array_pad( explode( ':', $_POST['batch_command'], 2 ), 2, '' );
		}
		$batchCount = 0;
		foreach( $rows as $contentId => $row ) {
			$newTitle = ( isset( $titles[$contentId] ) && is_string( $titles[$contentId] ) ) ? trim( $titles[$contentId] ) : NULL;
			$retitle = ( $newTitle !== NULL && $newTitle != trim( (string)$row['title'] ) );
			$inBatch = ( $batchCommand && isset( $batch[$contentId] ) );
			if( !$retitle && !$inBatch ) {
				continue;
			}
			$image = LibertyBase::getLibertyObject( $contentId );
			if( !is_a( $image, 'FisheyeImage' ) || !$image->isValid() ) {
				continue;
			}
			$storeHash = array();
			if( $retitle ) {
				$storeHash['title'] = $newTitle;
				$titleCount++;
			}
			if( $inBatch ) {
				switch( $batchCommand ) {
					case 'thumbnail':
						$image->generateThumbnails();
						$batchCount++;
						break;
					case 'grayscale':
						$image->convertColorspace( 'grayscale' );
						$batchCount++;
						break;
					case 'filenametoimagename':
						if( $gBitUser->hasPermission( 'p_fisheye_admin' ) && !empty( $row['file_name'] ) ) {
							$storeHash['title'] = file_name_to_title( $row['file_name'] );
							$batchCount++;
						}
						break;
					case 'security':
						if( $batchParam === '' || isset( $securities[$batchParam] ) ) {
							$storeHash['security_id'] = $batchParam;
							$batchCount++;
						}
						break;
				}
			}
			if( $storeHash ) {
				// a store without these would clear them
				if( !isset( $storeHash['title'] ) ) {
					$storeHash['title'] = $image->getField( 'title' );
				}
				$storeHash['edit'] = $image->getField( 'data' );
				$image->store( $storeHash );
			}
		}

		if( $positionCount ) {
			$feedback['success'][] = $positionCount.' '.tra( $positionCount == 1 ? 'position saved.' : 'positions saved.' );
		}
		if( $titleCount ) {
			$feedback['success'][] = $titleCount.' '.tra( $titleCount == 1 ? 'title saved.' : 'titles saved.' );
		}
		if( $favoriteCount ) {
			$feedback['success'][] = $favoriteCount.' '.tra( $favoriteCount == 1 ? 'favorite changed.' : 'favorites changed.' );
		}
		if( $batchCount ) {
			$feedback['success'][] = tra( 'Batch command applied to' ).' '.$batchCount.' '.tra( $batchCount == 1 ? 'image.' : 'images.' );
		}
		if( empty( $feedback ) ) {
			$feedback['success'][] = tra( 'Nothing needed saving.' );
		}
	}

	$_SESSION['image_order_feedback'] = $feedback;

	// Redirect so reload does not cause double-batch processing
	bit_redirect( FISHEYE_PKG_URL.'image_order.php?gallery_id='.$gContent->getField( 'gallery_id' ) );
}

$feedback = NULL;
if( !empty( $_SESSION['image_order_feedback'] ) ) {
	$feedback = $_SESSION['image_order_feedback'];
	unset( $_SESSION['image_order_feedback'] );
}

$listHash = array( 'max_records' => FISHEYE_IMAGE_ORDER_MAX, 'thumbnail_size' => 'avatar' );
$feItems = $gContent->getItemList( $listHash );
// galleries that keep the legacy "position number" layout break pages on the whole part of the position
$pageBreaks = ( $gContent->getPreference( 'gallery_pagination' ) == FISHEYE_PAGINATION_POSITION_NUMBER );
$lastWhole = NULL;
foreach( $feItems as $contentId => $item ) {
	// plain dates: the date modifiers are too slow to run on every row of a large gallery
	$feItems[$contentId]['taken_text'] = !empty( $item['event_time'] ) ? gmdate( 'Y-m-d H:i', (int)$item['event_time'] ) : '';
	$feItems[$contentId]['uploaded_text'] = !empty( $item['created'] ) ? gmdate( 'Y-m-d', (int)$item['created'] ) : '';
	$whole = is_numeric( $item['item_position'] ) ? (int)floor( $item['item_position'] ) : 0;
	$feItems[$contentId]['page_break'] = ( $pageBreaks && $whole !== $lastWhole );
	$feItems[$contentId]['position_text'] = is_numeric( $item['item_position'] ) ? rtrim( rtrim( number_format( (float)$item['item_position'], 6, '.', '' ), '0' ), '.' ) : '';
	$lastWhole = $whole;
}

$gBitSmarty->assign( 'feItems', $feItems );
$gBitSmarty->assign( 'feList', $listHash );
$gBitSmarty->assign( 'formfeedback', $feedback );

$gBitSystem->setRequestConfig( 'layout-body', '-fluid' );
$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'js/fisheye.js', FALSE, 600, FALSE, FALSE );
$gBitSystem->display( 'bitpackage:fisheye/image_order.tpl', tra( 'Image Order' ).': '.$gContent->getTitle() , array( 'display_mode' => 'display' ));
