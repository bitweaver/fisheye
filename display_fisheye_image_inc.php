<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/display_fisheye_image_inc.php,v 1.7 2006/11/08 08:02:53 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

if( !$gContent->isValid() ) {
	$gBitSystem->fatalError( "No image exists with the given ID" );
}

$displayHash = array( 'perm_name' => 'p_fisheye_view' );
$gContent->invokeServices( 'content_display_function', $displayHash );

// Get the proper thumbnail size to display on this page
reset($gContent->mStorage);
$imageStorage = current($gContent->mStorage);

$thumbSize = (!empty( $_REQUEST['size'] ) ? $_REQUEST['size'] :
				(!empty( $_COOKIE['fisheyeviewsize'] ) ? $_COOKIE['fisheyeviewsize'] :
				$gBitSystem->getConfig('fisheye_image_default_thumbnail_size', FISHEYE_DEFAULT_THUMBNAIL_SIZE)));
$gContent->mInfo['display_url'] = $gContent->getThumbnailUrl( $thumbSize );

$gBitSystem->setBrowserTitle( $gContent->getTitle() );
$gBitSystem->display( $gContent->getRenderTemplate() );

?>
