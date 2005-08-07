<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/FisheyeBase.php,v 1.3.2.14 2005/08/07 16:23:22 lsces Exp $
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
		if( $pGalleryContentId && $newPosition && !empty($this->mContentId) ) {
			$sql = "UPDATE `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` SET `position` = ? WHERE `item_content_id` = ? AND `gallery_content_id` = ?";
			$rs = $this->mDb->query($sql, array($newPosition, $this->mContentId, $pGalleryContentId));
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
			$inGalleries = $this->mDb->getAssoc( "SELECT `gallery_id`,`gallery_content_id` FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim INNER JOIN `".BIT_DB_PREFIX."tiki_fisheye_gallery` tfg ON (tfgim.`gallery_content_id`=tfg.`content_id`) WHERE `item_content_id` = ?", array( $this->mContentId ) );
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
					$sql = "DELETE FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` WHERE `gallery_content_id` = ? AND `item_content_id` = ?";
					$rs = $this->mDb->query($sql, array( $galleryId, $this->mContentId ) );
				}
			}
		}
	}

	function isInGallery( $pGalleryContentId, $pItemContentId = NULL) {
		if( empty( $pItemContentId ) ) {
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
						  FROM connectby('`".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map`', '`gallery_content_id`', '`item_content_id`', ?, 0, '/') AS t(`cb_gallery_content_id` int,`cb_item_content_id` int, `level` int, `branch` text)
						  WHERE `cb_gallery_content_id`=?
						  ORDER BY branch
						";
				if ( $this->GetOne($query, array(  $pItemContentId, $pGalleryContentId ) ) ) {
					$ret = TRUE;
				}
			} else {
				$sql = "SELECT count(`item_content_id`) as `item_count`
						FROM `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map`
						WHERE `gallery_content_id` = ? AND `item_content_id` = ?";
				$rs = $this->mDb->query($sql, array($pGalleryContentId, $pItemContentId));
				if ($rs->fields['item_count'] > 0) {
					$ret = TRUE;
				}
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


	function validateUserAccess( $pAnswer=NULL, $pInfo=NULL ) {
		if( empty( $pInfo ) ) {
			$pInfo = $this->mInfo;
		}

		$ret = FALSE;
		if( isset( $_SESSION['gatekeeper_security'][$pInfo['security_id']] ) && ($_SESSION['gatekeeper_security'][$pInfo['security_id']] == md5( $pInfo['access_answer'] )) ) {
			$ret = TRUE;
		} elseif( strtoupper( trim( $pAnswer ) ) == strtoupper( trim($pInfo['access_answer']) ) ) {
			$_SESSION['gatekeeper_security'][$pInfo['security_id']] = md5( $pInfo['access_answer'] );
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
			if( $this->mDb->isAdvancedPostgresEnabled() ) {
				global $gBitDb, $gBitSmarty;
				// This code makes use of the badass /usr/share/pgsql/contrib/tablefunc.sql
				// contribution that you have to install like: psql foo < /usr/share/pgsql/contrib/tablefunc.sql

				// This code pulls all branches for the current node and determines if there is a path from this content to the root
				// without hitting a security_id. If there is clear path it returns TRUE. If there is a security_id, then
				// it determines if the current user has permission
				$query = "SELECT branch,level,cb_item_content_id,cb_gallery_content_id,ts.*
						  FROM connectby('`".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map`', '`gallery_content_id`', '`item_content_id`', ?, 0, '/') AS t(`cb_gallery_content_id` int,`cb_item_content_id` int, `level` int, `branch` text)
						  	LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcsm ON (`cb_gallery_content_id`=tcsm.`content_id`)
						  	LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON (ts.`security_id`=tcsm.`security_id`)
						  ORDER BY branch
						";
$gBitDb->setFatalActive( FALSE );
				$tree = $this->mDb->getAssoc( $query, array( $this->mContentId ) );
$gBitDb->setFatalActive( TRUE );
				if( $tree ) {
					// we will assume true here since the prevention cases can repeatedly flag FALSE
					$ret = TRUE;
					$lastLevel = -1;
					foreach( $tree AS $branch => $node ) {
						if( $node['level'] <= $lastLevel ) {
							// we have moved followed a branch to the end and there is no security!
							$ret = TRUE;
							break;
						}
						if( $node['security_id'] ) {
							$ret = FALSE;
							if( $node['is_hidden'] ) {
								$ret = TRUE;
							}
							if( $node['is_private'] ) {
								$ret = $this->isOwner();
							}
							if( !empty( $sec['access_answer'] ) ) {
								if( !($ret = $this->validateUserAccess( NULL, $sec )) && empty( $this->mInfo['access_question'] ) ) {
									$this->mInfo = array_merge( $this->mInfo, $sec );
								}
							}
						}
						$lastLevel = $node['level'];
					}

				} elseif( !empty( $gBitDb->mDb->_errorMsg ) ) {
					if( $gBitUser->isOwner() ) {
						$gBitSmarty->assign( 'feedback', array( 'warning' => $gBitDb->mDb->_errorMsg.'<br/>'.tra( 'Please check the galleries to which this item belongs' ) ) );
					}
					$ret = TRUE;
				} else {
					$ret = $gBitUser->hasPermission( $pPermName );
				}
			} else {

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
		}
$this->debug(0);
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
