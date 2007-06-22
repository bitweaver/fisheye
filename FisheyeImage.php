<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/FisheyeImage.php,v 1.60 2007/06/22 11:13:41 lsces Exp $
 * @package fisheye
 */

/**
 * required setup
 */
require_once( FISHEYE_PKG_PATH.'FisheyeBase.php' );

define('FISHEYEIMAGE_CONTENT_TYPE_GUID', 'fisheyeimage');

/**
 * @package fisheye
 */
class FisheyeImage extends FisheyeBase {
	var $mImageId;

	function FisheyeImage($pImageId = NULL, $pContentId = NULL) {
		FisheyeBase::FisheyeBase();
		$this->mImageId = (int)$pImageId;
		$this->mContentId = (int)$pContentId;
		$this->mAdminContentPerm = 'p_fisheye_admin';

		$this->registerContentType(FISHEYEIMAGE_CONTENT_TYPE_GUID, array('content_type_guid' => FISHEYEIMAGE_CONTENT_TYPE_GUID,
				'content_description' => 'Image',
				'handler_class' => 'FisheyeImage',
				'handler_package' => 'fisheye',
				'handler_file' => 'FisheyeImage.php',
				'maintainer_url' => 'http://www.bitweaver.org'
			) );
	}

	function lookup( $pLookupHash ) {
		global $gBitDb;
		$ret = NULL;

		$lookupContentId = NULL;
		if (!empty($pLookupHash['image_id']) && is_numeric($pLookupHash['image_id'])) {
			if( $lookup = $gBitDb->getRow( "SELECT lc.`content_id`, lc.`content_type_guid` FROM `".BIT_DB_PREFIX."fisheye_image` fi INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(lc.`content_id`=fi.`content_id`) WHERE `image_id`=?", array( $pLookupHash['image_id'] ) ) ) {
				$lookupContentId = $lookup['content_id'];
				$lookupContentGuid = $lookup['content_type_guid'];
			}
		} elseif (!empty($pLookupHash['content_id']) && is_numeric($pLookupHash['content_id'])) {
			$lookupContentId = $pLookupHash['content_id'];
			$lookupContentGuid = NULL;
		}
	
		if( BitBase::verifyId( $lookupContentId ) ) {
			$ret = LibertyBase::getLibertyObject( $lookupContentId, $lookupContentGuid );
		}

		return $ret;
	}

	function load() {
		if( $this->isValid() ) {
			global $gBitSystem;
			$gateSql = NULL;
			$selectSql = $joinSql = $whereSql = '';
			$bindVars = array();

			if ( @$this->verifyId( $this->mImageId ) ) {
				$whereSql = " WHERE fi.`image_id` = ?";
				$bindVars[] = $this->mImageId;
			} elseif ( @$this->verifyId( $this->mContentId ) ) {
				$whereSql = " WHERE fi.`content_id` = ?";
				$bindVars[] = $this->mContentId;
			}

			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$sql = "SELECT fi.*, lc.* $gateSql $selectSql
						, uue.`login` AS `modifier_user`, uue.`real_name` AS `modifier_real_name`
						, uuc.`login` AS `creator_user`, uuc.`real_name` AS `creator_real_name`, ufm.`favorite_content_id` AS `is_favorite`
						, lch.`hits`
					FROM `".BIT_DB_PREFIX."fisheye_image` fi
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id` = fi.`content_id`)
						LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON (uue.`user_id` = lc.`modifier_user_id`)
						LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON (uuc.`user_id` = lc.`user_id`)
						LEFT JOIN `".BIT_DB_PREFIX."users_favorites_map` ufm ON (ufm.`favorite_content_id`=lc.`content_id` AND ufm.`user_id`=uuc.`user_id`)
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lch.`content_id` = lc.`content_id` ) $joinSql
					$whereSql";
			if( $this->mInfo = $this->mDb->getRow( $sql, array( $bindVars ) ) ) {
				$this->mImageId = $this->mInfo['image_id'];
				$this->mContentId = $this->mInfo['content_id'];

				$this->mInfo['creator'] = (isset( $this->mInfo['creator_real_name'] ) ? $this->mInfo['creator_real_name'] : $this->mInfo['creator_user'] );
				$this->mInfo['editor'] = (isset( $this->mInfo['modifier_real_name'] ) ? $this->mInfo['modifier_real_name'] : $this->mInfo['modifier_user'] );

				if( $gBitSystem->isPackageActive( 'gatekeeper' ) && !@$this->verifyId( $this->mInfo['security_id'] ) ) {
					// check to see if this image is in a protected gallery
					// this burns an extra select but avoids an big and gnarly LEFT JOIN sequence that may be hard to optimize on all DB's
					$query = "SELECT ls.* FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim
								INNER JOIN `".BIT_DB_PREFIX."gatekeeper_security_map` tsm ON(fgim.`gallery_content_id`=tsm.`content_id` )
								INNER JOIN `".BIT_DB_PREFIX."gatekeeper_security` ls ON(tsm.`security_id`=ls.`security_id` )
							  WHERE fgim.`item_content_id`=?";
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
		if( $this->isValid() && ($details = $this->getImageDetails() ) ) {
			if( $this->mInfo['width'] != $details['width'] || $this->mInfo['height'] != $details['height']  ) {
				// if our data got out of sync with the database, force an update
				$query = "UPDATE `".BIT_DB_PREFIX."fisheye_image` SET `width`=?, `height`=? WHERE `content_id`=?";
				$this->mDb->query( $query, array( $details['width'], $details['height'], $this->mContentId ) );
				$this->mInfo['width'] = $details['width'];
				$this->mInfo['height'] = $details['height'];
			}

			$ret = array(	'type' => $this->getContentType(),
							'landscape' => $this->isLandscape(),
							'url' => $this->getDisplayUrl(),
							'content_id' => $this->mContentId,
							'title' => $this->getTitle(),
							'has_description' => !empty( $this->mInfo['data'] ),
							'is_favorite' => $this->getField('is_favorite'),
						);
		}
		return $ret;
	}

	function isLandscape() {
		return( !empty( $this->mInfo['width'] ) && !empty( $this->mInfo['height'] ) && ($this->mInfo['width'] > $this->mInfo['height']) );
	}

	function verifyImageData(&$pStorageHash) {
		$pStorageHash['content_type_guid'] = $this->getContentType();

		if ( empty($pStorageHash['purge_from_galleries']) ) {
			$pStorageHash['purge_from_galleries'] = FALSE;
		}

		// let's add a default title
		if( empty( $pStorageHash['title'] ) && !empty( $pStorageHash['upload']['name'] ) ) {
			if( preg_match( '/^[A-Z]:\\\/', $pStorageHash['upload']['name'] ) ) {
				// MSIE shit file names if passthrough via gigaupload, etc.
				// basename will not work - see http://us3.php.net/manual/en/function.basename.php
				$tmp = preg_split("[\\\]",$pStorageHash['upload']['name']);
				$defaultName = $tmp[count($tmp) - 1];
			} elseif( strpos( '.', $pStorageHash['upload']['name'] ) ) {
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
		global $gBitSystem, $gLibertySystem;
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
				$pStorageHash['upload']['thumbnail'] = !$gBitSystem->isFeatureActive( 'liberty_offline_thumbnailer' );
			}

			if( LibertyAttachable::store( $pStorageHash ) ) {
				if( $currentImageAttachmentId && $currentImageAttachmentId != $this->mInfo['image_file']['attachment_id'] ) {
					$this->expungeAttachment($currentImageAttachmentId);
				}
				// get storage format back from LibertyAttachment
				$this->mContentId = $pStorageHash['content_id'];
				$this->mInfo['content_id'] = $this->mContentId;

				if ( isset($pStorageHash['storage_guid']) && !empty($pStorageHash['STORAGE'][$pStorageHash['storage_guid']]['upload']['source_file'])) {
					$imageDetails = $this->getImageDetails($pStorageHash['STORAGE'][$pStorageHash['storage_guid']]['upload']['source_file']);
				} else {
					$imageDetails = NULL;
				}

				if (!$imageDetails) {
					$imageDetails['width'] = (!empty($this->mInfo['width']) ? $this->mInfo['width'] : NULL);
					$imageDetails['height'] = (!empty($this->mInfo['height']) ? $this->mInfo['height'] : NULL);
				}

				if ($this->imageExistsInDatabase()) {
					$sql = "UPDATE `".BIT_DB_PREFIX."fisheye_image`
							SET `content_id` = ?, `width` = ?, `height` = ?
							WHERE `image_id` = ?";
					$bindVars = array($this->mContentId, $imageDetails['width'], $imageDetails['height'], $this->mImageId);
				} else {
					$this->mImageId = $this->mDb->GenID('fisheye_image_id_seq');
					$this->mInfo['image_id'] = $this->mImageId;
					$sql = "INSERT INTO `".BIT_DB_PREFIX."fisheye_image` (`image_id`, `content_id`, `width`, `height`) VALUES (?,?,?,?)";
					$bindVars = array($this->mImageId, $this->mContentId, $imageDetails['width'], $imageDetails['height']);
				}
				$rs = $this->mDb->query($sql, $bindVars);

				// check to see if we need offline thumbnailing
				if( $gBitSystem->isFeatureActive( 'liberty_offline_thumbnailer' ) ) {
					$this->generateThumbnails();
				}
			}
		} else {
			$this->mErrors[] = "There were errors while attempting to save this gallery image";
		}

		return (count($this->mErrors) == 0);
	}

	function getExifField( $pExifField ) {
		$ret = NULL;
		if( function_exists( 'exif_read_data' ) ) {
			$pExifField = strtolower( $pExifField );
			$file = $this->getSourceFile();
			// only attempt to get exif data from jpg or tiff files - chokes otherwise
			if( empty( $this->mExif ) && preg_match( "!\.(jpe?g|tif{1,2})$!", $file ) ) {
				$this->mExif = array_change_key_case( exif_read_data( $file ), CASE_LOWER );
			}
			if( !empty( $this->mExif[$pExifField] ) ) {
				$ret = $this->mExif[$pExifField];
			}
		}
		return $ret;
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
			if( $pDegrees == 'auto' ) {
				if( $exifOrientation = $this->getExifField( 'orientation' ) ) {
					switch( $exifOrientation ) {
						 case 1: //) transform="";;
							break;
						 case 2: //) transform="-flip horizontal";;
							break;
						 case 3: //) transform="-rotate 180";;
							$pDegrees = 180;
							break;
						 case 4: //) transform="-flip vertical";;
							break;
						 case 5: //) transform="-transpose";;
							break;
						 case 6: //) transform="-rotate 90";;
							// make sure image has not already been rotated
							if( $this->isLandscape() ) {
								$pDegrees = 90;
							}
							break;
						 case 7: //) transform="-transverse";;
							break;
						 case 8: //) transform="-rotate 270";;
							// make sure image has not already been rotated
							if( $this->isLandscape() ) {
								$pDegrees = 270;
							}
							break;
						 // *) transform="";;
					}	
				}
			}
			if( is_numeric( $pDegrees ) ) {
				$fileHash['degrees'] = $pDegrees;
				$rotateFunc = liberty_get_function( 'rotate' );
				if( $rotateFunc( $fileHash ) ) {
					liberty_clear_thumbnails( $fileHash );
					$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."fisheye_image` SET `width`=`height`, `height`=`width` WHERE `content_id`=?", array( $this->mContentId ) );
					$this->generateThumbnails();
				} else {
					$this->mErrors['rotate'] = $fileHash['error'];
				}
			} elseif( $pDegrees == 'auto' ) {
				$this->mErrors['rotate'] = "Image was not auto-rotated.";
			}
		}
		return (count($this->mErrors) == 0);
	}


	/**
	 * convertColorspace
	 * 
	 * @param string $pColorSpace - target color space, only 'grayscale' is currently supported, and only when using the MagickWand image processor
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function convertColorspace( $pColorSpace ) {
		$ret = FALSE;
		if( !empty( $this->mInfo['image_file'] ) || $this->load() ) {
			$fileHash['source_file'] = BIT_ROOT_PATH.$this->mInfo['image_file']['storage_path'];
			$fileHash['dest_base_name'] = preg_replace('/(.+)\..*$/', '$1', basename( $fileHash['source_file'] ) );
			$fileHash['type'] = 'image/'.strtolower( substr( $fileHash['source_file'], (strrpos( $fileHash['source_file'], '.' )+1) ) );
			$fileHash['size'] = filesize( $fileHash['source_file'] );
			$fileHash['dest_path'] = dirname( $this->mInfo['image_file']['storage_path'] ).'/';
			$fileHash['name'] = $this->mInfo['image_file']['filename'];
			if( $convertFunc = liberty_get_function( 'convert_colorspace' ) ) {
				if( $ret = $convertFunc( $fileHash, $pColorSpace ) ) {
					liberty_clear_thumbnails( $fileHash );
					$sql = "UPDATE `".BIT_DB_PREFIX."liberty_files SET `file_size`=? WHERE `file_id` = ?";
					$this->mDb->query( $sql, array( filesize( $fileHash['dest_file'] ), $this->mInfo['image_file']['file_id'] ) );
					$this->generateThumbnails();
				}
			}
		}
		return $ret;
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
			// make a copy of the fileHash that we can compare output after processing
			$preResize = $fileHash;
			$resizeFunc = liberty_get_function( 'resize' );

			if( !$resizeFunc( $fileHash ) ) {
				$this->mErrors['upload'] = $fileHash['errors'];
			} else {
				// Ack this is evil direct bashing of the liberty tables! XOXO spiderr
				// should be a cleaner way eventually

				// we need to update the souce_file in case it's changed from a non jpg to a jpg
				if( $fileHash['name'] != $preResize['name'] ) {
					$fileHash['source_file'] = dirname( $fileHash['source_file'] ).'/'.$fileHash['name'];
					// make absolutely certain that we have 2 image files and that they are different, then remove the original
					if( $fileHash['source_file'] != $preResize['source_file'] && is_file( $fileHash['source_file'] ) && is_file( $preResize['source_file'] ) ) {
						@unlink( $preResize['source_file'] );
					}
				}
				$details = $this->getImageDetails( $fileHash['source_file'] );
				// store all the values that might have changed due to the resize
				$storeHash = array(
					'file_size'    => $details['size'],
					'mime_type'    => $details['mime'],
					'storage_path' => str_replace( BIT_ROOT_PATH, "", $fileHash['source_file'] ),
				);
				$this->mDb->associateUpdate( BIT_DB_PREFIX."liberty_files", $storeHash, array( 'file_id' => $this->mInfo['image_file']['file_id'] ) );
				//$query = "UPDATE `".BIT_DB_PREFIX."liberty_files` SET `file_size`=? WHERE `file_id`=?";
				//$this->mDb->query( $query, array( $details['size'], $this->mInfo['image_file']['file_id'] ) );
				$query = "UPDATE `".BIT_DB_PREFIX."fisheye_image` SET `width`=?, `height`=? WHERE `content_id`=?";
				$this->mDb->query( $query, array( $details['width'], $details['height'], $this->mContentId ) );
				// if we've come this far, we can try removing the original if it's different to the resized image
				// make absolutely certain that we have 2 image files and that they are different, then remove the original
				if( $fileHash['source_file'] != $preResize['source_file'] && is_file( $fileHash['source_file'] ) && is_file( $preResize['source_file'] ) ) {
					@unlink( $preResize['source_file'] );
				}
			}
		}
		return (count($this->mErrors) == 0);
	}


	function generateThumbnails( $pResizeOriginal=NULL ) {
		global $gBitSystem;
		$ret = FALSE;
		// LibertyAttachable will take care of thumbnail generation of the offline thumbnailer is not active
		if( $gBitSystem->isFeatureActive( 'liberty_offline_thumbnailer' ) ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_process_queue`
					  WHERE `content_id`=?";
			$this->mDb->query( $query, array( $this->mContentId ) );
			$query = "INSERT INTO `".BIT_DB_PREFIX."liberty_process_queue`
					  (`content_id`, `queue_date`, `processor_parameters`) VALUES (?,?,?)";
			$this->mDb->query( $query, array( $this->mContentId, $gBitSystem->getUTCTime(), serialize( array( 'resize_original' => $pResizeOriginal ) ) ) );
		} else {
			$ret = $this->renderThumbnails();
		}
		return $ret;
	}


	function renderThumbnails( $pThumbSizes=NULL ) {
		if( !empty( $this->mInfo['image_file'] ) || $this->load() ) {
			$fileHash['source_file'] = BIT_ROOT_PATH.$this->mInfo['image_file']['storage_path'];
			$fileHash['type'] = 'image/'.strtolower( substr( $fileHash['source_file'], (strrpos( $fileHash['source_file'], '.' )+1) ) );
			$fileHash['size'] = filesize( $fileHash['source_file'] );
			$fileHash['dest_path'] = dirname( $this->mInfo['image_file']['storage_path'] ).'/';
			$fileHash['name'] = $this->mInfo['image_file']['filename'];
			$fileHash['thumbnail_sizes'] = $pThumbSizes;
			// just generate thumbnails
			liberty_generate_thumbnails( $fileHash );
			if( !empty( $fileHash['error'] ) ) {
				$this->mErrors['thumbnail'] = $fileHash['error'];
			}
		}
		return( count($this->mErrors) == 0 );
	}

	function getSourceUrl() {
		$ret = NULL;
		if( !empty( $this->mInfo['image_file']['storage_path'] ) ) {
			 $ret =  BIT_ROOT_URL.dirname( $this->mInfo['image_file']['storage_path'] ).'/'.rawurlencode( basename( $this->mInfo['image_file']['storage_path'] ) );
		}
		return $ret;
	}

	function getSourceFile() {
		$ret = NULL;
		if( !empty( $this->mInfo['image_file']['storage_path'] ) ) {
			 $ret = BIT_ROOT_PATH.$this->mInfo['image_file']['storage_path'];
		}
		return $ret;
	}

	// Get resolution, etc
	function getImageDetails($pFilePath = NULL) {
		$info = NULL;
		$pFilePath = ($pFilePath ? $pFilePath : $this->getSourceFile());

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
    * Returns include file that will setup vars for display
    * @return the fully specified path to file to be included
    */
	function getRenderFile() {
		return FISHEYE_PKG_PATH."display_fisheye_image_inc.php";
	}

    /**
    * Returns template file used for display
    * @return the fully specified path to file to be included
    */
	function getRenderTemplate() {
		return 'bitpackage:fisheye/view_image.tpl';
	}

    /**
    * Function that returns link to display a piece of content
    * @param pImageId id of gallery to link
    * @param pMixed if a string, it is assumed to be the size, if an array, it is assumed to be a mInfo hash
    * @return the url to display the gallery.
    */
	function getDisplayUrl( $pImageId=NULL, $pMixed=NULL ) {
		$ret = '';
		if( !@BitBase::verifyId( $pImageId ) ) {
			$pImageId = $this->mImageId;
		}

		// if pMixed is an array, we assume it has all the pertinent information
		if( is_array( $pMixed )) {
			$info = $pMixed;
		} else {
			$info = &$this->mInfo;
		}

		$size = ( is_string( $pMixed ) && isset( $info['image_file']['thumbnail_url'][$pMixed] ) ) ? $pMixed : NULL ;
		global $gBitSystem;
		if( @BitBase::verifyId( $pImageId ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
				$ret = FISHEYE_PKG_URL.'image/'.$pImageId;
				if( !empty( $this ) && !empty( $this->mGalleryPath ) ) {
					$ret .= $this->mGalleryPath;
				}
				if( $size ) {
					$ret .= '/'.$size;
				}
			} else {
				$ret = FISHEYE_PKG_URL.'view_image.php?image_id='.$pImageId;
				if( !empty( $this ) && !empty( $this->mGalleryPath ) ) {
					$ret .= '&gallery_path='.$this->mGalleryPath;
				}
				if( $size ) {
					$ret .= '&size='.$size;
				}
			}
		} elseif( @BitBase::verifyId( $info['content_id'] ) ) {
			$ret = FISHEYE_PKG_URL.'view_image.php?content_id='.$info['content_id'];
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
	function getDisplayLink( $pTitle=NULL, $pMixed=NULL ) {
		global $gBitSystem;
		if( empty( $pTitle ) && !empty( $this ) ) {
			$pTitle = $this->getTitle();
		}

		if( empty( $pMixed ) && !empty( $this ) ) {
			$pMixed = $this->mInfo;
		}

		$ret = $pTitle;
		if( $gBitSystem->isPackageActive( 'fisheye' ) ) {
			$ret = '<a title="'.$pTitle.'" href="'.FisheyeImage::getDisplayUrl( NULL, $pMixed ).'">'.$pTitle.'</a>';
		}
		return $ret;
	}


	function loadThumbnail( $pSize='small' ) {
		$this->mInfo['image_file']['gallery_thumbnail_url'] = &$this->mInfo['image_file']['thumbnail_url'][$pSize];
	}

	function getThumbnailUrl( $pSize='small', $pInfoHash=NULL ) {
		$ret = NULL;
		if( !empty( $pInfoHash ) ) {
			// do some stuff if we are given a hash of stuff
		} else {
			if( empty( $this->mInfo['image_file']['gallery_thumbnail_url'] ) ) {
				$this->loadThumbnail( $pSize );
			}
			$ret = $this->mInfo['image_file']['gallery_thumbnail_url'];
		}
		return $ret;
	}

	function expunge($pExpungeAttachment = TRUE) {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "DELETE FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` WHERE `item_content_id` = ?";
			$rs = $this->mDb->query($query, array( $this->mContentId ));
			$query = "UPDATE `".BIT_DB_PREFIX."fisheye_gallery` SET `preview_content_id`=NULL WHERE `preview_content_id` = ?";
			$rs = $this->mDb->query($query, array( $this->mContentId ));
			$query = "DELETE FROM `".BIT_DB_PREFIX."fisheye_image` WHERE `content_id` = ?";
			$rs = $this->mDb->query($query, array( $this->mContentId ));
			if( LibertyAttachable::expunge($pExpungeAttachment) ) {
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	function expungingAttachment($pAttachmentId, $pContentIdArray) {
		foreach ($pContentIdArray as $id) {
			$this->mContentId = $id;
			// Vital that we call LibertyAttachable::expunge with false since the attachment is already being deleted.
			$this->expunge(FALSE);
		}
	}

	function isValid() {
		return( @$this->verifyId( $this->mImageId ) || @$this->verifyId( $this->mContentId ) );
	}

	function imageExistsInDatabase() {
		$ret = FALSE;
		if( $this->isValid() && $this->mImageId ) {
			$sql = "SELECT COUNT(`image_id`)
					FROM `".BIT_DB_PREFIX."fisheye_image`
					WHERE `image_id` = ?";
			$rs = $this->mDb->query($sql, array($this->mImageId));

			if ($rs->fields['count'] > 0)
					$ret = TRUE;
		}
		return $ret;
	}


	function getList( &$pListHash ) {
		global $gBitUser,$gBitSystem;

		$this->prepGetList( $pListHash );
		$bindVars = array();
		$distinct = '';
		$select = '';
		$mid = '';
		$join = '';

		if( @$this->verifyId( $pListHash['user_id'] ) ) {
			$mid .= " AND lc.`user_id` = ? ";
			$bindVars[] = $pListHash['user_id'];
		} elseif( !empty( $pListHash['recent_users'] )) {
			$distinct = " DISTINCT ON ( uu.`user_id` ) ";
			$pListHash['sort_mode'] = 'uu.user_id_desc';
		}

		if( @$this->verifyId( $pListHash['gallery_id'] ) ) {
			$mid .= " AND fg.`gallery_id` = ? ";
			$bindVars[] = $pListHash['gallery_id'];
		}

		if( !empty( $pListHash['search'] ) ) {
			$mid .= " AND UPPER(lc.`title`) LIKE ? ";
			$bindVars[] = '%'.strtoupper( $pListHash['search'] ).'%';
		}

		if( !empty( $pListHash['max_age'] ) && is_numeric( $pListHash['max_age'] ) ) {
			$mid .= " AND lc.`created` > ? ";
			$bindVars[] = $pListHash['max_age'];
		}

		$orderby = '';
		if ( !empty( $pListHash['sort_mode'] ) ) {
			//converted in prepGetList()
			$orderby = " ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] )." ";
		}

		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		$query = "SELECT $distinct fi.`image_id` AS `hash_key`, fi.*, lf.*, lc.*, fg.`gallery_id`, uu.`login`, uu.`real_name` $select $selectSql
				FROM `".BIT_DB_PREFIX."fisheye_image` fi
					INNER JOIN `".BIT_DB_PREFIX."liberty_attachments_map` lam ON(lam.`content_id`=fi.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON(la.`attachment_id`=lam.`attachment_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON(la.`foreign_id`=lf.`file_id`)
					, `".BIT_DB_PREFIX."users_users` uu, `".BIT_DB_PREFIX."liberty_content` lc $join
					LEFT OUTER JOIN `".BIT_DB_PREFIX."fisheye_gallery_image_map` tfgim2 ON(tfgim2.`item_content_id`=lc.`content_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."fisheye_gallery` fg ON(fg.`content_id`=tfgim2.`gallery_content_id`) $joinSql
				WHERE fi.`content_id` = lc.`content_id` AND uu.`user_id` = lc.`user_id` $mid $whereSql $orderby";

		if( $rs = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] ) ) {
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

	/**
	 * isCommentable 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function isCommentable() {
		global $gGallery;

		// if we have a loaded gallery, we just use that to work out if we can add comments to this image
		if( is_object( $gGallery ) ) {
			return $gGallery->isCommentable();
		}

		$ret = FALSE;
		if( $parents = $this->getParentGalleries() ) {
			// @TODO: No idea how to work out if you can add a comment to this image
			// for now we'll take the mGalleryPath and use that gallery
			$gal = current( $parents );
			$query = "SELECT `pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id` = ? AND `pref_name` = ?";
			$ret = ( $this->mDb->getOne( $query, array( $gal['content_id'], 'allow_comments' )) == 'y' );
		}
		return $ret;
	}
}

?>
