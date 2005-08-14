<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/display_fisheye_image_inc.php,v 1.1.1.1.2.3 2005/08/14 18:45:58 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

if( !$gContent->isValid() ) {
	$gBitSystem->fatalError( "No image exists with the given ID" );
}

$accessPermission = 'bit_p_view_fisheye';
require_once( LIBERTY_PKG_PATH.'access_check_inc.php' );

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
