<?php
/**
* Gallery2 Remote support for fisheye
*
* @package  fisheye
* @version  $Header: /cvsroot/bitweaver/_bit_fisheye/main.php,v 1.1 2009/07/12 12:46:00 spiderr Exp $
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
	$gFisheyeRemote->processRequest($_REQUEST['g2_form']);
} elseif( !empty( $_REQUEST['g2_itemId'] ) ) {
	//If we don't have g2_form, they must be asking the gallery to be opened upon export completion
	$gallery = new FisheyeGallery( $_REQUEST['g2_itemId'] );
	bit_redirect( $gallery->getDisplayUrl() );
}


?>
