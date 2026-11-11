<?php
/**
 * @version $Header$
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );

require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php');
global $gBitSystem, $gBitSmarty, $gBitThemes, $gBitUser, $gFisheyeGallery;

$gBitSystem->verifyPermission( 'p_fisheye_list_galleries' );

$gFisheyeGallery = new FisheyeGallery();

/* Get a page of root galleries. A signed-in visitor gets their own library; user_id names another owner, and all=1 lists every owner's galleries. */
$listHash = array(
	'root_only' => TRUE,
	'lead_thumbnail_size' => 'medium',
	'page'      => ( !empty( $_REQUEST['page'] ) && is_numeric( $_REQUEST['page'] ) ) ? (int)$_REQUEST['page'] : 1,
	'sort'      => ( !empty( $_REQUEST['sort'] ) && is_string( $_REQUEST['sort'] ) ) ? $_REQUEST['sort'] : NULL,
	'find'      => ( !empty( $_REQUEST['find'] ) && is_string( $_REQUEST['find'] ) ) ? trim( $_REQUEST['find'] ) : NULL,
);
$feBaseUrl = FISHEYE_PKG_URL.'?';
if( !BitBase::verifyIdParameter( $_REQUEST, 'user_id' ) && empty( $_REQUEST['all'] ) ) {
	if( !$gBitUser->isRegistered() ) {
		// the front page is the visitor's own library, so it needs a visitor
		$gBitSystem->fatalPermission( 'p_fisheye_list_galleries' );
	}
	$_REQUEST['user_id'] = $gBitUser->mUserId;
}
$feOwn = FALSE;
if( BitBase::verifyIdParameter( $_REQUEST, 'user_id' ) ) {
	$listHash['user_id'] = (int)$_REQUEST['user_id'];
	$feOwn = ( $listHash['user_id'] == $gBitUser->mUserId );
	// owners see their empty galleries so they can fill them
	$listHash['show_empty'] = ( $listHash['user_id'] == $gBitUser->mUserId );
	$gBitSmarty->assign( 'gQueryUserId', $listHash['user_id'] );
	if( !$feOwn ) {
		$feBaseUrl .= 'user_id='.$listHash['user_id'].'&';
	}
} elseif( !empty( $_REQUEST['all'] ) ) {
	$feBaseUrl .= 'all=1&';
}
$gBitSmarty->assign( 'feOwn', $feOwn );
// the same library without a search or sort
$gBitSmarty->assign( 'feLibraryUrl', rtrim( $feBaseUrl, '?&' ) );
// what the search and sort forms must send to stay in this library
$gBitSmarty->assign( 'feLibraryFields', $feOwn ? array() : ( !empty( $listHash['user_id'] ) ? array( 'user_id' => $listHash['user_id'] ) : ( !empty( $_REQUEST['all'] ) ? array( 'all' => 1 ) : array() ) ) );

$galleryList = $gFisheyeGallery->getLibraryList( $listHash );
$feBaseUrl .= 'sort='.$listHash['sort'];
if( $listHash['find'] ) {
	$feBaseUrl .= '&find='.urlencode( $listHash['find'] );
}

$gBitSmarty->assign( 'galleryList', $galleryList );
$gBitSmarty->assign( 'feList', $listHash );
$gBitSmarty->assign( 'feBaseUrl', $feBaseUrl );

if( !empty( $_REQUEST['fragment'] ) ) {
	// next page of cards for the "more" control, without the page chrome
	header( 'Content-Type: text/html; charset=utf-8' );
	print $gBitSmarty->fetch( 'bitpackage:fisheye/library_items_inc.tpl' );
	die;
}

$listRequest = $_REQUEST;
$gFisheyeGallery->invokeServices( 'content_list_function', $listRequest );

$gBitSmarty->assign( 'feSortLabels', array(
	'created_desc'  => tra( 'Newest first' ),
	'created_asc'   => tra( 'Oldest first' ),
	'modified_desc' => tra( 'Recently changed' ),
	'title_asc'     => tra( 'Title, A to Z' ),
	'title_desc'    => tra( 'Title, Z to A' ),
) );

$gBitSystem->setRequestConfig( 'layout-body', '-fluid' );
$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'js/fisheye.js', FALSE, 600, FALSE, FALSE );

// Display the template
$gDefaultCenter = "bitpackage:fisheye/library.tpl";
$gBitSmarty->assignByRef( 'gDefaultCenter', $gDefaultCenter );
$gBitSystem->display( 'bitpackage:kernel/dynamic.tpl', tra( 'Galleries' ), array( 'display_mode' => 'list' ));
