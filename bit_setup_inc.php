<?php
global $gBitSystem, $gBitSmarty;

$registerHash = array(
	'package_name' => 'fisheye',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'fisheye' ) ) {
	// Default Preferences Defines
	define ( 'FISHEYE_DEFAULT_ROWS_PER_PAGE', 5 );
	define ( 'FISHEYE_DEFAULT_COLS_PER_PAGE', 2 );
	define ( 'FISHEYE_DEFAULT_THUMBNAIL_SIZE', 'small' );

	$gBitSmarty->assign( 'FISHEYE_DEFAULT_ROWS_PER_PAGE', FISHEYE_DEFAULT_ROWS_PER_PAGE );
	$gBitSmarty->assign( 'FISHEYE_DEFAULT_COLS_PER_PAGE', FISHEYE_DEFAULT_COLS_PER_PAGE );
	$gBitSmarty->assign( 'FISHEYE_DEFAULT_THUMBNAIL_SIZE', FISHEYE_DEFAULT_THUMBNAIL_SIZE );

	$gBitSystem->registerAppMenu( FISHEYE_PKG_NAME, $gBitSystem->getConfig('fisheye_menu_text', ucfirst( FISHEYE_PKG_DIR ) ), FISHEYE_PKG_URL.'index.php', 'bitpackage:fisheye/menu_fisheye.tpl', 'Image Galleries');

	include_once( FISHEYE_PKG_PATH.'FisheyeGallery.php' );
}
?>
