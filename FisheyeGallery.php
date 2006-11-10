<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/FisheyeGallery.php,v 1.44 2006/11/10 18:09:55 spiderr Exp $
 * @package fisheye
 */

/**
 * required setup
 */
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php' );		// A gallery is composed of FisheyeImages

define('FISHEYEGALLERY_CONTENT_TYPE_GUID', 'fisheyegallery' );

define( 'FISHEYE_PAGINATION_FIXED_GRID', 'fixed_grid' );
define( 'FISHEYE_PAGINATION_AUTO_FLOW', 'auto_flow' );
define( 'FISHEYE_PAGINATION_POSITION_NUMBER', 'position_number' );

/**
 * FisheyeBase extends LibertyAttachable, which this class doesn't need, but we need a common base class
 *
 * @package fisheye
 * @subpackage FisheyeGallery
 */
class FisheyeGallery extends FisheyeBase {
	var $mGalleryId;		// fisheye_gallery.gallery_id
	var $mItems;			// Array of FisheyeImage class instances which belong to this gallery

	function FisheyeGallery($pGalleryId = NULL, $pContentId = NULL) {
		FisheyeBase::FisheyeBase();		// Call base constructor
		$this->mGalleryId = (int)$pGalleryId;		// Set member variables according to the parameters we were passed
		$this->mContentId = (int)$pContentId;		// liberty_content.content_id which this gallery references
		$this->mItems = NULL;					// Assume no images (if $pAutoLoad is TRUE we will populate this array later)
		$this->mAdminContentPerm = 'p_fisheye_admin';

		// This registers the content type for FishEye galleries
		// FYI: Any class which uses a table which inherits from liberty_content should create their own content type(s)
		$this->registerContentType(FISHEYEGALLERY_CONTENT_TYPE_GUID, array('content_type_guid' => FISHEYEGALLERY_CONTENT_TYPE_GUID,
				'content_description' => 'Image Gallery',
				'handler_class' => 'FisheyeGallery',
				'handler_package' => 'fisheye',
				'handler_file' => 'FisheyeGallery.php',
				'maintainer_url' => 'http://www.bitweaver.org'
			) );
	}

	function isValid() {
		return( @$this->verifyId( $this->mGalleryId ) || @$this->verifyId( $this->mContentId ) );
	}

	function lookup( $pLookupHash ) {
		global $gBitDb;
		$ret = NULL;

		$lookupContentId = NULL;
		if (!empty($pLookupHash['gallery_id']) && is_numeric($pLookupHash['gallery_id'])) {
			if( $lookup = $gBitDb->getRow( "SELECT lc.`content_id`, lc.`content_type_guid` FROM `".BIT_DB_PREFIX."fisheye_gallery` fg INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(lc.`content_id`=fg.`content_id`) WHERE `gallery_id`=?", array( $pLookupHash['gallery_id'] ) ) ) {
				$lookupContentId = $lookup['content_id'];
				$lookupContentGuid = $lookup['content_type_guid'];
			}
		} elseif (!empty($pLookupHash['content_id']) && is_numeric($pLookupHash['content_id'])) {
			$lookupContentId = $lookup['content_id'];
			$lookupContentGuid = NULL;
		}
	
		if( BitBase::verifyId( $lookupContentId ) ) {
			$ret = LibertyBase::getLibertyObject( $lookupContentId, $lookupContentGuid );
		}

		return $ret;
	}

	function load( $pCurrentImageId=NULL ) {
		global $gBitSystem;
		$bindVars = array();
		$selectSql = $joinSql = $whereSql = '';

		if( @$this->verifyId( $this->mGalleryId ) ) {
			$whereSql = " WHERE fg.`gallery_id` = ?";
			$bindVars = array( $this->mGalleryId );
		} elseif ( @$this->verifyId( $this->mContentId ) ) {
			$whereSql = " WHERE fg.`content_id` = ?";
			$bindVars = array($this->mContentId);
		} else {
			$whereSql = $bindVars = NULL;
		}

		if ($whereSql) {	// If we have some way to know what fisheye_gallery row to load...
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$query = "SELECT fg.*, lc.* $selectSql
						, uue.`login` AS modifier_user, uue.`real_name` AS `modifier_real_name`
						, uuc.`login` AS creator_user, uuc.`real_name` AS `creator_real_name`
					FROM `".BIT_DB_PREFIX."fisheye_gallery` fg 
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (fg.`content_id` = lc.`content_id`) $joinSql
						LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON (uue.`user_id` = lc.`modifier_user_id`)
						LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON (uuc.`user_id` = lc.`user_id`)
					$whereSql";

			if( $rs = $this->mDb->query($query, $bindVars) ) {
				$this->mInfo = $rs->fields;
				$this->mContentId = $rs->fields['content_id'];
				LibertyContent::load();
				if( @$this->verifyId($this->mInfo['gallery_id'] ) ) {

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

					$this->mInfo['num_images'] = $this->getImageCount();
					if( $this->getPreference( 'gallery_pagination' ) == FISHEYE_PAGINATION_POSITION_NUMBER ) {
						$this->mInfo['num_pages'] = $this->mDb->getOne( "SELECT COUNT( distinct( floor(`item_position`) ) ) FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` WHERE gallery_content_id=?", array( $this->mContentId ) );
					} else {
						$this->mInfo['images_per_page'] = ($this->mInfo['cols_per_page'] * $this->mInfo['rows_per_page']);
						$this->mInfo['num_pages'] = (int)($this->mInfo['num_images'] / $this->mInfo['images_per_page'] + ($this->mInfo['num_images'] % $this->mInfo['images_per_page'] == 0 ? 0 : 1));
					}

				} else {
					unset( $this->mContentId );
					unset( $this->mGalleryId );
				}
				if( @$this->verifyId( $pCurrentImageId ) ) {
					// this code sucks but works - XOXO spiderr
					$query = "SELECT fgim.*, fi.`image_id`, lf.`storage_path`
							FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim
								INNER JOIN `".BIT_DB_PREFIX."fisheye_image` fi ON ( fi.`content_id`=fgim.`item_content_id` )
								INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON ( la.`content_id`=fi.`content_id` )
								INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON ( lf.`file_id`=la.`foreign_id` )
							WHERE fgim.`gallery_content_id` = ?
							ORDER BY fgim.`item_position`, fi.`content_id` ";
					if( $rs = $this->mDb->query($query, array( $this->mContentId ) ) ) {
						$rows = $rs->getRows();
						for( $i = 0; $i < count( $rows ); $i++ ) {
							if( $rows[$i]['image_id'] == $pCurrentImageId ) {
								if( $i > 0 ) {
									$this->mInfo['previous_image_id'] = $rows[$i-1]['image_id'];

									$trailingName = dirname( $rows[$i-1]['storage_path'] )."/avatar.jpg";
									if( file_exists( BIT_ROOT_PATH.$trailingName ) ) {
										$this->mInfo['previous_image_avatar'] = BIT_ROOT_URL.$trailingName;
									} else {
										$mime_type = BitSystem::lookupMimeType( preg_match( "/\..*?$/", $rows[$i-1]['storage_path'] ) );
										$this->mInfo['previous_image_avatar'] = LibertySystem::getMimeThumbnailURL( $mime_type );
									}
								}
								if( $i + 1  < count( $rows ) ) {
									$this->mInfo['next_image_id'] = $rows[$i+1]['image_id'];

									$trailingName = dirname( $rows[$i+1]['storage_path'] )."/avatar.jpg";
									if( file_exists( BIT_ROOT_PATH.$trailingName ) ) {
										$this->mInfo['next_image_avatar'] = BIT_ROOT_URL.$trailingName;
									} else {
										$mime_type = BitSystem::lookupMimeType( preg_match( "/\..*?$/", $rows[$i+1]['storage_path'] ) );
										$this->mInfo['next_image_avatar'] = LibertySystem::getMimeThumbnailURL( $mime_type );
									}
								}
							}
						}
					}
				}
			}
		}

		return count($this->mInfo);
	}

	function loadImages( $pPage=-1 ) {
		global $gLibertySystem, $gBitSystem, $gBitUser;
		if( !$this->isValid() ) {
			return NULL;
		}

		$bindVars = array($this->mContentId);
		$mid = '';
		$whereSql = '';
		$select = '';
		$join = '';
		$rows = NULL;
		$offset = NULL;
		// load for just a single page
		if( $pPage != -1 ) {
			if( $this->getPreference( 'gallery_pagination' ) == FISHEYE_PAGINATION_POSITION_NUMBER ) {
				$query = "SELECT DISTINCT(FLOOR(`item_position`)) 
						  FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` 
						  WHERE gallery_content_id=?
						  ORDER BY floor(item_position)";
				$mantissa = $this->mDb->getOne( $query, array( $this->mContentId ), 1, ($pPage - 1) );
				$whereSql .= " AND floor(item_position)=? ";
				array_push( $bindVars, $mantissa );
			} else {
				$rows = $this->getField( 'rows_per_page' ) * $this->getField( 'cols_per_page' );
				$offset = $rows * ($pPage - 1);
			}
		}

		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			$select .= ' ,ls.`security_id`, ls.`security_description`, ls.`is_private`, ls.`is_hidden`, ls.`access_question`, ls.`access_answer` ';
			$join .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."gatekeeper_security_map` cg ON (lc.`content_id`=cg.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."gatekeeper_security` ls ON (ls.`security_id`=cg.`security_id` )";
//			$where = ' AND (cg.`security_id` IS NULL OR lc.`user_id`=?) ';
//			$bindVars[] = $gBitUser->mUserId;
		}
		$this->mItems = NULL;

		$query = "SELECT fgim.*, lc.`content_type_guid`, lc.`user_id`, lct.*, ufm.`favorite_content_id` AS is_favorite $select
				FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim 
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id`=fgim.`item_content_id` ) 
					INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lct.`content_type_guid`=lc.`content_type_guid` ) 
					$join  
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_favorites_map` ufm ON ( ufm.`favorite_content_id`=lc.`content_id` AND lc.`user_id`=ufm.`user_id` )
				WHERE fgim.`gallery_content_id` = ? $whereSql
				ORDER BY fgim.`item_position`, fgim.`item_content_id` $mid";
		$rs = $this->mDb->query($query, $bindVars, $rows, $offset);

		$rows = $rs->getRows();
		foreach ($rows as $row) {
			$pass = TRUE;
			if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
				$pass = $gBitUser->hasPermission( 'p_fisheye_admin' ) || !@$this->verifyId( $row['security_id'] ) || ( $row['user_id'] == $gBitUser->mUserId ) || @$this->verifyId( $_SESSION['gatekeeper_security'][$row['security_id']] );
			}
			if( $pass ) {
				$type = $gLibertySystem->mContentTypes[$row['content_type_guid']];
				require_once( constant( strtoupper( $type['handler_package'] ).'_PKG_PATH' ).$type['handler_file'] );
				$item = new $type['handler_class']( NULL, $row['item_content_id'] );
				if( is_object( $item ) && $item->load() ) {
					$item->loadThumbnail( $this->mInfo['thumbnail_size'] );
					$item->setGalleryPath( $this->mGalleryPath.'/'.$this->mGalleryId );
					$item->mInfo['item_position'] = $row['item_position'];
					$this->mItems[$row['item_content_id']] = $item;
				}
			}
		}
		return count( $this->mItems );
	}

	function exportHtml( $pPaginate = FALSE ) {
		$ret = NULL;
		$ret['metadata'] = array(	'type' => $this->getContentType(),
						'landscape' => FALSE,
						'url' => $this->getDisplayUrl(),
						'content_id' => $this->mContentId,
					);
		if( $this->loadImages() ) {
			foreach( array_keys( $this->mItems ) as $key ) {
				if( $pPaginate ) {
					$ret['content']['page'][$this->getItemPage($key)][] = $this->mItems[$key]->exportHtml();;
				} else {
					$ret['content'][] = $this->mItems[$key]->exportHtml();
				}
			}
		}
		return $ret;
	}

	function getItemPage( $pItemContentId ) {
		$ret = NULL;
		if( empty( $this->mPaginationLookup ) ) {
			$this->mPaginationLookup = $this->mDb->getAssoc( "SELECT `item_content_id`, floor(`item_position`) FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` WHERE `gallery_content_id`=?", array( $this->mContentId ) );
		}
		if( !empty( $this->mPaginationLookup[$pItemContentId] ) ) {
			$ret = $this->mPaginationLookup[$pItemContentId];
		}
		return $ret;
	}

	function getImageCount() {
		$ret = 0;

		if ($this->mGalleryId) {
			$query = 'SELECT COUNT(*) AS "count"
					FROM `'.BIT_DB_PREFIX.'fisheye_gallery_image_map`
					WHERE `gallery_content_id` = ?';
			$rs = $this->mDb->query($query, array($this->mContentId));
			$ret = $rs->fields['count'];
		}
		return $ret;
	}

	function verifyGalleryData(&$pStorageHash) {
		global $gBitSystem;

		if (empty($pStorageHash['rows_per_page'])) {
			$pStorageHash['rows_per_page'] = $gBitSystem->getConfig('fisheye_gallery_default_rows_per_page', (!empty($this->mInfo['rows_per_page']) ? $this->mInfo['rows_per_page'] : FISHEYE_DEFAULT_ROWS_PER_PAGE));
		}

		if (empty($pStorageHash['cols_per_page'])) {
			$pStorageHash['cols_per_page'] = $gBitSystem->getConfig('fisheye_gallery_default_cols_per_page', (!empty($this->mInfo['cols_per_page']) ? $this->mInfo['cols_per_page'] : FISHEYE_DEFAULT_COLS_PER_PAGE));
		}

		if (empty($pStorageHash['thumbnail_size'])) {
			$pStorageHash['thumbnail_size'] = $gBitSystem->getConfig('fisheye_gallery_default_thumbnail_size', (!empty($this->mInfo['thumbnail_size']) ? $this->mInfo['thumbnail_size'] : FISHEYE_DEFAULT_THUMBNAIL_SIZE));
		}

		if (empty($pStorageHash['title'])) {
			$this->mErrors[] = "You must specify a title for this image gallery";
		}

		$pStorageHash['content_type_guid'] = $this->getContentType();

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


	function getThumbnailUrl( $pSize='small' ) {
		if( empty( $this->mInfo['preview_content'] ) ) {
			$this->loadThumbnail();
		}

		if( is_object( $this->mInfo['preview_content'] ) ) {
			return $this->mInfo['preview_content']->getThumbnailUrl( $pSize );
		}
	}


	function getThumbnailImage( $pContentId=NULL, $pThumbnailContentId=NULL, $pThumbnailContentType=NULL ) {
		global $gLibertySystem;
		$ret = NULL;

		if( !@$this->verifyId( $pContentId ) ) {
			$pContentId = $this->mContentId;
		}



		if( !@$this->verifyId( $pThumbnailContentId ) ) {
			if( @$this->verifyId( $this->mInfo['preview_content_id'] ) ) {
				$pThumbnailContentId = $this->mInfo['preview_content_id'];
			} else {
				if( $this->mDb->isAdvancedPostgresEnabled() ) {
					$query = "SELECT COALESCE( fg.`preview_content_id`, lc.`content_id` )
							FROM connectby('`".BIT_DB_PREFIX."fisheye_gallery_image_map`', '`item_content_id`', '`gallery_content_id`', ?, 0, '/') AS t(`cb_item_content_id` int, `cb_parent_content_id` int, `level` int, `branch` text)
								INNER JOIN liberty_content lc ON(content_id=cb_item_content_id)
								LEFT OUTER JOIN `".BIT_DB_PREFIX."gatekeeper_security_map` cgm ON (cgm.`content_id`=lc.`content_id`), `".BIT_DB_PREFIX."fisheye_gallery` fg
							WHERE lc.`content_type_guid`='fisheyeimage' AND cgm.`security_id` IS NULL AND `cb_parent_content_id`=fg.`content_id` ORDER BY RANDOM()";
					if( $pThumbnailContentId = $this->mDb->getOne( $query, array( $pContentId ) ) ) {
						$pThumbnailContentType = 'fisheyeimage';
					}
				} else {
					$query = "SELECT fgim.`item_content_id`, lc.`content_type_guid`
							FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( fgim.`item_content_id`=lc.`content_id` )
							WHERE fgim.`gallery_content_id` = ?
							ORDER BY ".$this->mDb->convert_sortmode('random');
					$rs = $this->mDb->query($query, array( $pContentId ), 1);
					$pThumbnailContentId = $rs->fields['item_content_id'];
					$pThumbnailContentType = $rs->fields['content_type_guid'];
				}
			}
		}

		if( @$this->verifyId( $pThumbnailContentId ) ) {
			$ret = $gLibertySystem->getLibertyObject( $pThumbnailContentId, $pThumbnailContentType );
			if( strtolower( get_class( $ret ) ) == 'fisheyegallery' ) {
				//recurse down in to find the first image
				$ret = $ret->getThumbnailImage();
			}
		}
		return $ret;
	}


	function loadThumbnail( $pSize='small', $pContentId=NULL ) {
		$this->mPreviewImage = $this->getThumbnailImage( $pContentId );
		$this->mInfo['preview_content'] = &$this->mPreviewImage;
	}


	function storeGalleryThumbnail($pContentId = NULL) {
		$ret = FALSE;
		if ($pContentId && !$this->isInGallery( $this->mContentId, $pContentId ) ) {
			return FALSE;
		}
		if ($this->mGalleryId) {
			if (!$pContentId)
				$pContentId = NULL;
			$query = "UPDATE `".BIT_DB_PREFIX."fisheye_gallery` SET `preview_content_id` = ? WHERE `gallery_id`= ?";
			$rs = $this->mDb->query($query, array($pContentId, $this->mGalleryId));
			$this->mInfo['preview_content_id'] = $pContentId;
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
					$query = "UPDATE `".BIT_DB_PREFIX."fisheye_gallery`
							SET `rows_per_page` = ?, `cols_per_page` = ?, `thumbnail_size` = ? 
							WHERE `gallery_id` = ?";
					$bindVars = array($pStorageHash['rows_per_page'], $pStorageHash['cols_per_page'], $pStorageHash['thumbnail_size'], $this->mGalleryId);
				} else {
					$this->mGalleryId = $this->mDb->GenID('fisheye_gallery_id_seq');
					$this->mInfo['gallery_id'] = $this->mGalleryId;
					$query = "INSERT INTO `".BIT_DB_PREFIX."fisheye_gallery` (`gallery_id`, `content_id`, `rows_per_page`, `cols_per_page`, `thumbnail_size`) VALUES (?,?,?,?,?)";
					$bindVars = array($this->mGalleryId, $this->mContentId, $pStorageHash['rows_per_page'], $pStorageHash['cols_per_page'], $pStorageHash['thumbnail_size']);
				}
				$rs = $this->mDb->query($query, $bindVars);
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
				$this->mErrors[] = "There were errors while attempting to save this gallery";
			}
		} else {
			$this->mErrors[] = "There were errors while attempting to save this gallery";
		}

		return (count($this->mErrors) == 0);
	}

	function removeItem( $pContentId ) {
		$ret = FALSE;
		if( $this->isValid() && @$this->verifyId( $pContentId ) ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map`
					  WHERE `item_content_id`=? AND `gallery_content_id`=?";
			$rs = $this->mDb->query($query, array($pContentId, $this->mContentId ) );
			$ret = TRUE;
		}
		return $ret;
	}

    /**
    * Adds a new item (image or gallery) to this gallery. We check to make sure we are not a member
	* of this gallery and this gallery is not a member of the new item to avoid infinite recursion scenarios
    * @return boolean wheter or not the item was added
    */
	function addItem( $pContentId, $pPosition=NULL ) {
		$ret = FALSE;
		if( @$this->verifyId( $this->mContentId ) && @$this->verifyId( $pContentId ) && ( $this->mContentId != $pContentId ) && !$this->isInGallery( $this->mContentId, $pContentId  )  && !$this->isInGallery( $pContentId, $this->mContentId ) ) {
			$query = "INSERT INTO `".BIT_DB_PREFIX."fisheye_gallery_image_map` (`item_content_id`, `gallery_content_id`, `item_position`) VALUES (?,?,?)";
			$rs = $this->mDb->query($query, array($pContentId, $this->mContentId, $pPosition ) );
			$ret = TRUE;
		}
		return $ret;
	}

	function expunge( $pRecursiveDelete = FALSE ) {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();


			if( $this->loadImages() ) {
				foreach( array_keys( $this->mItems ) as $key ) {
					if( $pRecursiveDelete ) {
						$this->mItems[$key]->expunge( $pRecursiveDelete );
					} elseif( $this->mItems[$key]->mInfo['content_type_guid'] == FISHEYEIMAGE_CONTENT_TYPE_GUID ) {
						$query = "SELECT COUNT(`item_content_id`) AS `other_gallery`
								  FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map`
								  WHERE `item_content_id`=? AND `gallery_content_id`!=?";
						if( $rs = $this->mDb->query($query, array($this->mItems[$key]->mContentId, $this->mContentId ) ) ) {
							if( empty( $rs->fields['other_gallery'] ) ) {
								$this->mItems[$key]->expunge();
							}
						}
					}
				}
			}

			$query = "DELETE FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` WHERE `gallery_content_id`=?";
			$rs = $this->mDb->query($query, array( $this->mContentId ) );
			$query = "DELETE FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` WHERE `item_content_id`=?";
			$rs = $this->mDb->query($query, array( $this->mContentId ) );
			$query = "DELETE FROM `".BIT_DB_PREFIX."fisheye_gallery` WHERE `content_id`=?";
			$rs = $this->mDb->query($query, array( $this->mContentId ) );
			if( LibertyContent::expunge() ) {
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
vd( $this->mErrors );
			}
		}
		return( count( $this->mErrors ) == 0 );
	}


	function galleryExistsInDatabase() {
		$ret = FALSE;

		if( @$this->verifyId( $this->mGalleryId ) ) {
			$query = "SELECT COUNT(`gallery_id`) AS `count`
					FROM `".BIT_DB_PREFIX."fisheye_gallery`
					WHERE `gallery_id` = ?";
			$rs = $this->mDb->query($query, array($this->mGalleryId));
			if ($rs->fields['count'] > 0)
				$ret = TRUE;
		}

		return $ret;
	}

    /**
    * Returns the layout of the gallery accounting for various defaults
    * @return the layout string preference
    */
	function getLayout() {
		global $gBitSystem;
		return $this->getPreference( 'gallery_pagination', $gBitSystem->getConfig( 'default_gallery_pagination', 'fixed_grid' ) );
	}

    /**
    * Returns include file that will setup the object for rendering
    * @return the fully specified path to file to be included
    */
	function getRenderFile() {
		return FISHEYE_PKG_PATH."display_fisheye_gallery_inc.php";
	}

    /**
    * Returns template file used for display
    * @return the fully specified path to file to be included
    */
	function getRenderTemplate() {
		return 'bitpackage:fisheye/view_gallery.tpl';
	}

    /**
    * Function that returns link to display a piece of content
    * @param pGalleryId id of gallery to link
    * @return the url to display the gallery.
    */
	function getDisplayUrl( $pGalleryId=NULL, $pPath=NULL ) {
		$ret = FISHEYE_PKG_URL;
		if( !@$this->verifyId( $pGalleryId ) ) {
			$pGalleryId = $this->mGalleryId;
			$pPath = $this->mGalleryPath;
		}
		if( @$this->verifyId( $pGalleryId ) ) {
			global $gBitSystem;
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
				$ret .= 'gallery'.$pPath.'/'.$pGalleryId;
			} else {
				$ret .= 'view.php?gallery_id='.$pGalleryId;
				if( !empty( $pPath ) ) {
					$ret .= '&gallery_path='.$pPath;
				}
			}
		} elseif( @$this->verifyId( $pImageId['content_id'] ) ) {
			$ret = FISHEYE_PKG_URL.'view_image.php?content_id='.$pImageId['content_id'];
		}
		return $ret;
	}

	function getList( &$pListHash ) {
		global $gBitUser,$gBitSystem, $gBitDbType, $commentsLib;

		$this->prepGetList( $pListHash );
		$bindVars = array();
		$selectSql = $joinSql = $whereSql = $sortSql = '';

		if( $gBitDbType == 'mysql' ) {
			// loser mysql without subselects
			if( !empty( $pListHash['root_only'] ) ) {
				$joinSql .= " LEFT OUTER JOIN  `".BIT_DB_PREFIX."fisheye_gallery_image_map` tfgim2 ON (tfgim2.`item_content_id`=lc.`content_id`)";
				$whereSql .= ' AND tfgim2.`item_content_id` IS NULL ';
			}
		}

		if( !empty( $pListHash['contain_item'] ) ) {
			$selectSql = " , tfgim3.`item_content_id` AS `in_gallery` ";
			$joinSql .= " LEFT OUTER JOIN  `".BIT_DB_PREFIX."fisheye_gallery_image_map` tfgim3 ON (tfgim3.`gallery_content_id`=lc.`content_id`) AND tfgim3.`item_content_id`=? ";
			$bindVars[] = $pListHash['contain_item'];
		}

		if( @$this->verifyId( $pListHash['user_id'] ) ) {
			$whereSql .= " AND lc.`user_id` = ? ";
			$bindVars[] = $pListHash['user_id'];
		}

		if( !empty( $pListHash['find'] ) ) {
			$whereSql .= " AND UPPER( lc.`title` ) LIKE ? ";
			$bindVars[] = '%'.strtoupper( $pListHash['find'] ).'%';
		}

		if( !empty( $pListHash['show_public'] ) ) {
			$joinSql .= " LEFT OUTER JOIN  `".BIT_DB_PREFIX."liberty_content_prefs` lcp ON( lcp.`content_id`=lc.`content_id` )";
			$whereSql .= " OR  ( lcp.`pref_name`=? AND lcp.`pref_value`=? ) ";
			$bindVars[] = 'is_public';
			$bindVars[] = 'y';
		}

		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			$selectSql .= ' ,ls.`security_id`, ls.`security_description`, ls.`is_private`, ls.`is_hidden`, ls.`access_question`, ls.`access_answer` ';
			$joinSql .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."gatekeeper_security_map` cg ON (lc.`content_id`=cg.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."gatekeeper_security` ls ON (ls.`security_id`=cg.`security_id` )";
			if( !$gBitUser->isAdmin() ) {
				$whereSql .= ' AND (cg.`security_id` IS NULL OR lc.`user_id`=?) ';
				$bindVars[] = $gBitUser->mUserId;
			}
		}

		$mapJoin = "";
		if( $gBitDbType != 'mysql' ) {
			// weed out empty galleries if we don't need them. DO NOT get clever and change the IN and EXISTS choices here.
			if( empty( $pListHash['show_empty'] ) ) {
				$whereSql .= " AND fg.`content_id` IN (SELECT `gallery_content_id` FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim WHERE fgim.`gallery_content_id`=fg.`content_id`)";
			}
			if( !empty( $pListHash['root_only'] ) ) {
				$whereSql .= " AND NOT EXISTS (SELECT `gallery_content_id` FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` tfgim2 WHERE tfgim2.`item_content_id`=lc.`content_id`)";
			}
		} else {
			// weed out empty galleries if we don't need them
			if( empty( $pListHash['show_empty'] ) ) {
				$mapJoin = "INNER JOIN `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim ON (fgim.`gallery_content_id`=lc.`content_id`)";
			}
		}

		if ( !empty( $pListHash['sort_mode'] ) ) {
			//converted in prepGetList()
			$sortSql .= " ORDER BY ".$this->mDb->convert_sortmode( $pListHash['sort_mode'] )." ";
		}
		// Putting in the below hack because mssql cannot select distinct on a text blob column.
		$selectSql .= $gBitDbType == 'mssql' ? " ,CAST(lc.`data` AS VARCHAR(250)) as `data` " : " ,lc.`data` ";

		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		if( !empty( $whereSql ) ) {
			$whereSql = substr_replace( $whereSql, ' WHERE ', 0, 4 );
		}

		$query = "SELECT fg.`gallery_id` AS `hash_key`, fg.*, 
					lc.`content_id`, lc.`user_id`, lc.`modifier_user_id`, lc.`created`, lc.`last_modified`,
					lc.`content_type_guid`, lc.`format_guid`, lch.`hits`, lch.`last_hit`, lc.`event_time`, lc.`version`,
					lc.`lang_code`, lc.`title`, lc.`ip`, uu.`login`, uu.`real_name`, plc.`content_type_guid` AS `preview_content_type_guid`
					$selectSql
				FROM `".BIT_DB_PREFIX."fisheye_gallery` fg
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (fg.`content_id` = lc.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id` = lc.`user_id`)
					LEFT JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON (lch.`content_id` = lc.`content_id`)
					$mapJoin $joinSql
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` plc ON (fg.`preview_content_id` = plc.`content_id`)
				$whereSql $sortSql";
		if( $rs = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] ) ) {
			$data = $rs->GetAssoc();
			if( empty( $pListHash['no_thumbnails'] ) ) {
				$thumbsize = !empty( $pListHash['thumbnail_size'] ) ? $pListHash['thumbnail_size'] : 'small';
				foreach( array_keys( $data ) as $galleryId ) {
					$data[$galleryId]['display_url'] = $this->getDisplayUrl( $galleryId );
					if( $thumbImage = $this->getThumbnailImage( $data[$galleryId]['content_id'], $data[$galleryId]['preview_content_id'], $data[$galleryId]['preview_content_type_guid'] ) ) {
						$data[$galleryId]['thumbnail_url'] = $thumbImage->getThumbnailUrl( $thumbsize );
					} elseif( !empty( $pListHash['show_empty'] ) ) {
						$data[$galleryId]['thumbnail_url'] = FISHEYE_PKG_URL.'image/no_image.png';
					} else {
						unset( $data[$galleryId] );
					}
				}
			}
		}

		// count galleries
		$query_c = "SELECT COUNT( fg.`gallery_id` )
					FROM `".BIT_DB_PREFIX."fisheye_gallery` fg
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (fg.`content_id` = lc.`content_id`)
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id` = lc.`user_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` ptc ON( fg.`preview_content_id`=ptc.`content_id` )
				$mapJoin $joinSql
				$whereSql";
		$cant = $this->mDb->getOne( $query_c, $bindVars );

		$ret['cant'] = $cant;
		$ret['data'] = $data;
		return $ret;
	}
}

?>
