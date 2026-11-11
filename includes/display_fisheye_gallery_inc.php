<?php
/**
 * @package fisheye
 * @subpackage functions
 */

$displayHash = array( 'perm_name' => 'p_fisheye_view' );
$gContent->invokeServices( 'content_display_function', $displayHash );

// Library rail requests from the page script: one gallery's nested galleries, or the whole tree
if( !empty( $_REQUEST['rail'] ) ) {
	if( $_REQUEST['rail'] == 'all' ) {
		$feRail = $gContent->getRailList( $gContent->getField( 'user_id' ) );
		$gContent->loadRailChildren( $feRail['galleries'], array(), TRUE );
		$railItems = $feRail['galleries'];
	} else {
		$children = $gContent->getRailChildren( array( $gContent->mGalleryId ) );
		$railItems = !empty( $children[$gContent->mGalleryId] ) ? $children[$gContent->mGalleryId] : array();
	}
	$gBitSmarty->assign( 'railItems', $railItems );
	$gBitSmarty->assign( 'feRailCurrent', BitBase::verifyIdParameter( $_REQUEST, 'current' ) ? (int)$_REQUEST['current'] : 0 );
	print $gBitSmarty->fetch( 'bitpackage:fisheye/library_rail_items_inc.tpl' );
	die;
}

// Display mode: an explicit choice is remembered for the browser session, otherwise the gallery default applies
$feModes = FisheyeGallery::getAllLayouts();
$feMode = $gContent->getLayout();
if( !empty( $_REQUEST['mode'] ) && is_string( $_REQUEST['mode'] ) && isset( $feModes[$_REQUEST['mode']] ) ) {
	$feMode = $_REQUEST['mode'];
	if( !headers_sent() ) {
		setcookie( 'fisheye_mode', $feMode, 0, BIT_ROOT_URL );
	}
} elseif( !empty( $_COOKIE['fisheye_mode'] ) && is_string( $_COOKIE['fisheye_mode'] ) && isset( $feModes[$_COOKIE['fisheye_mode']] ) ) {
	$feMode = $_COOKIE['fisheye_mode'];
}

$listHash = array(
	'page' => ( !empty( $_REQUEST['page'] ) && is_numeric( $_REQUEST['page'] ) ) ? (int)$_REQUEST['page'] : 1,
	'sort' => ( !empty( $_REQUEST['sort'] ) && is_string( $_REQUEST['sort'] ) ) ? $_REQUEST['sort'] : 'position',
	// sized for 2x displays: tiles and rows are drawn at up to half these pixel sizes
	'thumbnail_size' => ( $feMode == FISHEYE_LAYOUT_DETAILS ) ? 'avatar' : 'medium',
);
$feItems = $gContent->getItemList( $listHash );
$gBitSmarty->assign( 'feCanEdit', $gContent->hasUpdatePermission() );
if( $listHash['sort'] != 'position' ) {
	// hand the chosen order on to the photo view, so previous and next follow it
	foreach( $feItems as $contentId => $item ) {
		$feItems[$contentId]['display_url'] = $item['display_url'].( strpos( $item['display_url'], '?' ) === FALSE ? '?' : '&' ).'sort='.urlencode( $listHash['sort'] );
	}
}

// The gallery's address, ending ready for parameters to be appended
$feBaseUrl = fisheye_gallery_url( $gContent->mGalleryId, array( 'gallery_path' => $gContent->getField( 'gallery_path' ) ) );
$feBaseUrl .= ( strpos( $feBaseUrl, '?' ) === FALSE ) ? '?' : '&';

// Showcase and Columns open on the dark stage, the working views on the light ground, unless the viewer has chosen
$feWide = ( $feMode == FISHEYE_LAYOUT_JUSTIFIED || $feMode == FISHEYE_LAYOUT_MASONRY );
$feTheme = FisheyeBase::getViewerTheme();
$gBitSmarty->assign( 'feWide', $feWide );
$gBitSmarty->assign( 'feDark', $feTheme ? ( $feTheme == 'dark' ) : $feWide );
$gBitSmarty->assign( 'feThemeUrls', FisheyeBase::getThemeUrls( $feBaseUrl.'mode='.$feMode.( $listHash['sort'] != 'position' ? '&sort='.urlencode( $listHash['sort'] ) : '' ) ) );

$gBitSmarty->assign( 'feModes', $feModes );
$gBitSmarty->assign( 'feMode', $feMode );
$gBitSmarty->assign( 'feItems', $feItems );
$gBitSmarty->assign( 'feList', $listHash );
$gBitSmarty->assign( 'feBaseUrl', $feBaseUrl );

if( !empty( $_REQUEST['fragment'] ) ) {
	// next page of items for the "more" control, without the page chrome
	header( 'Content-Type: text/html; charset=utf-8' );
	print $gBitSmarty->fetch( 'bitpackage:fisheye/gallery_items_inc.tpl' );
	die;
}

$feSubItems = ( $listHash['page'] == 1 ) ? $gContent->getSubItems() : array();
$gBitSmarty->assign( 'feSubItems', $feSubItems );
// library rail: the owner's galleries as a tree, opened along the path to this gallery and, when short, one level below it
$feRail = $gContent->getRailList( $gContent->getField( 'user_id' ) );
$feCrumbIds = array_keys( $gContent->getBreadcrumbLinks( TRUE ) );
// a gallery with a long list of nested galleries starts closed, so the rail stays short
$feCrumbIds = array_diff( $feCrumbIds, array( $gContent->mGalleryId ) );
if( count( $feSubItems ) <= 40 ) {
	$feCrumbIds[] = $gContent->mGalleryId;
}
$gContent->loadRailChildren( $feRail['galleries'], $feCrumbIds );
$gBitSmarty->assign( 'feRail', $feRail );
$gBitSmarty->assign( 'feRailCurrent', $gContent->mGalleryId );
$gBitSmarty->assign( 'feSortLabels', array(
	'position'      => tra( 'Gallery order' ),
	'taken_desc'    => tra( 'Date taken, newest first' ),
	'taken_asc'     => tra( 'Date taken, oldest first' ),
	'uploaded_desc' => tra( 'Uploaded, newest first' ),
	'uploaded_asc'  => tra( 'Uploaded, oldest first' ),
	'title_asc'     => tra( 'Title, A to Z' ),
	'title_desc'    => tra( 'Title, Z to A' ),
	'size_desc'     => tra( 'File size, largest first' ),
	'size_asc'      => tra( 'File size, smallest first' ),
) );

$gBitSystem->setRequestConfig( 'layout-body', '-fluid' );
$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'js/fisheye.js', FALSE, 600, FALSE, FALSE );

$gContent->addHit();

$gBitSystem->setBrowserTitle( $gContent->getTitle().' '.tra('Gallery') );
$gBitSystem->display( $gContent->getRenderTemplate() , NULL, array( 'display_mode' => 'display' ));
