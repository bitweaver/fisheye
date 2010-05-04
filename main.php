<?php
/**
* Gallery2 Remote support for fisheye
*
* @package  fisheye
* @version  $Header: /cvsroot/bitweaver/_bit_fisheye/main.php,v 1.11 2010/05/04 01:04:32 spiderr Exp $
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

require_once( '../kernel/setup_inc.php' );

//Point of access for FisheyeRemote requests
require_once( 'FisheyeRemote.php' );
require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php' );

$gFisheyeRemote = new FisheyeRemote();

// Fisheye allows directories to below to multiple parents - not in gallery. This confuses some clients
// We pad with a random number for uniqueness
foreach( array( 'g2_itemId', 'set_albumName' ) as $key ) {
	if( !empty( $_POST['g2_form'][$key] ) && $_POST['g2_form'][$key] > 1 ) {
		$_POST['g2_form'][$key] = substr( $_POST['g2_form'][$key], 0, (strlen( $_POST['g2_form'][$key] ) - 2) );
	}
	if( !empty( $_GET['g2_form'][$key] ) && $_GET['g2_form'][$key] > 1 ) {
		$_GET['g2_form'][$key] = substr( $_GET['g2_form'][$key], 0, (strlen( $_GET['g2_form'][$key] ) - 2) );
	}
	if( !empty( $_REQUEST[$key] ) && $_REQUEST[$key] > 1 ) {
		$_REQUEST[$key] = substr( $_REQUEST[$key], 0, (strlen( $_REQUEST[$key] ) - 2) );
	}
}

if( !empty( $_REQUEST['g2_form'] ) ){
	$gFisheyeRemote->processRequest( (!empty( $_GET['g2_form'] ) ? $_GET['g2_form'] : array()), (!empty( $_POST['g2_form'] ) ? $_POST['g2_form'] : array()) );
} elseif( !empty( $_REQUEST['g2_itemId'] ) ) {
	//If we don't have g2_form, they must be asking the gallery to be opened upon export completion
	$gallery = new FisheyeGallery();
	$gallery = $gallery->lookup(array('content_id' => $_REQUEST['g2_itemId'] ));
	bit_redirect( $gallery->getDisplayUrl() );
}


?>
