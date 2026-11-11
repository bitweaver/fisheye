<?php
global $gBitSystem, $gBitUser, $gBitSmarty, $gBitThemes;

$registerHash = array(
	'package_name' => 'fisheye',
	'package_path' => dirname( dirname( __FILE__ ) ).'/',
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
		'index_url'     => FISHEYE_PKG_URL,
		'menu_template' => 'bitpackage:fisheye/menu_fisheye.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );

	/**
	 * Address of a page of this package. With pretty URLs the script extension is left off; .htaccess adds it back.
	 * @param string $pPage script name without its extension, such as upload
	 * @param array $pParams query parameters; NULL values are left out
	 */
	function fisheye_page_url( $pPage, $pParams = array() ) {
		global $gBitSystem;
		$query = http_build_query( $pParams, '', '&' );
		return FISHEYE_PKG_URL.$pPage.( $gBitSystem->isFeatureActive( 'pretty_urls' ) ? '' : '.php' ).( $query !== '' ? '?'.$query : '' );
	}

	/**
	 * Address of a gallery page, with any parameters after it.
	 * @param int $pGalleryId an empty id gives the prefix a script appends an id to
	 * @param array $pParams query parameters; gallery_path becomes part of a pretty address
	 */
	function fisheye_gallery_url( $pGalleryId, $pParams = array() ) {
		global $gBitSystem;
		if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
			$path = '';
			if( !empty( $pParams['gallery_path'] ) && preg_match( '#^(/\d+)+$#', $pParams['gallery_path'] ) ) {
				$path = $pParams['gallery_path'];
			}
			unset( $pParams['gallery_path'] );
			$query = http_build_query( $pParams, '', '&' );
			$ret = FISHEYE_PKG_URL.'gallery'.$path.'/'.$pGalleryId.( $query !== '' ? '?'.$query : '' );
		} else {
			$query = http_build_query( $pParams, '', '&' );
			$ret = FISHEYE_PKG_URL.'view.php?gallery_id='.$pGalleryId.( $query !== '' ? '&'.$query : '' );
		}
		return $ret;
	}

	/**
	 * Template function {feurl}: {feurl page="upload" gallery_id=$id} or {feurl gallery=$id mode="organize"}.
	 * The result is escaped for an attribute. With assign="name" the plain address goes to that template variable instead.
	 */
	function fisheye_smarty_url( $pParams, $pSmarty = NULL ) {
		$assign = !empty( $pParams['assign'] ) ? $pParams['assign'] : NULL;
		unset( $pParams['assign'] );
		if( array_key_exists( 'gallery', $pParams ) ) {
			$galleryId = $pParams['gallery'];
			unset( $pParams['gallery'] );
			$ret = fisheye_gallery_url( $galleryId, $pParams );
		} else {
			$page = !empty( $pParams['page'] ) ? $pParams['page'] : 'index';
			unset( $pParams['page'] );
			$ret = fisheye_page_url( $page, $pParams );
		}
		if( $assign && $pSmarty ) {
			$pSmarty->assign( $assign, $ret );
			return '';
		}
		return htmlspecialchars( $ret, ENT_QUOTES, 'UTF-8' );
	}
	try { $gBitSmarty->registerPlugin( 'function', 'feurl', 'fisheye_smarty_url' ); } catch( \SmartyException $e ) {}

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

	include_once( FISHEYE_PKG_CLASS_PATH.'FisheyeGallery.php' );
}
?>
