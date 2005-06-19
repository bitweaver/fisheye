<?php
global $gContent;

if (!empty($_REQUEST['gallery_id']) && is_numeric($_REQUEST['gallery_id'])) {
	$gContent = new FisheyeGallery( $_REQUEST['gallery_id'] );
	if( !$gContent->load() ) {
	}
} elseif (!empty($_REQUEST['content_id']) && is_numeric($_REQUEST['content_id'])) {
	$gContent = new FisheyeGallery( NULL, $_REQUEST['content_id'] );
	if( !$gContent->load() ) {
	}
} else {
	$gContent = new FisheyeGallery();
	$galleryId = NULL;
}

if( !empty( $_REQUEST['gallery_path'] ) ) {
	$gContent->setGalleryPath( $_REQUEST['gallery_path'] );
}

$smarty->assign_by_ref('gContent', $gContent);
$smarty->assign_by_ref('galleryId', $gContent->mGalleryId);

?>
