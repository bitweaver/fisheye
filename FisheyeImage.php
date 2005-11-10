<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/FisheyeImage.php,v 1.2.2.24 2005/11/10 17:18:02 spiderr Exp $
 * @package fisheye
 */

/**
 * required setup
 */
require_once( FISHEYE_PKG_PATH.'FisheyeBase.php' );

define('FISHEYEIMAGE_CONTENT_TYPE_GUID', 'fisheyeimage');

/**
 * @package fisheye
 * @subpackage FisheyeImage
 */
class FisheyeImage extends FisheyeBase {
	var $mImageId;

	function FisheyeImage($pImageId = NULL, $pContentId = NULL) {
		FisheyeBase::FisheyeBase();
		$this->mImageId = $pImageId;
		$this->mContentId = $pContentId;

		$this->registerContentType(FISHEYEIMAGE_CONTENT_TYPE_GUID, array('content_type_guid' => FISHEYEIMAGE_CONTENT_TYPE_GUID,
				'content_description' => 'Image',
				'handler_class' => 'FisheyeImage',
				'handler_package' => 'fisheye',
				'handler_file' => 'FisheyeImage.php',
				'maintainer_url' => 'http://www.bitweaver.org'
			) );
	}

	function load() {
		if( $this->isValid() ) {
			global $gBitSystem, $gBitUser;
			$gateSql = NULL;
			$mid = NULL;
			if ($this->mImageId && is_numeric($this->mImageId)) {
				$mid = " WHERE tfi.`image_id` = ?";
				$bindVars = array($this->mImageId);
			} elseif ($this->mContentId && is_numeric($this->mContentId)) {
				$mid = " WHERE tfi.`content_id` = ?";
				$bindVars = array($this->mContentId);
			}
			if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
				$gateSql = ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer` ';
				$mid = " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs ON ( tc.`content_id`=tcs.`content_id` )  LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON ( tcs.`security_id`=ts.`security_id` )
 						".$mid;
			}
			$sql = "SELECT tfi.*, tc.* $gateSql
						, uue.`login` AS `modifier_user`, uue.`real_name` AS `modifier_real_name`
						, uuc.`login` AS `creator_user`, uuc.`real_name` AS `creator_real_name`
					FROM `".BIT_DB_PREFIX."tiki_fisheye_image` tfi, `".BIT_DB_PREFIX."tiki_content` tc
						LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON (uue.`user_id` = tc.`modifier_user_id`)
						LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON (uuc.`user_id` = tc.`user_id`)
					$mid AND tc.`content_id` = tfi.`content_id`";
			if( $rs = $this->mDb->query($sql, array($bindVars)) ) {
				$this->mInfo = $rs->fields;

				$this->mImageId = $this->mInfo['image_id'];
				$this->mContentId = $this->mInfo['content_id'];

				$this->mInfo['creator'] = (isset( $rs->fields['creator_real_name'] ) ? $rs->fields['creator_real_name'] : $rs->fields['creator_user'] );
				$this->mInfo['editor'] = (isset( $rs->fields['modifier_real_name'] ) ? $rs->fields['modifier_real_name'] : $rs->fields['modifier_user'] );

				if( $gBitSystem->isPackageActive( 'gatekeeper' ) && empty( $this->mInfo['security_id'] ) ) {
					// check to see if this image is in a protected gallery
					// this burns an extra select but avoids an big and gnarly LEFT JOIN sequence that may be hard to optimize on all DB's
					$query = "SELECT ts.* FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim
								INNER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tsm ON(tfgim.`gallery_content_id`=tsm.`content_id` )
								INNER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON(tsm.`security_id`=ts.`security_id` )
							  WHERE tfgim.`item_content_id`=?";
					$grs = $this->mDb->query($query, array( $this->mContentId ) );
					if( $grs && $grs->RecordCount() ) {
						$this->mInfo = array_merge( $this->mInfo, $grs->fields );
					}
				}

				LibertyAttachable::load();

				if (!empty($this->mStorage) && count($this->mStorage) > 0) {
					reset($this->mStorage);
					$this->mInfo['image_file'] = current($this->mStorage);
				} else {
					$this->mInfo['image_file'] = NULL;
				}
			}
		} else {
			// We don't have an image_id or a content_id so there is no way to know what to load
			return NULL;
		}

		return count($this->mInfo);
	}

	function exportHtml( $pData = NULL ) {
//		if( empty( $pData ) ) {
//			$pData = $this->mInfo['data'];
//		}
//		$ret = FisheyeBase::parseData( $pData );
//		$ret .= '<img src="'.$this->mInfo['image_file']['source_url'].'" width="400" height="300" />';
		$ret = NULL;
		// make sure we have a valid image file.
		if( $this->isValid() && ($details = $this->getImageDetails( BIT_ROOT_PATH.$this->mInfo['image_file']['storage_path'] ) ) ) {
			if( $this->mInfo['width'] != $details['width'] || $this->mInfo['height'] != $details['height']  ) {
				// if our data got out of sync with the database, force an update
				$query = "UPDATE `".BIT_DB_PREFIX."tiki_fisheye_image` SET `width`=?, `height`=? WHERE `content_id`=?";
				$this->mDb->query( $query, array( $details['width'], $details['height'], $this->mContentId ) );
				$this->mInfo['width'] = $details['width'];
				$this->mInfo['height'] = $details['height'];
			}
			$ret = array(	'type' => FISHEYEIMAGE_CONTENT_TYPE_GUID,
							'landscape' => $this->isLandscape(),
							'url' => $this->getDisplayUrl(),
							'content_id' => $this->mContentId,
							'title' => $this->getTitle(),
							'has_description' => !empty( $this->mInfo['data'] ),
						);
		}
		return $ret;
	}

	function isLandscape() {
		return( !empty( $this->mInfo['width'] ) && !empty( $this->mInfo['height'] ) && ($this->mInfo['width'] > $this->mInfo['height']) );
	}

	function verifyImageData(&$pStorageHash) {
		$pStorageHash['content_type_guid'] = FISHEYEIMAGE_CONTENT_TYPE_GUID;

		if ( empty($pStorageHash['purge_from_galleries']) ) {
			$pStorageHash['purge_from_galleries'] = FALSE;
		}

		// let's add a default title
		if( empty( $pStorageHash['title'] ) && !empty( $pStorageHash['upload']['name'] ) ) {
			if( strpos( '.', $pStorageHash['upload']['name'] ) ) {
				list( $defaultName, $ext ) = explode( '.', $pStorageHash['upload']['name'] );
			} else {
				$defaultName = $pStorageHash['upload']['name'];
			}
			$pStorageHash['title'] = str_replace( '_', ' ', substr( $defaultName, 0, strrpos( $defaultName, '.' ) ) );
		}

		if( !empty( $pStorageHash['resize'] ) ) {
			$pStorageHash['upload']['max_height'] = $pStorageHash['upload']['max_width'] = $pStorageHash['resize'];
		}

		return (count($this->mErrors) == 0);
	}

	function store(&$pStorageHash) {
		global $gBitSystem;
		if ($this->verifyImageData($pStorageHash)) {
			// Save the current attachment ID for the image attached to this FisheyeImage so we can
			// delete it after saving the new one
			if (!empty($this->mInfo['image_file']) && !empty($this->mInfo['image_file']['attachment_id']) && !empty($pStorageHash['upload'])) {
				$currentImageAttachmentId = $this->mInfo['image_file']['attachment_id'];
				$pStorageHash['attachment_id'] = $currentImageAttachmentId;
			} else {
				$currentImageAttachmentId = NULL;
			}

			// LibertyAttachable will take care of thumbnail generation of the offline thumbnailer is not active
			if( !empty( $pStorageHash['upload'] ) ) {
				$pStorageHash['upload']['thumbnail'] = !$gBitSystem->isFeatureActive( 'feature_offline_thumbnailer' );
			}
			if( LibertyAttachable::store( $pStorageHash ) ) {
				if( $currentImageAttachmentId && $currentImageAttachmentId != $this->mInfo['image_file']['attachment_id'] ) {
					$this->expungeAttachment($currentImageAttachmentId);
				}
				$this->mContentId = $pStorageHash['content_id'];
				$this->mInfo['content_id'] = $this->mContentId;

				if (!empty($pStorageHash['STORAGE']['bitfile']['upload']['source_file'])) {
					$imageDetails = $this->getImageDetails($pStorageHash['STORAGE']['bitfile']['upload']['source_file']);
				} else {
					$imageDetails = NULL;
				}

				if (!$imageDetails) {
					$imageDetails['width'] = (!empty($this->mInfo['width']) ? $this->mInfo['width'] : NULL);
					$imageDetails['height'] = (!empty($this->mInfo['height']) ? $this->mInfo['height'] : NULL);
				}

				if ($this->imageExistsInDatabase()) {
					$sql = "UPDATE `".BIT_DB_PREFIX."tiki_fisheye_image`
							SET `content_id` = ?, `width` = ?, `height` = ?
							WHERE `image_id` = ?";
					$bindVars = array($this->mContentId, $imageDetails['width'], $imageDetails['height'], $this->mImageId);
				} else {
					$this->mImageId = $this->mDb->GenID('tiki_fisheye_image_id_seq');
					$this->mInfo['image_id'] = $this->mImageId;
					$sql = "INSERT INTO `".BIT_DB_PREFIX."tiki_fisheye_image` (`image_id`, `content_id`, `width`, `height`) VALUES (?,?,?,?)";
					$bindVars = array($this->mImageId, $this->mContentId, $imageDetails['width'], $imageDetails['height']);
				}
				$rs = $this->mDb->query($sql, $bindVars);

				// check to see if we need offline thumbnailing
				if( $gBitSystem->isFeatureActive( 'feature_offline_thumbnailer' ) ) {
					$this->generateThumbnails();
				}
			}
		} else {
			$this->mErrors[] = "There were errors while attempting to save this gallery image";
		}

		return (count($this->mErrors) == 0);
	}


	function rotateImage( $pDegrees ) {
		global $gBitSystem;
		if( !empty( $this->mInfo['image_file'] ) || $this->load() ) {
			$fileHash['source_file'] = BIT_ROOT_PATH.$this->mInfo['image_file']['storage_path'];
			$fileHash['dest_base_name'] = preg_replace('/(.+)\..*$/', '$1', basename( $fileHash['source_file'] ) );
			$fileHash['type'] = 'image/'.strtolower( substr( $fileHash['source_file'], (strrpos( $fileHash['source_file'], '.' )+1) ) );
			$fileHash['size'] = filesize( $fileHash['source_file'] );
			$fileHash['dest_path'] = dirname( $this->mInfo['image_file']['storage_path'] ).'/';
			$fileHash['name'] = $this->mInfo['image_file']['filename'];
			$fileHash['degrees'] = $pDegrees;
			$rotateFunc = liberty_get_function( 'rotate' );
			if( $rotateFunc( $fileHash ) ) {
				liberty_clear_thumbnails( $fileHash );
				$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."tiki_fisheye_image` SET `width`=`height`, `height`=`width` WHERE `content_id`=?", array( $this->mContentId ) );
				$this->generateThumbnails();
			} else {
				$this->mErrors['rotate'] = $fileHash['error'];
			}
		}
		return (count($this->mErrors) == 0);
	}


	function resizeOriginal( $pResizeOriginal ) {
		global $gBitSystem;
		if( !empty( $this->mInfo['image_file'] ) || $this->load() ) {
			$fileHash['source_file'] = BIT_ROOT_PATH.$this->mInfo['image_file']['storage_path'];
			$fileHash['dest_base_name'] = preg_replace('/(.+)\..*$/', '$1', basename( $fileHash['source_file'] ) );
			$fileHash['type'] = 'image/'.strtolower( substr( $fileHash['source_file'], (strrpos( $fileHash['source_file'], '.' )+1) ) );
			$fileHash['size'] = filesize( $fileHash['source_file'] );
			$fileHash['dest_path'] = dirname( $this->mInfo['image_file']['storage_path'] ).'/';
			$fileHash['name'] = $this->mInfo['image_file']['filename'];
			$fileHash['max_height'] = $fileHash['max_width'] = $pResizeOriginal;
			$resizeFunc = ($gBitSystem->getPreference( 'image_processor' ) == 'imagick' ) ? 'liberty_imagick_resize_image' : 'liberty_gd_resize_image';
			if( !$resizeFunc( $fileHash ) ) {
				$this->mErrors['upload'] = $fileHash['errors'];
			}
			// Ack this is evil direct bashing of the liberty tables! XOXO spiderr
			// should be a cleaner way eventually
			$details = $this->getImageDetails( $fileHash['source_file'] );
			$query = "UPDATE `".BIT_DB_PREFIX."tiki_files` SET `size`=? WHERE `file_id`=?";
			$this->mDb->query( $query, array( $details['size'], $this->mInfo['image_file']['file_id'] ) );
			$query = "UPDATE `".BIT_DB_PREFIX."tiki_fisheye_image` SET `width`=?, `height`=? WHERE `content_id`=?";
			$this->mDb->query( $query, array( $details['width'], $details['height'], $this->mContentId ) );
		}
		return (count($this->mErrors) == 0);
	}


	function generateThumbnails( $pResizeOriginal=NULL ) {
		global $gBitSystem;
		// LibertyAttachable will take care of thumbnail generation of the offline thumbnailer is not active
		if( $gBitSystem->isFeatureActive( 'feature_offline_thumbnailer' ) ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_thumbnail_queue`
					  WHERE `content_id`=?";
			$this->mDb->query( $query, array( $this->mContentId ) );
			$query = "INSERT INTO `".BIT_DB_PREFIX."tiki_thumbnail_queue`
					  (`content_id`, `queue_date`, `resize_original`) VALUES (?,?,?)";
			$this->mDb->query( $query, array( $this->mContentId, $gBitSystem->getUTCTime(), $pResizeOriginal ) );
		} else {
			$this->renderThumbnails();
		}
	}


	function renderThumbnails() {
		if( !empty( $this->mInfo['image_file'] ) || $this->load() ) {
			$fileHash['source_file'] = BIT_ROOT_PATH.$this->mInfo['image_file']['storage_path'];
			$fileHash['type'] = 'image/'.strtolower( substr( $fileHash['source_file'], (strrpos( $fileHash['source_file'], '.' )+1) ) );
			$fileHash['size'] = filesize( $fileHash['source_file'] );
			$fileHash['dest_path'] = dirname( $this->mInfo['image_file']['storage_path'] ).'/';
			$fileHash['name'] = $this->mInfo['image_file']['filename'];
			// just generate thumbnails
			liberty_generate_thumbnails( $fileHash );
			if( !empty( $fileHash['error'] ) ) {
				$this->mErrors['thumbnail'] = $fileHash['error'];
			}
		}
		return( count($this->mErrors) == 0 );
	}

	// Get resolution, etc
	function getImageDetails($pFilePath = NULL) {
		$info = NULL;
		$pFilePath = ($pFilePath ? $pFilePath : (empty($this->mInfo['image_file']['storage_path']) ? NULL : BIT_ROOT_PATH.$this->mInfo['image_file']['storage_path']));

		$checkFiles = array( $pFilePath, dirname( $pFilePath ).'/original.jpg' );

		foreach( $checkFiles as $cf ) {
			if ($cf && file_exists( $cf ) && filesize( $cf ) ) {
				if( $info = getimagesize( rtrim( $cf ) ) ) {
					$info['width'] = $info[0];
					$info['height'] = $info[1];
					$info['size'] = filesize( $cf );
				}
			}
		}

		return $info;
	}

    /**
    * Returns include file that will
    * @return the fully specified path to file to be included
    */
	function getRenderFile() {
		return FISHEYE_PKG_PATH."display_fisheye_image_inc.php";
	}

    /**
    * Function that returns link to display a piece of content
    * @param pImageId id of gallery to link
    * @param pMixed if a string, it is assumed to be the size, if an array, it is assumed to be a mInfo hash
    * @return the url to display the gallery.
    */
	function getDisplayUrl( $pImageId=NULL, $pMixed=NULL ) {
		if( empty( $pImageId ) ) {
			$pImageId = $this->mImageId;
		}

		$size = ( !empty( $pMixed ) && isset( $this->mInfo['image_file']['thumbnail_url'][$pMixed] ) ) ? $pMixed : NULL ;
		global $gBitSystem;
		if( is_numeric( $pImageId ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
				$ret = FISHEYE_PKG_URL.'image/'.$pImageId;
				if( !empty( $this->mGalleryPath ) ) {
					$ret .= $this->mGalleryPath;
				}
				if( $size ) {
					$ret .= '/'.$pMixed;
				}
			} else {
				$ret = FISHEYE_PKG_URL.'view_image.php?image_id='.$pImageId;
				if( !empty( $this->mGalleryPath ) ) {
					$ret .= '&gallery_path='.$this->mGalleryPath;
				}
				if( $size ) {
					$ret .= '&size='.$pMixed;
				}
			}
		} elseif( !empty( $pMixed['content_id'] ) ) {
			$ret = FISHEYE_PKG_URL.'view_image.php?content_id='.$pMixed['content_id'];
		}
		return $ret;
	}


	/**
	 * Generate a valid display link for the Blog
	 *
	 * @param	object	PostId of the item to use
	 * @param	array	Not used
	 * @return	object	Fully formatted html link for use by Liberty
	 */
	function getDisplayLink( $pImageId=NULL, $pMixed=NULL ) {
		$ret = '';
		if( !empty( $this ) ) {
			$pImageId = $this->mImageId;
			$title = $this->getTitle();
			if( empty( $title ) ) {
				$title = tra( 'Image' ).' '.$pImageId;
			}
			$ret = "<a title=\"".urlencode( $title )."\" href=\"" . FisheyeImage::getDisplayUrl( $pImageId, $pMixed ) . "\">".$title."</a>";
		}
		return( $ret );
	}


	function loadThumbnail( $pSize='small' ) {
		$this->mInfo['image_file']['gallery_thumbnail_url'] = &$this->mInfo['image_file']['thumbnail_url'][$pSize];
	}

	function getThumbnailUrl( $pSize='small' ) {
		if( empty( $this->mInfo['image_file']['gallery_thumbnail_url'] ) ) {
			$this->loadThumbnail( $pSize );
		}
		return $this->mInfo['image_file']['gallery_thumbnail_url'];
	}

	function expunge() {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` WHERE `item_content_id` = ?";
			$rs = $this->mDb->query($query, array( $this->mContentId ));
			$query = "UPDATE `".BIT_DB_PREFIX."tiki_fisheye_gallery` SET `preview_content_id`=NULL WHERE `preview_content_id` = ?";
			$rs = $this->mDb->query($query, array( $this->mContentId ));
			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_fisheye_image` WHERE `content_id` = ?";
			$rs = $this->mDb->query($query, array( $this->mContentId ));
			if( LibertyAttachable::expunge() ) {
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	function isValid() {
		return( !empty( $this->mImageId ) || !empty( $this->mContentId ) );
	}

	function imageExistsInDatabase() {
		$ret = FALSE;
		if( $this->isValid() && $this->mImageId ) {
			$sql = "SELECT COUNT(`image_id`) AS `count`
					FROM `".BIT_DB_PREFIX."tiki_fisheye_image`
					WHERE `image_id` = ?";
			$rs = $this->mDb->query($sql, array($this->mImageId));

			if ($rs->fields['count'] > 0)
					$ret = TRUE;
		}
		return $ret;
	}


	function getList( &$pListHash ) {
		global $gBitUser,$gBitSystem, $commentsLib;

		$this->prepGetList( $pListHash );
		$bindVars = array();
		$distinct = '';
		$select = '';
		$mid = '';
		$join = '';

		if( !empty( $pListHash['user_id'] ) && is_numeric( $pListHash['user_id'] )) {
			$mid .= " AND tc.`user_id` = ? ";
			$bindVars[] = $pListHash['user_id'];
		} elseif( !empty( $pListHash['recent_users'] )) {
			$distinct = " DISTINCT ON ( uu.`user_id` ) ";
			$pListHash['sort_mode'] = 'uu.user_id_desc';
		}



		if( !empty( $pListHash['gallery_id'] ) && is_numeric( $pListHash['gallery_id'] )) {
			$mid .= " AND tfg.`gallery_id` = ? ";
			$bindVars[] = $pListHash['gallery_id'];
		}

		if( !empty( $pListHash['search'] ) ) {
			$mid .= " AND UPPER(tc.`title`) LIKE ? ";
			$bindVars[] = '%'.strtoupper( $pListHash['search'] ).'%';
		}
//  $this->debug();
		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			if( $this->mDb->isAdvancedPostgresEnabled() ) {
				$mid .= " AND (SELECT ts.`security_id` FROM connectby('tiki_fisheye_gallery_image_map', 'gallery_content_id', 'item_content_id', tfi.`content_id`, 0, '/')  AS t(`cb_gallery_content_id` int, `cb_item_content_id` int, level int, branch text), `".BIT_DB_PREFIX."tiki_content_security_map` tcsm,  `".BIT_DB_PREFIX."tiki_security` ts
						  WHERE ts.`security_id`=tcsm.`security_id` AND tcsm.`content_id`=`cb_gallery_content_id` LIMIT 1) IS NULL";
			} else {
				$select .= ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer` ';
				$join .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs ON (tc.`content_id`=tcs.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON (ts.`security_id`=tcs.`security_id` )  LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim ON (tfgim.`item_content_id`=tc.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs2 ON (tfgim.`gallery_content_id`=tcs2.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts2 ON (ts2.`security_id`=tcs2.`security_id` )";
				$mid .= ' AND (tcs2.`security_id` IS NULL OR tc.`user_id`=?) ';
				$bindVars[] = $gBitUser->mUserId;
			}
		}
		if ( !empty( $pListHash['sort_mode'] ) ) {
			//converted in prepGetList()
			$mid .= " ORDER BY ".$this->mDb->convert_sortmode( $pListHash['sort_mode'] )." ";
		}
		$query = "SELECT $distinct tfi.`image_id` AS `hash_key`, tfi.*, tf.*, tc.*, tfg.`gallery_id`, uu.`login`, uu.`real_name` $select
				FROM `".BIT_DB_PREFIX."tiki_fisheye_image` tfi
					INNER JOIN `".BIT_DB_PREFIX."tiki_attachments` ta ON(ta.`content_id`=tfi.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."tiki_files` tf ON(ta.`foreign_id`=tf.`file_id`)
					, `".BIT_DB_PREFIX."users_users` uu, `".BIT_DB_PREFIX."tiki_content` tc $join
					LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim2 ON(tfgim2.`item_content_id`=tc.`content_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_fisheye_gallery` tfg ON(tfg.`content_id`=tfgim2.`gallery_content_id`)
				WHERE tfi.`content_id` = tc.`content_id` AND uu.`user_id` = tc.`user_id` $mid";

		if( $rs = $this->mDb->query( $query, $bindVars, $pListHash['max_records'],$pListHash['offset'] ) ) {
			$ret = $rs->GetAssoc();
			if( empty( $pListHash['no_thumbnails'] ) ) {
				foreach( array_keys( $ret ) as $imageId ) {
					$trailingName = dirname( $ret[$imageId]['storage_path'] )."/avatar.jpg";
					if( file_exists( BIT_ROOT_PATH.$trailingName ) ) {
						$ret[$imageId]['thumbnail_url'] = BIT_ROOT_URL.$trailingName;
					} else {
						$ret[$imageId]['thumbnail_url'] = FISHEYE_PKG_URL.'image/generating_thumbnails.png';
					}
					$ret[$imageId]['display_url'] = $this->getDisplayUrl( $imageId );
					$ret[$imageId]['has_machine_name'] = $this->isMachineName( $ret[$imageId]['title'] );
				}
			}
		}
// $this->debug(0);
		return $ret;
	}
}

?>
