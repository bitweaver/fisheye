<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/gallery_lookup_inc.php,v 1.1.1.1.2.4 2005/08/30 16:30:10 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

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
} elseif( $gContent->isValid() && $parents = $gContent->getParentGalleries() ) {
	$gal = current( $parents );
	$gContent->setGalleryPath( '/'.$gal['gallery_id'] );
}

$gBitSmarty->assign_by_ref('gContent', $gContent);
$gBitSmarty->assign_by_ref('galleryId', $gContent->mGalleryId);

?>
