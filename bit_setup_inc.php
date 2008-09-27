<?php
global $gBitSystem, $gBitUser, $gBitSmarty, $gBitThemes;

$registerHash = array(
	'package_name' => 'fisheye',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'fisheye' ) && $gBitUser->hasPermission( 'p_fisheye_view' )) {

	// Default Preferences Defines
	define ( 'FISHEYE_DEFAULT_ROWS_PER_PAGE', 5 );
	define ( 'FISHEYE_DEFAULT_COLS_PER_PAGE', 2 );
	define ( 'FISHEYE_DEFAULT_THUMBNAIL_SIZE', 'large' );

	$menuHash = array(
		'package_name'  => FISHEYE_PKG_NAME,
		'index_url'     => FISHEYE_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:fisheye/menu_fisheye.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );

	include_once( FISHEYE_PKG_PATH.'FisheyeGallery.php' );
}
?>
