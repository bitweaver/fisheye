<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/FisheyeBase.php,v 1.18 2006/02/19 19:54:17 lsces Exp $
 * @package fisheye
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );		// FisheyeGallery base class

/**
 * @package fisheye
 * @subpackage FisheyeBase
 */
class FisheyeBase extends LibertyAttachable
{
	// Path of gallery images to get breadcrumbs
	var $mGalleryPath;

	function FisheyeBase() {
		$this->mGalleryPath = '';
		if( get_class( $this ) == 'fisheyegallery' ) {
			LibertyContent::LibertyContent();
		} else {
			LibertyAttachable::LibertyAttachable();
		}
	}

	// regular expression to determine if the title was computer generated
	function isMachineName( $pString ) {
		return( preg_match( '/(^[0-9][-0-9 ]*$)|(^[-0-9 ]*(img|dsc|dscn|pict|htg|dscf)[-0-9 ]*$)/i', trim( $pString ) ) );
	}

	// Gets a list of galleries which this item is attached to
	function getParentGalleries( $pContentId=NULL ) {
		if( !$this->verifyId( $pContentId ) ) {
			$pContentId = $this->mContentId;
		}
		$ret = NULL;

		if( is_numeric( $pContentId ) ) {
			$sql = "SELECT fg.`gallery_id` AS `hash_key`, fg.*, lc.`title`
					FROM `".BIT_DB_PREFIX."fisheye_gallery` fg, `".BIT_DB_PREFIX."liberty_content` lc, `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim
					WHERE fgim.`item_content_id` = ? AND fgim.`gallery_content_id`=fg.`content_id` AND fg.`content_id`=lc.`content_id`";
			$ret = $this->mDb->getAssoc( $sql, array( $pContentId ) );
		}
		return $ret;
	}

	function loadParentGalleries() {
		if( $this->isValid() ) {
			$this->mInfo['parent_galleries'] = $this->getParentGalleries();
		}
	}

	function updatePosition($pGalleryContentId, $newPosition = NULL) {
		if( $pGalleryContentId && $newPosition && $this->verifyId($this->mContentId) ) {
			// SQL optimization to prevent stupid updates of identical data
			$sql = "UPDATE `".BIT_DB_PREFIX."fisheye_gallery_image_map` SET `item_position` = ?
					WHERE `item_content_id` = ? AND `gallery_content_id` = ? AND (`item_position` IS NULL OR `item_position`!=?)";
			$rs = $this->mDb->query($sql, array($newPosition, $this->mContentId, $pGalleryContentId, $newPosition));
		}
	}

	function setGalleryPath( $pPath ) {
		$this->mGalleryPath = rtrim( $pPath, '/' );
	}


	// THis is a function that creates a mack daddy function to get a breadcrumb path with a single query.
	// Do not muck with this query unless you really, truly understand what is going on.
	function getBreadcrumbLinks() {
		$ret = '';
		if( !empty( $this->mGalleryPath ) ) {
			$path = split( '/', ltrim( $this->mGalleryPath, '/' ) );
			$p = 0;
			$c = 1;
			$joinSql = '';
			$selectSql = '';//AS title$g, fg$g.gallery_id AS gallery_id$g";
			$whereSql = '';
			$bindVars = array();
			foreach( $path as $galleryId ) {
				if( $galleryId ) {
					$p++; $c++;
					$selectSql .= " lc$p.`title` AS `title$p`, fg$p.`gallery_id` AS `gallery_id$p`,";
					$joinSql .= " `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim$p
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc$p ON(fgim$p.`gallery_content_id`=lc$p.`content_id`)
						INNER JOIN `".BIT_DB_PREFIX."fisheye_gallery` fg$p ON(fg$p.`content_id`=lc$p.`content_id`),";
					$whereSql .= " fg$p.`gallery_id`=? AND fgim$p.`item_content_id`=lc$c.`content_id` AND ";
					array_push( $bindVars, $galleryId );
				}
			}
//			$selectSql .= " lc$c.title AS title$c ";//AS title$g, fg$g.gallery_id AS gallery_id$g";
			$joinSql .= " `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim$c
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc$c ON(fgim$c.`item_content_id`=lc$c.`content_id`) ";
			$whereSql .= " lc$c.`content_id`=?  AND fgim$c.`gallery_content_id`=lc$p.`content_id` ";
			array_push( $bindVars, $this->mContentId );
			$rs = $this->mDb->query( "SELECT ".rtrim( $selectSql, ',')." FROM ".rtrim( $joinSql, ',')." WHERE $whereSql", $bindVars );
			if( !empty( $rs->fields ) ) {
				$path = '';
				for( $i = 1; $i <= (count( $rs->fields ) / 2); $i++ ) {
					$ret .= ' <a href="'.FisheyeGallery::getDisplayUrl( $rs->fields['gallery_id'.$i], $path ).'" >'.$rs->fields['title'.$i].'</a> &raquo; ';
					$path .= '/'.$rs->fields['gallery_id'.$i];
				}
			}
			$ret .= $this->getTitle();
		}
		return $ret;
	}


	function addToGalleries( $pGalleryArray, $pPosition=NULL ) {
		if( $this->isValid() ) {
			$inGalleries = $this->mDb->getAssoc( "SELECT `gallery_id`,`gallery_content_id` FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim INNER JOIN `".BIT_DB_PREFIX."fisheye_gallery` fg ON (fgim.`gallery_content_id`=fg.`content_id`) WHERE `item_content_id` = ?", array( $this->mContentId ) );
			$galleries = array();
			if( count( $pGalleryArray ) ) {
				foreach( $pGalleryArray as $galleryId ) {
					// image has been requested to be put in a new gallery
					if( empty( $inGalleries[$galleryId] ) ) {
						if( empty( $galleries[$galleryId] ) ) {
							$galleries[$galleryId] = new FisheyeGallery( $galleryId );
							$galleries[$galleryId]->load();
						}
						if( $galleries[$galleryId]->isValid() ) {
							if( $galleries[$galleryId]->hasUserPermission( 'bit_p_edit_fisheye' ) ) {
								$galleries[$galleryId]->addItem( $this->mContentId, $pPosition );
							} else {
								$this->mErrors[] = "You do not have permission to attach ".$this->getTitle()." to ".$galleries[$galleryId]->getTitle();
							}
						}
					} else {
						// image already in an existing gallery.
						unset( $inGalleries[$galleryId] );
					}
				}
			}
			if( count( $inGalleries ) ) {
				// if we have any left over in the inGalleries array, we should delete them. these were the "unchecked" boxes
				foreach( $inGalleries as $galleryId ) {
					$sql = "DELETE FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` WHERE `gallery_content_id` = ? AND `item_content_id` = ?";
					$rs = $this->mDb->query($sql, array( $galleryId, $this->mContentId ) );
				}
			}
		}
	}

	function isInGallery( $pGalleryContentId, $pItemContentId = NULL) {
		if( !$this->verifyId( $pItemContentId ) ) {
			$pItemContentId = $this->mContentId;
		}
		$ret = FALSE;
		if ( is_numeric( $this->mGalleryId ) && is_numeric( $pGalleryContentId ) ) {

			if( $this->mDb->isAdvancedPostgresEnabled() ) {
				global $gBitDb, $gBitSmarty;
				// This code pulls all branches for the current node and determines if there is a path from this content to the root
				// without hitting a security_id. If there is clear path it returns TRUE. If there is a security_id, then
				// it determines if the current user has permission
				$query = "SELECT branch,level,cb_item_content_id,cb_gallery_content_id
						  FROM connectby('`".BIT_DB_PREFIX."fisheye_gallery_image_map`', '`gallery_content_id`', '`item_content_id`', ?, 0, '/') AS t(`cb_gallery_content_id` int,`cb_item_content_id` int, `level` int, `branch` text)
						  WHERE `cb_gallery_content_id`=?
						  ORDER BY branch
						";
				if ( $this->mDb->getOne($query, array(  $pItemContentId, $pGalleryContentId ) ) ) {
					$ret = TRUE;
				}
			} else {
				$sql = "SELECT count(`item_content_id`) as `item_count`
						FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map`
						WHERE `gallery_content_id` = ? AND `item_content_id` = ?";
				$rs = $this->mDb->query($sql, array($pGalleryContentId, $pItemContentId));
				if ($rs->fields['item_count'] > 0) {
					$ret = TRUE;
				}
			}
		}
		return $ret;
	}

	/**
	* Overloaded function that determines if this content can be edited by the current gBitUser
	* @return the fully specified path to file to be included
	*/
	function hasUserPermission( $pPermName, $pFatalIfFalse=FALSE, $pFatalMessage=NULL ) {
		$ret = FALSE;
		if( $pPermName == 'bit_p_edit_fisheye' || $pPermName == 'bit_p_upload_fisheye' ) {
			if( !($ret = $this->isOwner()) ) {
				global $gBitUser;
				if( !($ret = $gBitUser->isAdmin()) ) {
					if( $this->loadPermissions() ) {
						$userPerms = $this->getUserPermissions( $gBitUser->mUserId );
						$ret = isset( $userPerms[$pPermName]['user_id'] ) && ( $userPerms[$pPermName]['user_id'] == $gBitUser->mUserId );
					}
				}
			}
		} else {
			$ret = LibertyContent::hasUserPermission( $pPermName, $pFatalMessage );
		}
		if( !$ret && $pFatalIfFalse ) {
			global $gBitSystem;
			$gBitSystem->fatalPermission( $pPermName, $pFatalIfFalse=FALSE, $pFatalMessage=NULL );
		}

		return( $ret );
	}



}
?>
