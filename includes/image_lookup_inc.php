<?php
/**
 * @version $Header$
 * @package fisheye
 * @subpackage functions
 */

global $gContent, $gGallery;


if( $gContent = FisheyeImage::lookup( $_REQUEST ) ) {
	// nothing to do. ::lookup will do a full load
} else {
	$gContent = new FisheyeImage();
	$imageId = NULL;
}

if( !empty( $_REQUEST['gallery_path'] ) ) {
	$_REQUEST['gallery_path'] = rtrim( $_REQUEST['gallery_path'], '/' );
	$gContent->setGalleryPath( $_REQUEST['gallery_path'] );
	$matches = array();
	$tail = strrpos( $_REQUEST['gallery_path'], '/' );
	$_REQUEST['gallery_id'] = substr( $_REQUEST['gallery_path'], $tail + 1 );
}
if( empty( $_REQUEST['gallery_id'] ) ) {
	if( $parents = $gContent->getParentGalleries() ) {
		$gal = current( $parents );
		$gContent->setGalleryPath( '/'.$gal['gallery_id'] );
		$_REQUEST['gallery_id'] = $gal['gallery_id'];
	}
}
// A photo opened from a timeline keeps that timeline as its gallery, so previous and next follow it
$metaGallery = NULL;
if( !empty( $_REQUEST['meta'] ) && $gContent->isValid() ) {
	require_once( FISHEYE_PKG_CLASS_PATH.'FisheyeMetaGallery.php' );
	if( ( $metaGallery = FisheyeMetaGallery::lookupMeta( $_REQUEST['meta'], $gContent->getField( 'user_id' ) ) ) && !$metaGallery->canView() ) {
		$metaGallery = NULL;
	}
}
// the image is considered the primary content, however the gallery is useful
if( $metaGallery ) {
	$gGallery = $metaGallery;
	$gBitSmarty->assignByRef('gGallery', $gGallery);
} elseif( BitBase::verifyIdParameter( $_REQUEST, 'gallery_id' ) ) {
	$gGallery = FisheyeGallery::lookup( $_REQUEST );
	$gBitSmarty->assignByRef('gGallery', $gGallery);
	$gBitSmarty->assignByRef('galleryId', $_REQUEST['gallery_id']);
}

// This user does not own this gallery and they have not been granted the permission to edit this gallery
$gContent->verifyViewPermission();

$gBitSmarty->assignByRef('gContent', $gContent);
$gBitSmarty->assignByRef('imageId', $gContent->mImageId );


?>
