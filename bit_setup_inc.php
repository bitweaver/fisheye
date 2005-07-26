<?php
global $gBitSystem, $gBitSmarty;

$gBitSystem->registerPackage( 'fisheye', dirname( __FILE__).'/' );

if( $gBitSystem->isPackageActive( 'fisheye' ) ) {
	// Default Preferences Defines
	define ( 'FISHEYE_DEFAULT_ROWS_PER_PAGE', 5 );
	define ( 'FISHEYE_DEFAULT_COLS_PER_PAGE', 2 );
	define ( 'FISHEYE_DEFAULT_THUMBNAIL_SIZE', 'small' );
	
	$gBitSmarty->assign( 'FISHEYE_DEFAULT_ROWS_PER_PAGE', FISHEYE_DEFAULT_ROWS_PER_PAGE );
	$gBitSmarty->assign( 'FISHEYE_DEFAULT_COLS_PER_PAGE', FISHEYE_DEFAULT_COLS_PER_PAGE );
	$gBitSmarty->assign( 'FISHEYE_DEFAULT_THUMBNAIL_SIZE', FISHEYE_DEFAULT_THUMBNAIL_SIZE );

	$gBitSystem->registerAppMenu( 'fisheye', $gBitSystem->getPreference('fisheye_menu_text','Fisheye'), FISHEYE_PKG_URL.'index.php', 'bitpackage:fisheye/menu_fisheye.tpl', 'Image Galleries');
	
	include_once( FISHEYE_PKG_PATH.'FisheyeGallery.php' );
}
?>
