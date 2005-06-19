<?php
global $gContent, $gGallery;

if (!empty($_REQUEST['image_id']) && is_numeric($_REQUEST['image_id'])) {
	$gContent = new FisheyeImage( $_REQUEST['image_id'] );
	$gContent->load();
	//vd($gContent->mInfo['image_file']);
} elseif (!empty($_REQUEST['content_id']) && is_numeric($_REQUEST['content_id'])) {
	$gContent = new FisheyeImage( NULL, $_REQUEST['content_id'] );
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
	$smarty->assign_by_ref('gGallery', $gGallery);
	$smarty->assign_by_ref('galleryId', $_REQUEST['gallery_id']);
}

$smarty->assign_by_ref('gContent', $gContent);
$smarty->assign_by_ref('imageId', $gContent->mImageId );


?>
