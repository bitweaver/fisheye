<?php
/**
* Gallery2 Remote support for fisheye
*
* @package  fisheye
* @version  $Header: /cvsroot/bitweaver/_bit_fisheye/FisheyeRemote.php,v 1.2 2009/07/13 18:10:13 spiderr Exp $
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

define( 'FEG2REMOTE_SUCCESS', 0 );

define( 'FEG2REMOTE_PROTOCOL_MAJOR_VERSION_INVALID', 101 );
define( 'FEG2REMOTE_PROTOCOL_MINOR_VERSION_INVALID', 102 );
define( 'FEG2REMOTE_PROTOCOL_VERSION_FORMAT_INVALID', 103 );
define( 'FEG2REMOTE_PROTOCOL_VERSION_MISSING', 104 );

define( 'FEG2REMOTE_PASSWORD_WRONG', 201 );
define( 'FEG2REMOTE_LOGIN_MISSING', 202 );

define( 'FEG2REMOTE_UNKNOWN_COMMAND', 301 );
define( 'FEG2REMOTE_MISSING_ARGUMENTS', 302 );

define( 'FEG2REMOTE_NO_ADD_PERMISSION', 401 );
define( 'FEG2REMOTE_NO_FILENAME', 402 );
define( 'FEG2REMOTE_UPLOAD_PHOTO_FAIL', 403 );
define( 'FEG2REMOTE_NO_WRITE_PERMISSION', 404 );
define( 'FEG2REMOTE_NO_VIEW PERMISSION', 405 );

define( 'FEG2REMOTE_NO_CREATE_ALBUM_PERMISSION', 501 );
define( 'FEG2REMOTE_CREATE_ALBUM_FAILED', 502 );
define( 'FEG2REMOTE_MOVE_ALBUM_FAILED', 503 );
define( 'FEG2REMOTE_ROTATE_IMAGE_FAILED', 504 );



class FisheyeRemote {

	var $mResponse = array();

	function getApiVersion() {
		return '2.13';
	}


	// separate out pPostData and pParamhash data since some plugins can populate _POST['g2_form'] and _GET['g2_form'] differently.
	// weird but true. ubermind is an example
    function processRequest( $pGetData, $pPostData ) {
		
		if(!empty($pGetData)){
			switch ($pGetData['cmd']) {
				case 'login':
					$response = $this->cmdLogin( $pPostData );
					break;

				case 'fetch-albums':
					$response = $this->cmdFetchAlbums( $pPostData );
					break;

				case 'fetch-albums-prune':
					$response = $this->cmdFetchAlbums( $pPostData );
					break;

				case 'add-item':
					$response = $this->cmdAddItem( $pPostData );
					break;

				case 'album-properties':
					// not implemented yet
					break;

				case 'new-album':
					$response = $this->cmdNewAlbum( $pPostData );
					break;

				case 'fetch-album-images':
					// not implemented yet
					break;

				case 'move-album':
					// not implemented yet
					break;

				case 'increment-view-count':
					// not implemented yet
					break;

				case 'image-properties':
					// not implemented yet
					break;

				case 'no-op':
					$response = $this->cmdNoOp( $pPostData );
					// not implemented yet
					break;

				default:
					$response = $this->createResponse( FEG2REMOTE_UNKNOWN_COMMAND, "Command unknown: ".$pGetData['cmd'] );
					break;
			}
		} else {
			$response = $this->createResponse( FEG2REMOTE_UNKNOWN_COMMAND, "No command received." );
		}

		if( !empty( $response ) ) {
			print $this->sendResponse( $response );
		}
    }


    function cmdNoOp( $pParamHash ) {
		global $gBitUser;
	
		$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'Ping.' );
		return $response;
    }

    function cmdLogin( $pParamHash ) {
		global $gBitUser;
	
		$url = $gBitUser->login( $pParamHash['uname'], $pParamHash['password'] );	
		if( $gBitUser->isRegistered() ) {
			$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'Login successful.', array( 'server_version' => $this->getApiVersion() ) );
		} else {
			$response = $this->createResponse( FEG2REMOTE_PASSWORD_WRONG, 'Invalid username or password' );
		}
		return $response;
    }

	// Recursively traverses a multi-dimensional array of galleries
	function traverseGalleries( $pGalHash, &$pResponse ) {
		static $i = 1;
		foreach( $pGalHash as $key=>$gallery)
		{ 
			if($gallery['content']['level'] != 0){
				$gallery['content']['title']= '|'.str_repeat('-',$gallery['content']['level']).$gallery['content']['title'];
			}	
			$pResponse['album.name.' . $i] = $gallery['content']['gallery_id'];
			$pResponse['album.title.' . $i] = $this->cleanResponseValue( $gallery['content']['title'] );

			$pResponse['album.perms.add.' . $i] = 'true';
			$pResponse['album.perms.write.' . $i] = 'true';
			$pResponse['album.perms.del_alb.' . $i] = 'true';
			$pResponse['album.perms.create_sub.' . $i] = 'true';
			$i++;
		
			if( !empty( $gallery['children'] ) ) { 
				$this->traverseGalleries($gallery['children'],$pResponse);
			}
		}
		return $i;
	}	

    function cmdFetchAlbums( $pParamHash ) {
		require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php' );
		global $gBitUser;
		$treeGallery = new FisheyeGallery();
		$listHash['user_id'] = $gBitUser->mUserId;
		if( $galleryList = $treeGallery->getTree( $listHash,  array( 'name' => "gallery_id", 'id' => "gallerylist", 'item_attributes' => array( 'class'=>'listingtitle' ) ) ) ) {
			$galResponse = array();
			$galleryCount = $this->traverseGalleries( $galleryList, $galResponse );
			$galResponse['album_count'] = $galleryCount;
			$galResponse['can_create_root'] = 'true';
			$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'Gallery list successful', $galResponse );
		} else {
			// perhaps we should make at least on gallery at this point?
			$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'No galleries', array( 'album_count' => 0 ) );
		}
		return $response;
    }


    function cmdAddItem( $pParamHash ) {
		$response = array();

		$uploadFile =  (!empty( $_FILES['g2_userfile'] ) ? $_FILES['g2_userfile'] : NULL);

		if( empty( $pParamHash['set_albumName'] ) ) {
			$response = $this->createResponse( CREATE_ALBUM_FAILED , 'No gallery specified' );
		} elseif( empty( $uploadFile ) || empty( $uploadFile['size'] ) ) {
			$response = $this->createResponse( FEG2REMOTE_NO_FILENAME, 'No image uploaded' );
		} else {
			$storeHash['title'] = !empty( $pParamHash['caption'] ) ? $pParamHash['caption'] : NULL;
			$storeHash['summary'] = !empty( $pParamHash['extrafield.Summary'] ) ? $pParamHash['extrafield.Summary'] : NULL;
			$storeHash['edit'] = !empty( $pParamHash['extrafield.Description'] ) ? $pParamHash['extrafield.Description'] : NULL;

			require_once (FISHEYE_PKG_PATH.'upload_inc.php');	
			
			$_REQUEST['gallery_additions'] = array($pParamHash['set_albumName']);
			fisheye_store_upload( $uploadFile , $storeHash );

			$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'Image added', array( 'item_name'=>$uploadFile['name'] ) );
		}

		return $response;
    }

    function cmdNewAlbum( $pParamHash ) {
		global $gBitUser;
		$response = array();

		if( empty( $pParamHash['newAlbumTitle'] ) ) {
			$pParamHash['newAlbumTitle'] = $gBitUser->getTitle()."'s Gallery";
		}

		$storeHash['title'] = $pParamHash['newAlbumTitle'];
		$storeHash['edit']  = $pParamHash['newAlbumDesc'];

		$gallery = new FisheyeGallery();	
		$gallery->store( $storeHash );
		$gallery->addToGalleries( array( $pParamHash['set_albumName'] ) );

		$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'Gallery created', array( 'album_name' => $storeHash['title'] ) );

		return $response;
    }

    function sendResponse( $pResponse ) {
		print "#__GR2PROTO__\n";
		foreach ($pResponse as $k => $value) {
			print  "$k=$value\n";
		}
    }

	
	function createResponse( $pStatus, $pStatusText, $pExtra = NULL ) {
		$ret = array();
		
		// Each response must contain at least the keys: status and status_text. 
		$ret['status'] = $this->cleanResponseValue( $pStatus );
		// translate the text response for i18n
		$ret['status_text'] = $this->cleanResponseValue( tra( $pStatus ) );

		// tack on any additional responses
		if( !empty( $pExtra ) && is_array( $pExtra ) ) {
			foreach( $pExtra as $k => $value ) {
				$ret[$this->cleanResponseKey( $k )] = $this->cleanResponseValue( $value );
			}
		}
		return $ret;
	}

	/**
     * This will clean up the response value to make sure it is in an acceptable format for the remote client.
	 * Gallery apparently is very particular about the manner in which this data is cleaned up, and must be done
	 * in this specific order.
	 */
    function cleanResponseValue( $pValue ) {
		$pValue = str_replace('\\', '\\\\', $pValue);
		$pValue = str_replace("\r\n", '\n', $pValue);
		$pValue = str_replace(array("\r", "\n", "\t"), array('\n', '\n', '\t'), $pValue);
		$pValue = str_replace(array('#', '!', '=', ':'), array('\\#', '\\!', '\\=', '\\:'), $pValue);
		return $pValue;
    }


	function cleanResponseKey( $pKey ) {
		return str_replace(array('#', '!', '=', ':'), array('\\#', '\\!', '\\=', '\\:'), $pKey);
	}
	

}
