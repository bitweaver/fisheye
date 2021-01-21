<?php
global $gBitSystem, $gBitUser, $gBitSmarty, $gBitThemes;

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
	define ( 'FISHEYE_DEFAULT_THUMBNAIL_SIZE', 'large' );

	$menuHash = array(
		'package_name'  => FISHEYE_PKG_NAME,
		'index_url'     => FISHEYE_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:fisheye/menu_fisheye.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );

	define( 'LIBERTY_SERVICE_PHOTOSHARING', 'photosharing');

	$gLibertySystem->registerService( LIBERTY_SERVICE_PHOTOSHARING, FISHEYE_PKG_NAME, array(
		'users_expunge_function' => 'fisheye_expunge_user',
	) );

	function fisheye_expunge_user( $pObject ) {
		global $gBitDb;
		if( !empty( $pObject->mUserId ) ) {
			$query = "SELECT fg.`content_id` FROM `".BIT_DB_PREFIX."fisheye_gallery` fg INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(fg.`content_id`=lc.`content_id`) WHERE lc.`user_id`=?";
			if( $galleries = $gBitDb->getCol( $query, array( $pObject->mUserId ) ) ) {
				foreach( $galleries as $contentId ) {
					$delGallery = new FisheyeGallery( NULL, $contentId );
					if( $delGallery->load() ) {
						$delGallery->expunge( TRUE );
					}
				}
			}
		}
	}

	include_once( FISHEYE_PKG_PATH.'FisheyeGallery.php' );
}
?>
