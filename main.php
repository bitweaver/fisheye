<?php
/**
* Gallery2 Remote support for fisheye
*
* @package  fisheye
* @version  $Header: /cvsroot/bitweaver/_bit_fisheye/main.php,v 1.8 2010/02/09 03:32:04 spiderr Exp $
* @author   spider <spider@steelsun.com>
* @author   tylerbello <tylerbello@gmail.com>
*/

// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See below for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Authors: spider <spider@steelsun.com>
// +----------------------------------------------------------------------+

chdir( dirname( __FILE__ ) );

if( empty( $_REQUEST['g2_authToken'] ) ) {
	// No Auth token, nuke all cookies in case this was a previous login
	foreach( array_keys( $_COOKIE ) as $key ) {
		unset( $_COOKIE[$key] );
	}
}

require_once( '../../kernel/setup_inc.php' );

//Point of access for FisheyeRemote requests
require_once( 'FisheyeRemote.php' );
require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php' );

$gFisheyeRemote = new FisheyeRemote();

if( !empty( $_REQUEST['g2_form'] ) ){
	$gFisheyeRemote->processRequest( (!empty( $_GET['g2_form'] ) ? $_GET['g2_form'] : array()), (!empty( $_POST['g2_form'] ) ? $_POST['g2_form'] : array()) );
} elseif( !empty( $_REQUEST['g2_itemId'] ) ) {
	//If we don't have g2_form, they must be asking the gallery to be opened upon export completion
	$gallery = new FisheyeGallery();
	$gallery = $gallery->lookup(array('content_id' => $_REQUEST['g2_itemId'] ));
	bit_redirect( $gallery->getDisplayUrl() );
}


?>
