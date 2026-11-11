<?php
/**
 * @package fisheye
 * @subpackage functions
 */

if( !$gContent->isValid() ) {
	$gBitSystem->fatalError( tra( "No image exists with the given ID" ) ,'error.tpl', '', HttpStatusCodes::HTTP_GONE );
}

$displayHash = array( 'perm_name' => 'p_fisheye_view' );
$gContent->invokeServices( 'content_display_function', $displayHash );

// Get the proper thumbnail size to display on this page
if( empty( $_REQUEST['size'] )) {
	$_REQUEST['size'] = $gBitSystem->getConfig( 'fisheye_image_default_thumbnail_size', FISHEYE_DEFAULT_THUMBNAIL_SIZE );
}

// Position in the gallery, the neighbours for the film strip, and where previous and next lead
$feView = array( 'page_url' => $gContent->getDisplayUrl(), 'items' => array(), 'position' => 0, 'cant' => 0, 'prev_url' => NULL, 'next_url' => NULL, 'gallery_url' => NULL, 'gallery_title' => NULL );
if( is_object( $gGallery ) && $gGallery->isValid() ) {
	$listHash = array(
		'around_content_id' => $gContent->mContentId,
		'span' => 8,
		'thumbnail_size' => 'avatar',
		'sort' => ( !empty( $_REQUEST['sort'] ) && is_string( $_REQUEST['sort'] ) ) ? $_REQUEST['sort'] : 'position',
	);
	$feItems = $gGallery->getItemList( $listHash );
	// a sorted gallery view hands its order on, so previous and next follow what the visitor was looking at
	$sorted = ( $listHash['sort'] != 'position' );
	$previous = NULL;
	$found = FALSE;
	foreach( $feItems as $contentId => $item ) {
		if( $sorted ) {
			$feItems[$contentId]['display_url'] = $item['display_url'].( strpos( $item['display_url'], '?' ) === FALSE ? '?' : '&' ).'sort='.urlencode( $listHash['sort'] );
		}
		if( $found && !$feView['next_url'] ) {
			$feView['next_url'] = $feItems[$contentId]['display_url'];
		}
		if( $contentId == $gContent->mContentId ) {
			$found = TRUE;
			$feView['prev_url'] = $previous;
		}
		$previous = $feItems[$contentId]['display_url'];
	}
	$feView['items'] = $feItems;
	if( !empty( $feItems[$gContent->mContentId] ) ) {
		$feView['page_url'] = $feItems[$gContent->mContentId]['display_url'];
	}
	$feView['position'] = !empty( $listHash['position'] ) ? $listHash['position'] : 0;
	$feView['cant'] = $listHash['cant'];
	$feView['gallery_title'] = $gGallery->getTitle();
	$feView['gallery_url'] = $sorted ? FISHEYE_PKG_URL.'view.php?gallery_id='.$gGallery->mGalleryId.'&sort='.urlencode( $listHash['sort'] ) : $gGallery->getDisplayUrl();
}
// the photo is drawn from the largest thumbnail; the file links list what exists
$feView['image_url'] = NULL;
$feView['sizes'] = array();
foreach( array( 'small', 'medium', 'large' ) as $size ) {
	if( $url = $gContent->getThumbnailUrl( $size ) ) {
		$feView['sizes'][$size] = $url;
		$feView['image_url'] = $url;
	}
}
$gContent->loadParentGalleries();
$gBitSmarty->assign( 'feView', $feView );
// the photo view is dark unless the viewer has chosen light
$gBitSmarty->assign( 'feDark', FisheyeBase::getViewerTheme() != 'light' );
$gBitSmarty->assign( 'feThemeUrls', FisheyeBase::getThemeUrls( $feView['page_url'] ) );

$gBitSystem->setBrowserTitle( $gContent->getTitle() );
if( !empty( $_REQUEST['fragment'] ) || $gBitThemes->isAjaxRequest() ) {
	// the view alone, for the lightbox on gallery pages
	$gBitSmarty->assign( 'feFragment', TRUE );
	print $gBitSmarty->fetch( $gContent->getRenderTemplate() );
	die;
}

$gBitSystem->setRequestConfig( 'layout-body', '-fluid' );
$gBitThemes->loadCss( FISHEYE_PKG_PATH.'css/fisheye.css', TRUE, 300, TRUE, FALSE, FALSE );
$gBitThemes->loadJavascript( FISHEYE_PKG_PATH.'js/fisheye.js', FALSE, 600, FALSE, FALSE );
$gBitSystem->display( $gContent->getRenderTemplate() , NULL, array( 'display_mode' => 'display' ));
