<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/display_fisheye_gallery_inc.php,v 1.1.1.1.2.1 2005/06/27 10:55:45 lsces Exp $
 * @package fisheye
 * @subpackage functions
 */

if( !$gContent->hasUserAccess( 'bit_p_view_fisheye' ) ) {
	if ( !empty($_REQUEST['submit_answer'])) {	// User is attempting to authenticate themseleves to view this gallery
		if( !$gContent->validateUserAccess( $_REQUEST['try_access_answer']) ) {
			$smarty->assign("failedLogin", "Incorrect Answer");
			$gBitSystem->display("bitpackage:fisheye/authenticate.tpl", "Password Required to view: ".$gContent->getTitle() );
			die;
		}
	} else {
		if( !empty( $gContent->mInfo['access_answer'] ) ) {
			$gBitSystem->display("bitpackage:fisheye/authenticate.tpl", "Password Required to view: ".$gContent->getTitle() );
			die;
		}
		$gBitSystem->fatalError( tra( "You cannot view this image gallery" ) );
	}
}

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

$smarty->assign_by_ref('page', $page);
$smarty->assign_by_ref('imagesPerPage', $imagesPerPage);
$smarty->assign_by_ref('imageOffset', $imageOffset);
$smarty->assign_by_ref('rows_per_page', $gContent->mInfo['rows_per_page']);
$smarty->assign_by_ref('cols_per_page', $gContent->mInfo['cols_per_page']);

$gContent->loadImages($imageOffset, $imagesPerPage);
$gContent->addHit();

$gBitSystem->setBrowserTitle( $gContent->getTitle().' '.tra('Gallery') );
$gBitSystem->display("bitpackage:fisheye/view_gallery.tpl");

?>
