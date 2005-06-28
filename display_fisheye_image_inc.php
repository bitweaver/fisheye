<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/display_fisheye_image_inc.php,v 1.2 2005/06/28 07:45:42 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

if( !$gContent->isValid() ) {
	$gBitSystem->fatalError( "No image exists with the given ID" );
}

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
	$cat_obj_type = FISHEYEIMAGE_CONTENT_TYPE_GUID;
	$cat_objid = $gContent->mContentId;
	include_once( CATEGORIES_PKG_PATH.'categories_display_inc.php' );
}

// Get the proper thumbnail size to display on this page
reset($gContent->mStorage);
$imageStorage = current($gContent->mStorage);

$thumbSize = (!empty( $_REQUEST['size'] ) ? $_REQUEST['size'] :
				(!empty( $_COOKIE['fisheyeviewsize'] ) ? $_COOKIE['fisheyeviewsize'] :
				$gBitSystem->getPreference('fisheye_image_default_thumbnail_size', FISHEYE_DEFAULT_THUMBNAIL_SIZE)));
$gContent->mInfo['display_url'] = $gContent->getThumbnailUrl( $thumbSize );

$gBitSystem->setBrowserTitle( $gContent->getTitle() );
$gBitSystem->display("bitpackage:fisheye/view_image.tpl");

?>
