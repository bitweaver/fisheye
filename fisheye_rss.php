<?php
/**
 * @version $Header$
 * @package fisheye
 * @subpackage functions
 */

/**
 * Initialization
 */
require_once( "../kernel/setup_inc.php" );

$gBitSystem->verifyPackage( 'fisheye' );
$gBitSystem->verifyPackage( 'rss' );
$gBitSystem->verifyFeature( 'fisheye_rss' );

require_once( FISHEYE_PKG_PATH."FisheyeImage.php" );
require_once( RSS_PKG_INCLUDE_PATH.'rss_inc.php' );

$rss->title = $gBitSystem->getConfig( 'fisheye_rss_title', $gBitSystem->getConfig( 'site_title' ).' - '.tra( 'Image Galleries' ) );
$rss->description = $gBitSystem->getConfig( 'fisheye_rss_description', $gBitSystem->getConfig( 'site_title' ).' - '.tra( 'RSS Feed' ) );

// check permission to view fisheye images
if( !$gBitUser->hasPermission( 'p_fisheye_view' ) ) {
	require_once( RSS_PKG_PATH."rss_error.php" );
} else {
	$listHash = array(
		'max_records' => $gBitSystem->getConfig( 'fisheye_rss_max_records', 10 ),
		'sort_mode'   => 'last_modified_desc',
		'gallery_id'  => !empty( $_REQUEST['gallery_id'] ) ? $_REQUEST['gallery_id'] : NULL,
		'user_id'     => !empty( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL,
	);

	// check if we want to use the cache file
	$cacheFile = TEMP_PKG_PATH.RSS_PKG_NAME.'/'.FISHEYE_PKG_NAME.'/'."g{$listHash['gallery_id']}u{$listHash['user_id']}".$cacheFileTail;
	$rss->useCached( $rss_version_name, $cacheFile, $gBitSystem->getConfig( 'rssfeed_cache_time' ));

	// if we have a gallery we can work with - load it
	if( @BitBase::verifyId( $_REQUEST['gallery_id'] ) ) {
		$gallery = new FisheyeGallery( $_REQUEST['gallery_id'] );
		$gallery->load();
		$rss->title .= " - {$gallery->getTitle()}";
	}

	$fisheye = new FisheyeImage();
	$feeds = $fisheye->getList( $listHash );

	// set the rss link
	$rss->link = 'http://'.$_SERVER['HTTP_HOST'].FISHEYE_PKG_URL;

	global $gBitSystem;
	// get all the data ready for the feed creator
	foreach( $feeds as $feed ) {
		$item               = new FeedItem();
		$item->title        = $feed['title'];
		$item->link         = $feed['display_url'];
		$item->description  = '<a href="'.$feed['display_url'].'"><img src="'.$feed['thumbnail_url'].'" /></a>';
		$item->description .= '<p>'.$feed['data'].'</p>';

		$item->date         = ( int )$feed['last_modified'];
		$item->source       = 'http://'.$_SERVER['HTTP_HOST'].BIT_ROOT_URL;
		$item->author       = $gBitUser->getDisplayName( FALSE, $feed );

		$item->descriptionTruncSize = $gBitSystem->getConfig( 'rssfeed_truncate', 5000 );
		$item->descriptionHtmlSyndicated = FALSE;

		// pass the item on to the rss feed creator
		$rss->addItem( $item );
	}

	// finally we are ready to serve the data
	echo $rss->saveFeed( $rss_version_name, $cacheFile );
}
?>
