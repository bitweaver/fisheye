<?php
/**
 * @package fisheye
 */

require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php' );
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeImage.php' );

/**
 * A gallery that is a query, not stored rows: every photo a user owns, by
 * the date it was taken or the date it was uploaded, whichever galleries the
 * photos are in; or the photos a user owns that are in no gallery at all.
 * Nothing is stored for it.
 *
 * It is addressed by a string id: meta-taken, meta-taken-2026,
 * meta-taken-2026-06, meta-taken-2026-06-14, meta-taken-none (photos with no
 * date taken), and the same under meta-uploaded and meta-unfiled.
 *
 * getItemList() returns the same row hashes as a stored gallery, so the item
 * templates and the photo view work on either.
 *
 * @package fisheye
 */
class FisheyeMetaGallery extends FisheyeGallery {
	public $mBy;		// key of getTypes()
	public $mScope;		// '', a year, a year-month, a year-month-day, or 'none'
	public $mUserId;	// owner of the photos
	private $mTimes;	// cache for getTimes()

	/**
	 * The kinds of meta gallery
	 * @return array keyed by type; each has title, column (of liberty_content) and icon, and optionally
	 * where, a further condition on lc
	 */
	public static function getTypes() {
		return array(
			'taken'    => array( 'title' => 'Date taken', 'column' => 'event_time', 'icon' => 'fa-calendar-alt' ),
			'uploaded' => array( 'title' => 'Date uploaded', 'column' => 'created', 'icon' => 'fa-clock' ),
			// photos left without a gallery, for instance when a gallery was deleted by someone who does not own them
			'unfiled'  => array( 'title' => 'Not in a gallery', 'column' => 'created', 'icon' => 'fa-folder-open',
				'where' => "NOT EXISTS ( SELECT 1 FROM `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim WHERE fgim.`item_content_id` = lc.`content_id` )" ),
		);
	}

	/**
	 * Split a meta gallery id into its type and scope
	 * @param string $pId such as meta-taken-2026-06; the meta- prefix is optional
	 * @return array with by and scope, or NULL when the id is not a meta gallery id
	 */
	public static function parseId( $pId ) {
		$ret = NULL;
		if( is_string( $pId ) && preg_match( '/^(?:meta-)?([a-z]+)(?:-(none|\d{4}(?:-\d{2}(?:-\d{2})?)?))?$/', $pId, $match ) ) {
			$types = static::getTypes();
			$scope = isset( $match[2] ) ? $match[2] : '';
			if( isset( $types[$match[1]] ) && ( $scope != 'none' || $match[1] == 'taken' ) && static::scopeRange( $scope ) !== FALSE ) {
				$ret = array( 'by' => $match[1], 'scope' => $scope );
			}
		}
		return $ret;
	}

	/**
	 * @param string $pId a meta gallery id
	 * @param int $pUserId owner of the photos
	 * @return FisheyeMetaGallery or NULL when the id is not one
	 */
	public static function lookupMeta( $pId, $pUserId ) {
		$ret = NULL;
		if( ( $parsed = static::parseId( $pId ) ) && BitBase::verifyId( $pUserId ) ) {
			$ret = new FisheyeMetaGallery( $parsed['by'], $parsed['scope'], $pUserId );
		}
		return $ret;
	}

	function __construct( $pBy = NULL, $pScope = '', $pUserId = NULL ) {
		parent::__construct();
		$this->mBy = $pBy;
		$this->mScope = (string)$pScope;
		$this->mUserId = (int)$pUserId;
		$this->mGalleryId = $this->getMetaId();
		$this->mInfo['user_id'] = $this->mUserId;
		$this->mInfo['title'] = $this->getTitle();
	}

	function isValid() {
		$types = static::getTypes();
		return isset( $types[$this->mBy] ) && $this->mUserId > 0;
	}

	function load( $pContentId = NULL, $pPluginParams = NULL ) {
		return $this->isValid();
	}

	/** A meta gallery shows a user's photos from all their galleries, so only that user and gallery administrators may see it */
	public function canView() {
		global $gBitUser;
		return $this->isValid() && $gBitUser->isRegistered() && ( $gBitUser->mUserId == $this->mUserId || $gBitUser->hasPermission( 'p_fisheye_admin' ) );
	}

	// Nothing is stored, so there is nothing to change, comment on or configure
	function hasUpdatePermission( $pVerifyAccessControl = TRUE ) {
		return FALSE;
	}

	function isCommentable() {
		return FALSE;
	}

	function getPreference( $pPrefName, $pPrefDefault = NULL ) {
		return $pPrefDefault;
	}

	function addHit() {
		return FALSE;
	}

	/** @return string the id that addresses this meta gallery, optionally at another scope */
	public function getMetaId( $pScope = NULL ) {
		$scope = ( $pScope === NULL ) ? $this->mScope : $pScope;
		return 'meta-'.$this->mBy.( $scope !== '' ? '-'.$scope : '' );
	}

	function getTitle() {
		$types = static::getTypes();
		$ret = isset( $types[$this->mBy] ) ? tra( $types[$this->mBy]['title'] ) : '';
		if( $this->mScope == 'none' ) {
			$ret = tra( 'No date taken' );
		} elseif( $this->mScope !== '' ) {
			$ret .= ': '.static::bucketLabel( $this->mScope );
		}
		return $ret;
	}

	/**
	 * @param string $pScope another scope of the same type; NULL is this one
	 * @param string $pGroup heading size to ask for: year, month or day
	 */
	public function getDisplayUrl( $pScope = NULL, $pGroup = NULL ) {
		global $gBitUser;
		$scope = ( $pScope === NULL ) ? $this->mScope : $pScope;
		return fisheye_page_url( 'timeline', array(
			'by'      => $this->mBy,
			'in'      => ( $scope !== '' ) ? $scope : NULL,
			'group'   => $pGroup ? $pGroup : NULL,
			'user_id' => ( $this->mUserId != $gBitUser->mUserId ) ? $this->mUserId : NULL,
		) );
	}

	/** The column of liberty_content the photos are ordered by. Photos with no date taken fall back to the upload date. */
	private function getOrderColumn() {
		$types = static::getTypes();
		return ( $this->mScope == 'none' ) ? 'created' : $types[$this->mBy]['column'];
	}

	/**
	 * Newest first. The "+ 0" keeps the database from walking a site-wide index on the date column and
	 * discarding every other user's rows; one user's photos are few enough to sort.
	 */
	private function getOrderSql() {
		return "lc.`".$this->getOrderColumn()."` + 0 DESC, lc.`content_id` DESC";
	}

	/**
	 * The time range a scope covers, in the server's time zone
	 * @return array of first second and the second after the last; NULL for no range; FALSE when the scope is not a date
	 */
	public static function scopeRange( $pScope ) {
		$ret = NULL;
		if( $pScope !== '' && $pScope != 'none' ) {
			$part = array_map( 'intval', explode( '-', $pScope ) ) + array( 1 => 0, 2 => 0 );
			if( $part[0] < 1970 || $part[0] > 2200 || ( $part[1] && !checkdate( $part[1], $part[2] ? $part[2] : 1, $part[0] ) ) ) {
				$ret = FALSE;
			} elseif( $part[2] ) {
				$ret = array( mktime( 0, 0, 0, $part[1], $part[2], $part[0] ), mktime( 0, 0, 0, $part[1], $part[2] + 1, $part[0] ) );
			} elseif( $part[1] ) {
				$ret = array( mktime( 0, 0, 0, $part[1], 1, $part[0] ), mktime( 0, 0, 0, $part[1] + 1, 1, $part[0] ) );
			} else {
				$ret = array( mktime( 0, 0, 0, 1, 1, $part[0] ), mktime( 0, 0, 0, 1, 1, $part[0] + 1 ) );
			}
		}
		return $ret;
	}

	/** @return string the scope a time falls in at the given heading size: year, month or day */
	public static function bucketKey( $pTime, $pGroup ) {
		return date( ( $pGroup == 'year' ) ? 'Y' : ( ( $pGroup == 'day' ) ? 'Y-m-d' : 'Y-m' ), (int)$pTime );
	}

	/** @return string a scope in words: 2026, June 2026 or 14 June 2026 */
	public static function bucketLabel( $pScope ) {
		$part = explode( '-', $pScope ) + array( 1 => NULL, 2 => NULL );
		$ret = $part[0];
		if( $part[1] ) {
			$ret = tra( date( 'F', mktime( 0, 0, 0, (int)$part[1], 1, 2001 ) ) ).' '.$ret;
			if( $part[2] ) {
				$ret = (int)$part[2].' '.$ret;
			}
		}
		return $ret;
	}

	/** The WHERE fragment and bind variables that select this meta gallery's photos; lc is liberty_content */
	private function getSetSql( &$pBindVars, $pScoped = TRUE ) {
		$types = static::getTypes();
		$column = $types[$this->mBy]['column'];
		$pBindVars = array( $this->mUserId, FISHEYEIMAGE_CONTENT_TYPE_GUID );
		$ret = "lc.`user_id` = ? AND lc.`content_type_guid` = ?";
		if( !empty( $types[$this->mBy]['where'] ) ) {
			$ret .= " AND ".$types[$this->mBy]['where'];
		}
		if( $pScoped && $this->mScope == 'none' ) {
			$ret .= " AND ( lc.`$column` IS NULL OR lc.`$column` <= 0 )";
		} else {
			$ret .= " AND lc.`$column` > 0";
			if( $pScoped && ( $range = static::scopeRange( $this->mScope ) ) ) {
				$ret .= " AND lc.`$column` >= ? AND lc.`$column` < ?";
				$pBindVars[] = $range[0];
				$pBindVars[] = $range[1];
			}
		}
		return $ret;
	}

	protected function getItemListParts( &$pListHash ) {
		// one order only: newest first
		$pListHash['sort'] = 'position';
		$column = $this->getOrderColumn();
		$bindVars = array();
		return array(
			'from'   => "`".BIT_DB_PREFIX."liberty_content` lc",
			'where'  => $this->getSetSql( $bindVars ),
			'bind'   => $bindVars,
			'select' => "",
			'order'  => $this->getOrderSql(),
			'id'     => "lc.`content_id`",
			'plain_set' => TRUE,
		);
	}

	// Counts the photos ahead of the item, which reads far fewer rows than ranking the whole set
	protected function getItemPosition( $pContentId, $pParts, $pFromSql, $pSetSql, $pBindVars ) {
		$ret = 0;
		$column = $this->getOrderColumn();
		$itemVars = $pBindVars;
		$itemVars[] = $pContentId;
		$time = $this->mDb->getOne( "SELECT lc.`$column` $pSetSql AND lc.`content_id` = ?", $itemVars );
		if( $time !== NULL && $time !== FALSE ) {
			array_push( $pBindVars, $time, $time, $pContentId );
			$ret = 1 + (int)$this->mDb->getOne( "SELECT COUNT(*) $pSetSql AND ( lc.`$column` > ? OR ( lc.`$column` = ? AND lc.`content_id` > ? ) )", $pBindVars );
		}
		return $ret;
	}

	protected function decorateItemRow( &$pRow ) {
		$pRow['display_url'] = FisheyeImage::getDisplayUrlFromHash( $pRow );
		// the photo view reads this to keep previous and next inside this meta gallery
		$pRow['display_url'] .= ( strpos( $pRow['display_url'], '?' ) === FALSE ? '?' : '&' ).'meta='.substr( $this->getMetaId(), 5 );
		$pRow['sort_time'] = $pRow[$this->getOrderColumn()];
	}

	protected function markItemFavorites( &$pRows ) {
		global $gBitUser;
		if( $pRows && $gBitUser->isRegistered() ) {
			$contentIds = array_keys( $pRows );
			$query = "SELECT ufm.`favorite_content_id` FROM `".BIT_DB_PREFIX."users_favorites_map` ufm
					WHERE ufm.`user_id` = ? AND ufm.`favorite_content_id` IN ( ".implode( ',', array_fill( 0, count( $contentIds ), '?' ) )." )";
			if( $rs = $this->mDb->query( $query, array_merge( array( $gBitUser->mUserId ), $contentIds ) ) ) {
				while( $row = $rs->fetchRow() ) {
					$pRows[$row['favorite_content_id']]['is_favorite'] = TRUE;
				}
			}
		}
	}

	/**
	 * The scope the photo before a list offset falls in, so a later page knows whether it continues a heading
	 * @param int $pOffset offset of the page
	 * @param string $pGroup year, month or day
	 * @return string scope, or NULL at the start of the list
	 */
	public function getBucketBefore( $pOffset, $pGroup ) {
		$ret = NULL;
		if( $pOffset > 0 && $this->mScope != 'none' ) {
			$column = $this->getOrderColumn();
			$bindVars = array();
			$whereSql = $this->getSetSql( $bindVars );
			$rs = $this->mDb->query( "SELECT lc.`$column` AS `sort_time` FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE $whereSql ORDER BY ".$this->getOrderSql(), $bindVars, 1, $pOffset - 1 );
			if( $rs && ( $row = $rs->fetchRow() ) ) {
				$ret = static::bucketKey( $row['sort_time'], $pGroup );
			}
		}
		return $ret;
	}

	/**
	 * Every dated photo of this type's owner as a count per day, whatever the scope. One query, kept for the request.
	 * Days are counted here and not in SQL so they follow the server's time zone on any database.
	 * @return array of counts keyed by year-month-day, newest first
	 */
	private function getTimes() {
		if( $this->mTimes === NULL ) {
			$this->mTimes = array();
			$types = static::getTypes();
			$column = $types[$this->mBy]['column'];
			$bindVars = array();
			$whereSql = $this->getSetSql( $bindVars, FALSE );
			if( $rs = $this->mDb->query( "SELECT lc.`$column` AS `sort_time` FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE $whereSql", $bindVars ) ) {
				while( $row = $rs->fetchRow() ) {
					$day = date( 'Y-m-d', (int)$row['sort_time'] );
					$this->mTimes[$day] = isset( $this->mTimes[$day] ) ? $this->mTimes[$day] + 1 : 1;
				}
			}
			krsort( $this->mTimes );
		}
		return $this->mTimes;
	}

	/**
	 * Photo counts per year, month or day
	 * @param string $pGroup year, month or day
	 * @param boolean $pScoped FALSE counts the owner's whole timeline, not only this scope
	 * @return array of counts keyed by scope, newest first
	 */
	public function getBuckets( $pGroup, $pScoped = TRUE ) {
		$ret = array();
		$length = ( $pGroup == 'year' ) ? 4 : ( ( $pGroup == 'day' ) ? 10 : 7 );
		$prefix = ( $pScoped && $this->mScope != 'none' ) ? $this->mScope : '';
		foreach( $this->getTimes() as $day => $count ) {
			if( $prefix === '' || strpos( $day, $prefix ) === 0 ) {
				$key = substr( $day, 0, $length );
				$ret[$key] = isset( $ret[$key] ) ? $ret[$key] + $count : $count;
			}
		}
		return $ret;
	}

	/** @return int how many photos this meta gallery holds */
	public function countItems() {
		$bindVars = array();
		$whereSql = $this->getSetSql( $bindVars );
		return (int)$this->mDb->getOne( "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE $whereSql", $bindVars );
	}

	/** @return int how many of the owner's photos have no date taken */
	public function getUndatedCount() {
		return (int)$this->mDb->getOne( "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE lc.`user_id` = ? AND lc.`content_type_guid` = ? AND ( lc.`event_time` IS NULL OR lc.`event_time` <= 0 )", array( $this->mUserId, FISHEYEIMAGE_CONTENT_TYPE_GUID ) );
	}
}
