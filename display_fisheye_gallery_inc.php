<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/display_fisheye_gallery_inc.php,v 1.11 2009/05/05 06:28:35 lsces Exp $
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

$imagesPerPage = $gContent->getField( 'rows_per_page' ) * $gContent->getField( 'cols_per_page' );
$imageOffset = $imagesPerPage * ($page-1);

$gBitSmarty->assign_by_ref('pageCount', $page);
$gBitSmarty->assign_by_ref('imagesPerPage', $imagesPerPage);
$gBitSmarty->assign_by_ref('imageOffset', $imageOffset);
$gBitSmarty->assign_by_ref('rows_per_page', $gContent->mInfo['rows_per_page']);
$gBitSmarty->assign_by_ref('cols_per_page', $gContent->mInfo['cols_per_page']);

$gContent->loadImages( $page );
$gContent->addHit();

if( $pagination = $gContent->getPreference( 'gallery_pagination' ) ) {
	if ( $pagination == 'auto_flow' ) {
		$gBitThemes->loadCss( FISHEYE_PKG_PATH."div_layout.css", TRUE );
	}
	else if ( $pagination == 'ajax_scroller' ) {
		$gBitThemes->loadCss( FISHEYE_PKG_PATH."mb_layout.css", TRUE );
		$gBitThemes->loadAjax( 'jquery' );
		$gBitThemes->loadJavascript( UTIL_PKG_PATH.'/javascript/libs/jquery/plugins/mbgallery/mbGallery.js', FALSE, 500, FALSE );
		$gBitThemes->loadJavascript( UTIL_PKG_PATH.'/javascript/libs/jquery/plugins/mbgallery/mbGalleryBox.js', FALSE, 501, FALSE );
	}
}
$gBitSystem->setBrowserTitle( $gContent->getTitle().' '.tra('Gallery') );
$gBitSystem->display( $gContent->getRenderTemplate() , NULL, array( 'display_mode' => 'display' ));

?>
