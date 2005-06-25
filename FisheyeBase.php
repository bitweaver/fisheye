<?php
require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );		// FisheyeGallery base class

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
		return( preg_match( '/(^[0-9][-0-9 ]*$)|(^[-0-9 ]*(img|dsc|dscn|pict|htg|dscf)[-0-9 ]*$)/i', $pString ) );
	}

	// Gets a list of galleries which this item is attached to
	function getParentGalleries( $pContentId=NULL ) {
		if( empty( $pContentId ) ) {
			$pContentId = $this->mContentId;
		}
		$ret = NULL;

		if( is_numeric( $pContentId ) ) {
			$sql = "SELECT tfg.`gallery_id` AS `hash_key`, tfg.*, tc.`title`
					FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery` tfg, `".BIT_DB_PREFIX."tiki_content` tc, `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim
					WHERE tfgim.`item_content_id` = ? AND tfgim.`gallery_content_id`=tfg.`content_id` AND tfg.`content_id`=tc.`content_id`";
			$ret = $this->getAssoc( $sql, array( $pContentId ) );
		}
		return $ret;
	}

	function loadParentGalleries() {
		if( $this->isValid() ) {
			$this->mInfo['parent_galleries'] = $this->getParentGalleries();
		}
	}

	function updatePosition($pGalleryContentId, $newPosition = NULL) {
		if( $pGalleryContentId && $newPosition && !empty($this->mContentId) ) {
			$sql = "UPDATE `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` SET `position` = ? WHERE `item_content_id` = ? AND `gallery_content_id` = ?";
			$rs = $this->query($sql, array($newPosition, $this->mContentId, $pGalleryContentId));
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
			$selectSql = '';//AS title$g, tfg$g.gallery_id AS gallery_id$g";
			$whereSql = '';
			$bindVars = array();
			foreach( $path as $galleryId ) {
				if( $galleryId ) {
					$p++; $c++;
					$selectSql .= " tc$p.`title` AS `title$p`, tfg$p.`gallery_id` AS `gallery_id$p`,";
					$joinSql .= " `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim$p
						INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc$p ON(tfgim$p.`gallery_content_id`=tc$p.`content_id`)
						INNER JOIN `".BIT_DB_PREFIX."tiki_fisheye_gallery` tfg$p ON(tfg$p.`content_id`=tc$p.`content_id`),";
					$whereSql .= " tfg$p.`gallery_id`=? AND tfgim$p.`item_content_id`=tc$c.`content_id` AND ";
					array_push( $bindVars, $galleryId );
				}
			}
//			$selectSql .= " tc$c.title AS title$c ";//AS title$g, tfg$g.gallery_id AS gallery_id$g";
			$joinSql .= " `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim$c
				INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc$c ON(tfgim$c.`item_content_id`=tc$c.`content_id`) ";
			$whereSql .= " tc$c.`content_id`=?  AND tfgim$c.`gallery_content_id`=tc$p.`content_id` ";
			array_push( $bindVars, $this->mContentId );
			$rs = $this->query( "SELECT ".rtrim( $selectSql, ',')." FROM ".rtrim( $joinSql, ',')." WHERE $whereSql", $bindVars );
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


	function addToGalleries( $pGalleryArray ) {
		if( $this->isValid() ) {
			$inGalleries = $this->GetAssoc( "SELECT `gallery_id`,`gallery_content_id` FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim INNER JOIN `".BIT_DB_PREFIX."tiki_fisheye_gallery` tfg ON (tfgim.`gallery_content_id`=tfg.`content_id`) WHERE `item_content_id` = ?", array( $this->mContentId ) );
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
								$galleries[$galleryId]->addItem( $this->mContentId );
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
					$sql = "DELETE FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` WHERE `gallery_content_id` = ? AND `item_content_id` = ?";
					$rs = $this->query($sql, array( $galleryId, $this->mContentId ) );
				}
			}
		}
	}

	function isInGallery( $pGalleryContentId, $pItemContentId = NULL) {
		if( empty( $pItemContentId ) ) {
			$pItemContentId = $this->mContentId;
		}
		$ret = FALSE;
		if ($this->mGalleryId && $pGalleryContentId) {
			$sql = "SELECT count(`item_content_id`) as `item_count`
					FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map`
					WHERE `gallery_content_id` = ? AND `item_content_id` = ?";
			$rs = $this->query($sql, array($pGalleryContentId, $pItemContentId));
			if ($rs->fields['item_count'] > 0) {
				$ret = TRUE;
			}
		}
		return $ret;
	}

	function isProtected() {
		$ret = FALSE;
		if (!empty($this->mInfo['access_level']) && ($this->mInfo['access_level'] == GATEKEEPER_ACCESS_PROTECTED || $this->mInfo['access_level'] == GATEKEEPER_ACCESS_PROTECTED_HIDDEN)) {
			$ret = TRUE;
		}
		return $ret;
	}


	function validateUserAccess( $pAnswer=NULL ) {
		$ret = FALSE;
		if( isset( $_SESSION['gatekeeper_security'][$this->mInfo['security_id']] ) && ($_SESSION['gatekeeper_security'][$this->mInfo['security_id']] == md5( $this->mInfo['access_answer'] )) ) {
			$ret = TRUE;
		} elseif( strtoupper( trim( $pAnswer ) ) == strtoupper( trim($this->mInfo['access_answer']) ) ) {
			$_SESSION['gatekeeper_security'][$this->mInfo['security_id']] = md5( $this->mInfo['access_answer'] );
			$ret = TRUE;
		}
		return $ret;
	}


    /**
    * Overloaded function that determines if this content can be edited by the current gBitUser
    * @return the fully specified path to file to be included
    */
	function hasUserAccess( $pPermName ) {
		global $gBitUser;
		// assume true for now
		$ret = FALSE;
		if( $this->isValid() && !($ret = $this->isOwner())  && !($ret = $gBitUser->isAdmin()) ) {
			if( !($ret = empty($this->mInfo['security_id'] ) ) ) {
				// order matters here!
				if( $this->mInfo['is_hidden'] == 'y' ) {
					$ret = TRUE;
				}
				if( $this->mInfo['is_private'] == 'y' ) {
					$ret = $this->isOwner();
				}
				if( !empty( $this->mInfo['access_answer'] ) ) {
					$ret = $this->validateUserAccess();
				}
			} else {
				$ret = $gBitUser->hasPermission( $pPermName );
			}
/*
			if( $pPermName == 'bit_p_edit_fisheye' ) {
				if( !($ret = $this->isOwner()) ) {
					global $gBitUser;
					if( !($ret = $gBitUser->isAdmin()) ) {
						if( $this->loadPermissions() ) {
							$userPerms = $this->getUserPermissions( $gBitUser->mUserId );
							$ret = isset( $userPerms[$pPermName]['user_id'] ) && ( $userPerms[$pPermName]['user_id'] == $gBitUser->mUserId );
						}
					}
				}
*/
		}
		return( $ret );
	}


    /**
    * Overloaded function that determines if this content can be edited by the current gBitUser
    * @return the fully specified path to file to be included
    */
	function hasUserPermission( $pPermName, $pFatalIfFalse=FALSE, $pFatalMessage=NULL ) {
		$ret = FALSE;
		if( $pPermName == 'bit_p_edit_fisheye' ) {
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
