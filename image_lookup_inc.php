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
// the image is considered the primary content, however the gallery is useful
if( !empty($_REQUEST['gallery_id']) && is_numeric($_REQUEST['gallery_id']) ) {
	$gGallery = new FisheyeGallery( $_REQUEST['gallery_id'], NULL, FALSE );
	$gGallery->load();
	$gGallery->loadCurrentImage( $gContent->mImageId );
	$gBitSmarty->assign_by_ref('gGallery', $gGallery);
	$gBitSmarty->assign_by_ref('galleryId', $_REQUEST['gallery_id']);
}

// This user does not own this gallery and they have not been granted the permission to edit this gallery
$gContent->verifyViewPermission();

$gBitSmarty->assign_by_ref('gContent', $gContent);
$gBitSmarty->assign_by_ref('imageId', $gContent->mImageId );


?>
