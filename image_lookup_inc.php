<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/image_lookup_inc.php,v 1.5 2006/11/16 17:39:46 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

global $gContent, $gGallery;


if( $gContent = FisheyeImage::lookup( $_REQUEST ) ) {
	$gContent->load();
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
	$gGallery->load( $gContent->mImageId );
	$gBitSmarty->assign_by_ref('gGallery', $gGallery);
	$gBitSmarty->assign_by_ref('galleryId', $_REQUEST['gallery_id']);
}

if( $gContent->isProtected() && !$gContent->hasEditPermission() ) {
	// This user does not own this gallery and they have not been granted the permission to edit this gallery
	$gBitSystem->fatalError( tra( "You cannot view this image" ) );
}

$gBitSmarty->assign_by_ref('gContent', $gContent);
$gBitSmarty->assign_by_ref('imageId', $gContent->mImageId );


?>
