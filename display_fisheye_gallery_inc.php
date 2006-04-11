<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/display_fisheye_gallery_inc.php,v 1.5 2006/04/11 13:04:24 squareing Exp $
 * @package fisheye
 * @subpackage functions
 */

$displayHash = array( 'perm_name' => 'p_fisheye_view' );
$gContent->invokeServices( 'content_display_function', $displayHash );

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

$gBitSmarty->assign_by_ref('pageCount', $page);
$gBitSmarty->assign_by_ref('imagesPerPage', $imagesPerPage);
$gBitSmarty->assign_by_ref('imageOffset', $imageOffset);
$gBitSmarty->assign_by_ref('rows_per_page', $gContent->mInfo['rows_per_page']);
$gBitSmarty->assign_by_ref('cols_per_page', $gContent->mInfo['cols_per_page']);

$gContent->loadImages($imageOffset, $imagesPerPage);
$gContent->addHit();

$gBitSystem->setBrowserTitle( $gContent->getTitle().' '.tra('Gallery') );
$gBitSystem->display("bitpackage:fisheye/view_gallery.tpl");

?>
