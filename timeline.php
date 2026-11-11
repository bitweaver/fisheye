<?php
/**
 * Timeline: every photo a user owns in date order, by the date taken or the
 * date uploaded, under year, month or day headings. See FisheyeMetaGallery.
 *
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );

$gBitSystem->verifyPackage( 'fisheye' );
require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeMetaGallery.php' );

global $gBitSystem, $gBitSmarty, $gBitThemes, $gBitUser, $gContent;

if( !$gBitUser->isRegistered() ) {
	$gBitSystem->fatalPermission( 'p_fisheye_view' );
}

$feMetaId = ( !empty( $_REQUEST['by'] ) && is_string( $_REQUEST['by'] ) ) ? $_REQUEST['by'] : 'taken';
if( !empty( $_REQUEST['in'] ) && is_string( $_REQUEST['in'] ) ) {
	$feMetaId .= '-'.$_REQUEST['in'];
}
$gContent = FisheyeMetaGallery::lookupMeta( $feMetaId, BitBase::verifyIdParameter( $_REQUEST, 'user_id' ) ? (int)$_REQUEST['user_id'] : $gBitUser->mUserId );
if( !$gContent ) {
	$gBitSystem->fatalError( tra( 'There is no such timeline.' ), 'error.tpl', '', HttpStatusCodes::HTTP_NOT_FOUND );
}
if( !$gContent->canView() ) {
	$gBitSystem->fatalError( tra( 'A timeline can only be seen by the owner of its photos.' ), 'error.tpl', '', HttpStatusCodes::HTTP_FORBIDDEN );
}
$gBitSmarty->assignByRef( 'gContent', $gContent );

// Heading sizes that are finer than the scope; the first is the default
$scopeLength = strlen( $gContent->mScope );
if( $gContent->mScope == 'none' || $scopeLength == 10 ) {
	$feGroups = array();
} elseif( $scopeLength == 7 ) {
	$feGroups = array( 'day' => tra( 'Day' ) );
} elseif( $scopeLength == 4 ) {
	$feGroups = array( 'month' => tra( 'Month' ), 'day' => tra( 'Day' ) );
} else {
	$feGroups = array( 'month' => tra( 'Month' ), 'year' => tra( 'Year' ), 'day' => tra( 'Day' ) );
}
$feGroup = ( !empty( $_REQUEST['group'] ) && is_string( $_REQUEST['group'] ) && isset( $feGroups[$_REQUEST['group']] ) ) ? $_REQUEST['group'] : key( $feGroups );

$listHash = array(
	'page' => ( !empty( $_REQUEST['page'] ) && is_numeric( $_REQUEST['page'] ) ) ? (int)$_REQUEST['page'] : 1,
	// rows are drawn small, so the small thumbnail covers 2x displays
	'thumbnail_size' => 'small',
);
$feItems = $gContent->getItemList( $listHash );

// Group the page under headings. A page that continues the heading the page before ended on does not repeat it.
$feSections = array();
if( $feGroup ) {
	$feCounts = $gContent->getBuckets( $feGroup );
	$lastKey = !empty( $_REQUEST['fragment'] ) ? $gContent->getBucketBefore( $listHash['offset'], $feGroup ) : NULL;
	foreach( $feItems as $contentId => $item ) {
		$key = FisheyeMetaGallery::bucketKey( $item['sort_time'], $feGroup );
		if( $key !== $lastKey || !$feSections ) {
			$feSections[] = array(
				'key' => $key,
				'continued' => ( $key === $lastKey ),
				'title' => FisheyeMetaGallery::bucketLabel( $key ),
				'url' => $gContent->getDisplayUrl( $key ),
				'cant' => isset( $feCounts[$key] ) ? $feCounts[$key] : 0,
				'items' => array(),
			);
			$lastKey = $key;
		}
		$feSections[count( $feSections ) - 1]['items'][$contentId] = $item;
	}
} elseif( $feItems ) {
	$feSections[] = array( 'key' => $gContent->mScope, 'continued' => TRUE, 'items' => $feItems );
}

$feBaseUrl = $gContent->getDisplayUrl( NULL, ( $feGroup && $feGroup != key( $feGroups ) ) ? $feGroup : NULL );
$gBitSmarty->assign( 'feSections', $feSections );
$gBitSmarty->assign( 'feList', $listHash );
$gBitSmarty->assign( 'feBaseUrl', $feBaseUrl );
$gBitSmarty->assign( 'feGroups', $feGroups );
$gBitSmarty->assign( 'feGroup', $feGroup );

if( !empty( $_REQUEST['fragment'] ) ) {
	// next page of photos for the "more" control, without the page chrome
	header( 'Content-Type: text/html; charset=utf-8' );
	print $gBitSmarty->fetch( 'bitpackage:fisheye/timeline_items_inc.tpl' );
	die;
}

// The path from the whole timeline down to this scope
$feCrumbs = array();
if( $gContent->mScope != 'none' ) {
	for( $length = 4; $length < $scopeLength; $length += 3 ) {
		$scope = substr( $gContent->mScope, 0, $length );
		$feCrumbs[$gContent->getDisplayUrl( $scope )] = FisheyeMetaGallery::bucketLabel( $scope );
	}
}
$gBitSmarty->assign( 'feCrumbs', $feCrumbs );
$feTypes = FisheyeMetaGallery::getTypes();
$gBitSmarty->assign( 'feType', $feTypes[$gContent->mBy] );
$gBitSmarty->assign( 'feTypeUrl', $gContent->getDisplayUrl( '' ) );

// Years of the whole timeline for the jump list, and the first and last year for the summary line
$feYears = array();
foreach( $gContent->getBuckets( 'year', FALSE ) as $year => $count ) {
	$feYears[$year] = array( 'url' => $gContent->getDisplayUrl( (string)$year ), 'cant' => $count, 'current' => ( substr( $gContent->mScope, 0, 4 ) == $year ) );
}
$gBitSmarty->assign( 'feYears', $feYears );
$feYearKeys = array_keys( $feYears );
$gBitSmarty->assign( 'feYearSpan', $feYearKeys ? array( 'first' => end( $feYearKeys ), 'last' => reset( $feYearKeys ) ) : NULL );
$gBitSmarty->assign( 'feUndated', ( $gContent->mBy == 'taken' && $gContent->mScope === '' ) ? $gContent->getUndatedCount() : 0 );
$gBitSmarty->assign( 'feUndatedUrl', $gContent->getDisplayUrl( 'none' ) );

// library rail: the owner's galleries, with this timeline marked
$feRail = $gContent->getRailList( $gContent->mUserId );
$gBitSmarty->assign( 'feRail', $feRail );
$gBitSmarty->assign( 'feRailCurrent', 0 );
$gBitSmarty->assign( 'feRailMeta', $gContent->mBy );

$gBitSystem->setRequestConfig( 'layout-body', '-fluid' );
$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'js/fisheye.js', FALSE, 600, FALSE, FALSE );

$gBitSystem->display( 'bitpackage:fisheye/timeline.tpl', $gContent->getTitle(), array( 'display_mode' => 'display' ) );
