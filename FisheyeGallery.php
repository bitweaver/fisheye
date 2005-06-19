<?php
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php' );		// A gallery is composed of FisheyeImages

define('FISHEYEGALLERY_CONTENT_TYPE_GUID', 'fisheyegallery' );

// FisheyeBase extends LibertyAttachable, which this class doesn't need, but we need a common base class

class FisheyeGallery extends FisheyeBase {
	var $mGalleryId;		// tiki_fisheye_gallery.gallery_id
	var $mItems;			// Array of FisheyeImage class instances which belong to this gallery

	function FisheyeGallery($pGalleryId = NULL, $pContentId = NULL) {
		FisheyeBase::FisheyeBase();		// Call base constructor
		$this->mGalleryId = $pGalleryId;		// Set member variables according to the parameters we were passed
		$this->mContentId = $pContentId;		// tiki_content.content_id which this gallery references
		$this->mItems = NULL;					// Assume no images (if $pAutoLoad is TRUE we will populate this array later)

		// This registers the content type for FishEye galleries
		// FYI: Any class which uses a table which inherits from tiki_content should create their own content type(s)
		$this->registerContentType(FISHEYEGALLERY_CONTENT_TYPE_GUID, array('content_type_guid' => FISHEYEGALLERY_CONTENT_TYPE_GUID,
				'content_description' => 'Image Gallery',
				'handler_class' => 'FisheyeGallery',
				'handler_package' => 'fisheye',
				'handler_file' => 'FisheyeGallery.php',
				'maintainer_url' => 'http://www.bitweaver.org'
			) );
	}

	function isValid() {
		return( !empty( $this->mGalleryId ) || !empty( $this->mContentId ) );
	}

	function load( $pCurrentImageId=NULL ) {
		global $gBitSystem;
		if(!empty($this->mGalleryId)) {
			$mid = " WHERE fg.`gallery_id` = ?";
			$bindVars = array($this->mGalleryId);
		} elseif (!empty($this->mContentId)) {
			$mid = " WHERE fg.`content_id` = ?";
			$bindVars = array($this->mContentId);
		} else {
			$mid = $bindVars = NULL;
		}

		if ($mid) {	// If we have some way to know what tiki_fisheye_gallery row to load...
			$gateSql = NULL;
			if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
				$gateSql = ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer`  ';
				$mid = " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs ON ( tc.`content_id`=tcs.`content_id` )  LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON ( tcs.`security_id`=ts.`security_id` ) ".$mid;
			}

			$query = "SELECT fg.*, tc.* $gateSql
						, uue.`login` AS modifier_user, uue.`real_name` AS `modifier_real_name`
						, uuc.`login` AS creator_user, uuc.`real_name` AS `creator_real_name`
					FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery` fg, `".BIT_DB_PREFIX."tiki_content` tc
						LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON (uue.`user_id` = tc.`modifier_user_id`)
						LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON (uuc.`user_id` = tc.`user_id`)
					$mid AND fg.`content_id` = tc.`content_id`";

			if( $rs = $this->query($query, $bindVars) ) {
				$this->mInfo = $rs->fields;
				$this->mContentId = $rs->fields['content_id'];
				LibertyContent::load();
				if (!empty($this->mInfo['gallery_id'])) {

					$this->mGalleryId = $this->mInfo['gallery_id'];
					$this->mContentId = $this->mInfo['content_id'];

					$this->mInfo['creator'] = (isset( $rs->fields['creator_real_name'] ) ? $rs->fields['creator_real_name'] : $rs->fields['creator_user'] );
					$this->mInfo['editor'] = (isset( $rs->fields['modifier_real_name'] ) ? $rs->fields['modifier_real_name'] : $rs->fields['modifier_user'] );

					// Set some basic defaults for how to display a gallery if they're not already set
					if (empty($this->mInfo['thumbnail_size'])) {
						$this->mInfo['thumbnail_size'] = $this->getPreference('fisheye_gallery_default_thumbnail_size',FISHEYE_DEFAULT_THUMBNAIL_SIZE);
					}
					if (empty($this->mInfo['rows_per_page'])) {
						$this->mInfo['rows_per_page'] = $this->getPreference('fisheye_gallery_default_rows_per_page', FISHEYE_DEFAULT_ROWS_PER_PAGE);
					}
					if (empty($this->mInfo['cols_per_page'])) {
						$this->mInfo['cols_per_page'] = $this->getPreference('fisheye_gallery_default_cols_per_page', FISHEYE_DEFAULT_COLS_PER_PAGE);
					}
					if (empty($this->mInfo['access_answer'])) {
						$this->mInfo['access_answer'] = '';
					}

					$this->mInfo['images_per_page'] = ($this->mInfo['cols_per_page'] * $this->mInfo['rows_per_page']);

					$this->mInfo['num_images'] = $this->getImageCount();
					$this->mInfo['num_pages'] = (int)($this->mInfo['num_images'] / $this->mInfo['images_per_page'] + ($this->mInfo['num_images'] % $this->mInfo['images_per_page'] == 0 ? 0 : 1));

				} else {
					unset( $this->mContentId );
					unset( $this->mGalleryId );
				}
				if( !empty( $pCurrentImageId ) ) {
					// this code sucks but works - XOXO spiderr
					$query = "SELECT tfgim.*,tfi.`image_id`
							FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim
								INNER JOIN `".BIT_DB_PREFIX."tiki_fisheye_image` tfi ON ( tfi.`content_id`=tfgim.`item_content_id` )
							WHERE tfgim.`gallery_content_id` = ?
							ORDER BY tfgim.`position`, tfi.`content_id` ";
					if( $rs = $this->query($query, array( $this->mContentId ) ) ) {
						$rows = $rs->getRows();
						for( $i = 0; $i < count( $rows ); $i++ ) {
							if( $rows[$i]['image_id'] == $pCurrentImageId ) {
								if( $i > 0 ) {
									$this->mInfo['previous_image_id'] = $rows[$i-1]['image_id'];
								}
								if( $i + 1  < count( $rows ) ) {
									$this->mInfo['next_image_id'] = $rows[$i+1]['image_id'];
								}
							}
						}
					}
				}
			}
		}

		return count($this->mInfo);
	}

	function loadImages($pOffset = NULL, $pMaxRows = NULL) {
		global $gLibertySystem, $gBitSystem, $gBitUser;
		if (!$this->mGalleryId || !$this->mContentId) {
			return NULL;
		}

		$bindVars = array($this->mContentId);
		$mid = '';
		$where = '';
		$select = '';
		$join = '';
		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			$select .= ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer` ';
			$join .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs ON (tc.`content_id`=tcs.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON (ts.`security_id`=tcs.`security_id` )";
//			$where = ' AND (tcs.`security_id` IS NULL OR tc.`user_id`=?) ';
//			$bindVars[] = $gBitUser->mUserId;
		}
		$this->mItems = NULL;

		$query = "SELECT tfgim.*, tc.`content_type_guid`, tc.`user_id` $select
				FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON ( tc.`content_id`=tfgim.`item_content_id` ) $join
				WHERE tfgim.`gallery_content_id` = ? $where
				ORDER BY tfgim.`position`, tfgim.`item_content_id` $mid";
		$rs = $this->query($query, $bindVars, $pMaxRows, $pOffset);

		$rows = $rs->getRows();
		foreach ($rows as $row) {
			$pass = TRUE;
			if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
				$pass = empty( $row['security_id'] ) || ( $row['user_id'] == $gBitUser->mUserId ) || !empty( $_SESSION['gatekeeper_security'][$row['security_id']] );
			}
			if( $pass && $item = $gLibertySystem->getLibertyObject( $row['item_content_id'], $row['content_type_guid'] ) ) {
				$item->loadThumbnail( $this->mInfo['thumbnail_size'] );
				$item->setGalleryPath( $this->mGalleryPath.'/'.$this->mGalleryId );
				$item->mInfo['position'] = $row['position'];
				$this->mItems[] = $item;
			}
		}
		return count( $this->mItems );
	}

	function exportHtml( $pData = NULL ) {
		$ret = NULL;
		$ret[] = array(	'type' => FISHEYEGALLERY_CONTENT_TYPE_GUID,
						'landscape' => FALSE,
						'url' => $this->getDisplayUrl(),
						'content_id' => $this->mContentId,
					);
		if( $this->loadImages() ) {
			foreach( array_keys( $this->mItems ) as $key ) {
				$ret[] = $this->mItems[$key]->exportHtml();
			}
		}
		return $ret;
	}

	function getImageCount() {
		$ret = 0;

		if ($this->mGalleryId) {
			$query = "SELECT COUNT(*) AS `count`
					FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map`
					WHERE `gallery_content_id` = ?";
			$rs = $this->query($query, array($this->mContentId));
			$ret = $rs->fields['count'];
		}
		return $ret;
	}

	function verifyGalleryData(&$pStorageHash) {
		global $gBitSystem;

		if (empty($pStorageHash['rows_per_page'])) {
			$pStorageHash['rows_per_page'] = $gBitSystem->getPreference('fisheye_gallery_default_rows_per_page', (!empty($this->mInfo['rows_per_page']) ? $this->mInfo['rows_per_page'] : FISHEYE_DEFAULT_ROWS_PER_PAGE));
		}

		if (empty($pStorageHash['cols_per_page'])) {
			$pStorageHash['cols_per_page'] = $gBitSystem->getPreference('fisheye_gallery_default_cols_per_page', (!empty($this->mInfo['cols_per_page']) ? $this->mInfo['cols_per_page'] : FISHEYE_DEFAULT_COLS_PER_PAGE));
		}

		if (empty($pStorageHash['thumbnail_size'])) {
			$pStorageHash['thumbnail_size'] = $gBitSystem->getPreference('fisheye_gallery_default_thumbnail_size', (!empty($this->mInfo['thumbnail_size']) ? $this->mInfo['thumbnail_size'] : FISHEYE_DEFAULT_THUMBNAIL_SIZE));
		}

		if (empty($pStorageHash['title'])) {
			$this->mErrors[] = "You must specify a title for this image gallery";
		}

		if (empty($pStorageHash['edit'])) {
			$pStorageHash['edit'] = (!empty($this->mInfo['data']) ? $this->mInfo['data'] : '');
		}

		$pStorageHash['content_type_guid'] = FISHEYEGALLERY_CONTENT_TYPE_GUID;

		return (count($this->mErrors) == 0);
	}


	function generateThumbnails() {
		if( $this->isValid() ) {
			if( $this->loadImages() ) {
				foreach( array_keys( $this->mItems ) as $key ) {
					$this->mItems[$key]->generateThumbnails();
				}
			}
		}
	}


	function getThumbnailUrl() {
		if( empty( $this->mInfo['preview_content'] ) ) {
			$this->loadThumbnail();
		}

		if( is_object( $this->mInfo['preview_content'] ) ) {
			return $this->mInfo['preview_content']->getThumbnailUrl();
		}
	}


	function getThumbnailImage( $pContentId=NULL, $pThumbnailContentId=NULL, $pThumbnailContentType=NULL ) {
		global $gLibertySystem;
		$ret = NULL;

		if( empty( $pContentId ) ) {
			$pContentId = $this->mContentId;
		}

		if( empty( $pThumbnailContentId ) ) {
			if( !empty( $this->mInfo['preview_content_id'] ) ) {
				$pThumbnailContentId = $this->mInfo['preview_content_id'];
			} else {
				$query = "SELECT tfgim.`item_content_id`, tc.`content_type_guid`
						  FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON ( tfgim.`item_content_id`=tc.`content_id` )
						  WHERE tfgim.`gallery_content_id` = ?
						  ORDER BY ".$this->convert_sortmode('random');
				$rs = $this->query($query, array( $pContentId ), 1);
				$pThumbnailContentId = $rs->fields['item_content_id'];
				$pThumbnailContentType = $rs->fields['content_type_guid'];
			}
		}

		if( !empty( $pThumbnailContentId ) ) {
			$ret = $gLibertySystem->getLibertyObject( $pThumbnailContentId, $pThumbnailContentType );
			$ret->load();
			if( get_class( $ret ) == 'fisheyegallery' ) {
				//recurse down in to find the first image
				$ret = $ret->getThumbnailImage();
			}
		}
		return $ret;
	}


	function loadThumbnail( $pSize='small', $pContentId=NULL ) {
		$this->mInfo['preview_content'] = $this->getThumbnailImage( $pContentId );
	}


	function storeGalleryThumbnail($pContentId = NULL) {
		$ret = FALSE;
		if ($pContentId && !$this->isInGallery( $this->mContentId, $pContentId ) ) {
			return FALSE;
		}
		if ($this->mGalleryId) {
			if (!$pContentId)
				$pContentId = NULL;
			$query = "UPDATE `".BIT_DB_PREFIX."tiki_fisheye_gallery` SET `preview_content_id` = ? WHERE `gallery_id`= ?";
			$rs = $this->query($query, array($pContentId, $this->mGalleryId));
			$ret = TRUE;
		}
		return $ret;
	}

	function store(&$pStorageHash) {
		if ($this->verifyGalleryData($pStorageHash)) {
			$this->mDb->StartTrans();
			if( LibertyContent::store($pStorageHash)) {
				$this->mContentId = $pStorageHash['content_id'];
				$this->mInfo['content_id'] = $this->mContentId;
				if ($this->galleryExistsInDatabase()) {
					$query = "UPDATE `".BIT_DB_PREFIX."tiki_fisheye_gallery`
							SET `rows_per_page` = ?, `cols_per_page` = ?, `thumbnail_size` = ?
							WHERE `gallery_id` = ?";
					$bindVars = array($pStorageHash['rows_per_page'], $pStorageHash['cols_per_page'], $pStorageHash['thumbnail_size'], $this->mGalleryId);
				} else {
					$this->mGalleryId = $this->mDb->GenID('tiki_fisheye_gallery_id_seq');
					$this->mInfo['gallery_id'] = $this->mGalleryId;
					$query = "INSERT INTO `".BIT_DB_PREFIX."tiki_fisheye_gallery` (`gallery_id`, `content_id`, `rows_per_page`, `cols_per_page`, `thumbnail_size`) VALUES (?,?,?,?,?)";
					$bindVars = array($this->mGalleryId, $this->mContentId, $pStorageHash['rows_per_page'], $pStorageHash['cols_per_page'], $pStorageHash['thumbnail_size']);
				}
				$rs = $this->query($query, $bindVars);
				$this->mDb->mDb->CompleteTrans();
			} else {
				$this->mDb->mDb->RollbackTrans();
				$this->mErrors[] = "There were errors while attempting to save this gallery";
			}
		} else {
			$this->mErrors[] = "There were errors while attempting to save this gallery";
		}

		return (count($this->mErrors) == 0);
	}

	function removeItem( $pContentId ) {
		$ret = FALSE;
		if( $this->isValid() && is_numeric( $pContentId ) ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map`
					  WHERE `item_content_id`=? AND `gallery_content_id`=?";
			$rs = $this->query($query, array($pContentId, $this->mContentId ) );
			$ret = TRUE;
		}
		return $ret;
	}

	function addItem( $pContentId ) {
		$ret = FALSE;
		if( $this->isValid() && is_numeric( $pContentId ) && ( $this->mContentId != $pContentId ) && !$this->isInGallery( $this->mContentId, $pContentId ) ) {
			$query = "INSERT INTO `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` (`item_content_id`, `gallery_content_id`) VALUES (?,?)";
			$rs = $this->query($query, array($pContentId, $this->mContentId ) );
			$ret = TRUE;
		}
		return $ret;
	}

	function expunge( $pRecursiveDelete = FALSE ) {
//$this->debug();
		if( $this->isValid() ) {
			$this->mDb->StartTrans();


			if( $this->loadImages() ) {
				foreach( array_keys( $this->mItems ) as $key ) {
					if( $pRecursiveDelete ) {
						$this->mItems[$key]->expunge( $pRecursiveDelete );
					} elseif( $this->mItems[$key]->mInfo['content_type_guid'] == FISHEYEIMAGE_CONTENT_TYPE_GUID ) {
						$query = "SELECT COUNT(`item_content_id`) AS `other_gallery`
								  FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map`
								  WHERE `item_content_id`=? AND `gallery_content_id`!=?";
						if( $rs = $this->query($query, array($this->mItems[$key]->mContentId, $this->mContentId ) ) ) {
							if( empty( $rs->fields['other_gallery'] ) ) {
								$this->mItems[$key]->expunge();
							}
						}
					}
				}
			}

			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` WHERE `gallery_content_id`=?";
			$rs = $this->query($query, array( $this->mContentId ) );
			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` WHERE `item_content_id`=?";
			$rs = $this->query($query, array( $this->mContentId ) );
			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery` WHERE `content_id`=?";
			$rs = $this->query($query, array( $this->mContentId ) );
			if( LibertyContent::expunge() ) {
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
vd( $this->mErrors );
			}
		}
//$this->debug(0);
		return( count( $this->mErrors ) == 0 );
	}


	function galleryExistsInDatabase() {
		$ret = FALSE;

		if (!empty($this->mGalleryId)) {
			$query = "SELECT COUNT(`gallery_id`) AS `count`
					FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery`
					WHERE `gallery_id` = ?";
			$rs = $this->query($query, array($this->mGalleryId));
			if ($rs->fields['count'] > 0)
				$ret = TRUE;
		}

		return $ret;
	}

    /**
    * Returns include file that will
    * @return the fully specified path to file to be included
    */
	function getRenderFile() {
		return FISHEYE_PKG_PATH."display_fisheye_gallery_inc.php";
	}

    /**
    * Function that returns link to display a piece of content
    * @param pGalleryId id of gallery to link
    * @return the url to display the gallery.
    */
	function getDisplayUrl( $pGalleryId=NULL, $pPath=NULL ) {
		$ret = FISHEYE_PKG_URL;
		if( empty( $pGalleryId ) ) {
			$pGalleryId = $this->mGalleryId;
			$pPath = $this->mGalleryPath;
		}
		if( is_numeric( $pGalleryId ) ) {
			global $gBitSystem;
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
				$ret .= 'gallery'.$pPath.'/'.$pGalleryId;
			} else {
				$ret .= 'view.php?gallery_id='.$pGalleryId;
				if( !empty( $pPath ) ) {
					$ret .= '&gallery_path='.$pPath;
				}
			}
		} elseif( !empty( $pImageId['content_id'] ) ) {
			$ret = FISHEYE_PKG_URL.'view_image.php?content_id='.$pImageId['content_id'];
		}
		return $ret;
	}



	function getList( &$pListHash ) {
//	function getList($pUserId = NULL, $pFindString = NULL, $pSortMode = NULL, $pMaxRows = NULL) {
		global $gBitUser,$gBitSystem, $commentsLib;

		$this->prepGetList( $pListHash );
		$bindVars = array();
		$select = '';
		$mid = '';
		$join = '';

		$mapJoin = empty( $pListHash['show_empty'] ) ? ' INNER JOIN ' : ' LEFT OUTER JOIN ';
		// this *has* to go first because of bindVars order
		if( empty( $pListHash['show_empty'] ) ) {
		// This will nicely pull out the unused rows, but it is dog slow
//  			 $join .= " INNER JOIN  `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim ON (tfgim.`gallery_content_id`=tc.`content_id`) ";
			 $mid = '';
		}

		if( !empty( $pListHash['root_only'] ) ) {
			$join .= " LEFT OUTER JOIN  `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim2 ON (tfgim2.`item_content_id`=tc.`content_id`)";
			$mid .= ' AND tfgim2.`item_content_id` IS NULL ';
		}
		if( !empty( $pListHash['contain_item'] ) ) {
			$select = " , tfgim3.`item_content_id` AS `in_gallery` ";
			$join .= " LEFT OUTER JOIN  `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim3 ON (tfgim3.`gallery_content_id`=tc.`content_id`) AND tfgim3.`item_content_id`=? ";
			$bindVars[] = $pListHash['contain_item'];
		}
		if( !empty( $pListHash['user_id'] ) && is_numeric( $pListHash['user_id'] )) {
			$mid .= " AND tc.`user_id` = ? ";
			$bindVars[] = $pListHash['user_id'];
		}
		if( !empty( $pListHash['search'] ) ) {
			$mid .= " AND UPPER(tc.`title`) LIKE ? ";
			$bindVars[] = '%'.strtoupper( $pListHash['search'] ).'%';
		}
		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			$select .= ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer` ';
			$join .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs ON (tc.`content_id`=tcs.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON (ts.`security_id`=tcs.`security_id` )";
			$mid .= ' AND (tcs.`security_id` IS NULL OR tc.`user_id`=?) ';
			$bindVars[] = $gBitUser->mUserId;
		}

		if ( !empty( $pListHash['sort_mode'] ) ) {
			//converted in prepGetList()
			$mid .= " ORDER BY ".$this->convert_sortmode( $pListHash['sort_mode'] )." ";
		}

		$query = "SELECT DISTINCT( tfg.`gallery_id` ) AS `hash_key`, tfg.*, tc.*, uu.`login`, uu.`real_name`, ptc.`content_type_guid` AS `preview_content_type_guid` $select
				FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery` tfg LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content` ptc ON( tfg.`preview_content_id`=ptc.`content_id` ), `".BIT_DB_PREFIX."users_users` uu, `".BIT_DB_PREFIX."tiki_content` tc $join
				WHERE tfg.`content_id` = tc.`content_id` AND uu.`user_id` = tc.`user_id` $mid";
		if( $rs = $this->query( $query, $bindVars, $pListHash['max_records'],$pListHash['offset'] ) ) {
			$ret = $rs->GetAssoc();
			if( empty( $pListHash['no_thumbnails'] ) ) {
				$thumbsize = !empty( $pListHash['thumbnail_size'] ) ? $pListHash['thumbnail_size'] : 'small';
				foreach( array_keys( $ret ) as $galleryId ) {
					$ret[$galleryId]['display_url'] = $this->getDisplayUrl( $galleryId );
					if( $thumbImage = $this->getThumbnailImage( $ret[$galleryId]['content_id'], $ret[$galleryId]['preview_content_id'], $ret[$galleryId]['preview_content_type_guid'] ) ) {
						$ret[$galleryId]['thumbnail_url'] = $thumbImage->getThumbnailUrl( $thumbsize );
					} elseif( !empty( $pListHash['show_empty'] ) ) {
						$ret[$galleryId]['thumbnail_url'] = FISHEYE_PKG_URL.'image/no_image.png';
					} else {
						unset( $ret[$galleryId] );
					}
				}
			}
		}

		return $ret;
	}
}

?>