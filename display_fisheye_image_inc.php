<?php
/**
 * @package fisheye
 * @subpackage functions
 */

if( !$gContent->isValid() ) {
	$gBitSystem->setHttpStatus( 404 );
	$gBitSystem->fatalError( tra( "No image exists with the given ID" ) ,'error.tpl', '' );
}

$displayHash = array( 'perm_name' => 'p_fisheye_view' );
$gContent->invokeServices( 'content_display_function', $displayHash );

// Get the proper thumbnail size to display on this page
if( empty( $_REQUEST['size'] )) {
	$_REQUEST['size'] = $gBitSystem->getConfig( 'fisheye_image_default_thumbnail_size', FISHEYE_DEFAULT_THUMBNAIL_SIZE );
}

$gBitSystem->setBrowserTitle( $gContent->getTitle() );
if( $gBitThemes->isAjaxRequest() ) {
	$gBitSmarty->display( $gContent->getRenderTemplate() );
} else {
	$gBitSystem->display( $gContent->getRenderTemplate() , NULL, array( 'display_mode' => 'display' ));
}

