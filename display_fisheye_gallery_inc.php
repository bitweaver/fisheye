<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/display_fisheye_gallery_inc.php,v 1.1.1.1.2.3 2005/08/14 18:45:58 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

$accessPermission = 'bit_p_view_fisheye';
require_once( LIBERTY_PKG_PATH.'access_check_inc.php' );

/**
 * categories setup
 */
if( $gBitSystem->isPackageActive( 'categories' ) ) {
	$cat_obj_type = FISHEYEGALLERY_CONTENT_TYPE_GUID;
	$cat_objid =$gContent->mContentId;
	include_once( CATEGORIES_PKG_PATH.'categories_display_inc.php' );
}


if (!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) {
	$page = $_REQUEST['page'];
} else {
	$page = 0;
}

if ($page > $gContent->mInfo['num_pages']) {
	$page = $gContent->mInfo['num_pages'];
} elseif ($page < 1) {
	$page = 1;
}

$imagesPerPage = $gContent->mInfo['rows_per_page'] * $gContent->mInfo['cols_per_page'];
$imageOffset = $imagesPerPage * ($page-1);

$gBitSmarty->assign_by_ref('page', $page);
$gBitSmarty->assign_by_ref('imagesPerPage', $imagesPerPage);
$gBitSmarty->assign_by_ref('imageOffset', $imageOffset);
$gBitSmarty->assign_by_ref('rows_per_page', $gContent->mInfo['rows_per_page']);
$gBitSmarty->assign_by_ref('cols_per_page', $gContent->mInfo['cols_per_page']);

$gContent->loadImages($imageOffset, $imagesPerPage);
$gContent->addHit();

$gBitSystem->setBrowserTitle( $gContent->getTitle().' '.tra('Gallery') );
$gBitSystem->display("bitpackage:fisheye/view_gallery.tpl");

?>
