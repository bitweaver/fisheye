<?php
/**
 * @package fisheye
 * @subpackage functions
 */

$displayHash = array( 'perm_name' => 'p_fisheye_view' );
$gContent->invokeServices( 'content_display_function', $displayHash );

if (!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) {
	$page = $_REQUEST['page'];
} else {
	$page = 0;
}

if( $page > $gContent->getField( 'num_pages' ) ) {
	$page = $gContent->getField( 'num_pages' );
} elseif ($page < 1) {
	$page = 1;
}

$imagesPerPage = $gContent->getField( 'rows_per_page' ) * $gContent->getField( 'cols_per_page', 10 );

switch( $gContent->getLayout() ) {
	case 'auto_flow':
		$gBitThemes->loadCss( FISHEYE_PKG_PATH."css/div_layout.css", TRUE );
		break;
	case 'matteo':
		$gBitThemes->loadCss( FISHEYE_PKG_PATH."gallery_view/matteo/mb_layout.css", TRUE );
		$gBitThemes->loadAjax( 'jquery' );
		$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'/gallery_views/matteo/mbGallery.js', FALSE, 500, FALSE );
		$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'/gallery_views/matteo/mbGalleryBox.js', FALSE, 501, FALSE );
		break;
	case 'galleriffic':
		$imagesPerPage = -1;
		// Need to add options for different styles of layout
		$gBitThemes->loadCss( FISHEYE_PKG_PATH."/gallery_views/galleriffic/css/galleriffic_style_1.css", TRUE );
		$gBitThemes->loadAjax( 'jquery' );
		$gBitThemes->loadJavascript( UTIL_PKG_PATH.'javascript/jquery/plugins/migrate/jquery.migrate.js', FALSE, 500, FALSE );
		$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'/gallery_views/galleriffic/js/jquery.galleriffic.js', FALSE, 500, FALSE );
		$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'/gallery_views/galleriffic/js/jquery.history.js', FALSE, 501, FALSE );
		$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'/gallery_views/galleriffic/js/jquery.opacityrollover.js', FALSE, 502, FALSE );
		$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'/gallery_views/galleriffic/gftop.js', FALSE, 503, FALSE );
		break;
}

$imageOffset = $imagesPerPage * ($page-1);

$gBitSmarty->assignByRef('pageCount', $page);
$gBitSmarty->assignByRef('imagesPerPage', $imagesPerPage);
$gBitSmarty->assignByRef('imageOffset', $imageOffset);
$gBitSmarty->assignByRef('rows_per_page', $gContent->mInfo['rows_per_page']);
$gBitSmarty->assign('cols_per_page', $gContent->getField( 'cols_per_page', 10 ) );

$gContent->loadImages( $page, $imagesPerPage );
$gContent->addHit();

$gBitSystem->setBrowserTitle( $gContent->getTitle().' '.tra('Gallery') );
$gBitSystem->display( $gContent->getRenderTemplate() , NULL, array( 'display_mode' => 'display' ));
