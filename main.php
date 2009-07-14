<?php
/**
* Gallery2 Remote support for fisheye
*
* @package  fisheye
* @version  $Header: /cvsroot/bitweaver/_bit_fisheye/main.php,v 1.3 2009/07/14 18:48:24 tylerbello Exp $
* @author   spider <spider@steelsun.com>
* @author   tylerbello <tylerbello@gmail.com>
*/

// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Authors: spider <spider@steelsun.com>
// +----------------------------------------------------------------------+

require_once( '../../bit_setup_inc.php' );

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
