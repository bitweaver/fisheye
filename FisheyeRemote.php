<?php
/**
* Gallery2 Remote support for fisheye
*
* @package  fisheye
* @version  $Header: /cvsroot/bitweaver/_bit_fisheye/FisheyeRemote.php,v 1.10 2010/05/04 01:09:26 spiderr Exp $
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

	var $mSubGalIdx = 1;

	function getApiVersion() {
		return '2.14';
	}


	// separate out pPostData and pParamhash data since some plugins can populate _POST['g2_form'] and _GET['g2_form'] differently.
	// weird but true. ubermind is an example
    function processRequest( $pGetData, $pPostData ) {
		$pData = array_merge($pGetData, $pPostData); //Some programs (galleryexport) pass both post and get...and the cmd can be in either get or post

		if(!empty($pData)){
			switch ($pData['cmd']) {
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
	
		$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'No-op successful' );
		return $response;
    }

    function cmdLogin( $pParamHash ) {
		global $gBitUser, $gBitSystem;
		$url = $gBitUser->login( $pParamHash['uname'], $pParamHash['password'] );
		if( $gBitUser->isRegistered() ) {
			/*
			$cookieTime =  ( int )( time() + $gBitSystem->getConfig( 'users_remember_time', 86400 ));
			$cookiePath = $gBitSystem->getConfig( 'cookie_path', BIT_ROOT_URL );
			$cookieDomain = $gBitSystem->getConfig( 'cookie_domain', "" );
			setcookie( 'GALLERYSID', session_id(), $cookieTime, $cookiePath, $cookieDomain );
			*/
			$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'Login successful.', array( 'server_version' => $this->getApiVersion() ) );
		} else {
			$response = $this->createResponse( FEG2REMOTE_PASSWORD_WRONG, 'Invalid username or password' );
		}
		return $response;
    }

	// Recursively traverses a multi-dimensional array of galleries
	function traverseGalleries( &$pGalHash, &$pResponse ) {
		global $gBitUser;

		// Albums don't like being 0 indexed 
		$this->mSubGalIdx = 0;

		// the lightroom client is dumb, and can only handle one 0 level parent
		if( stripos( $_SERVER['HTTP_USER_AGENT'], 'lightroom' ) !== FALSE ) {
			$this->mSubGalIdx++;
			$pResponse['album.parent.' . $this->mSubGalIdx] = 0;
			$pResponse['album.name.' . $this->mSubGalIdx] = 1;
			$pResponse['album.title.' . $this->mSubGalIdx] = tra( 'Select a Gallery' );
			$pResponse['album.perms.add.' . $this->mSubGalIdx] = 'false';
			$pResponse['album.perms.write.' . $this->mSubGalIdx] = 'false';
			$pResponse['album.perms.del_alb.' . $this->mSubGalIdx] = 'false';
			$pResponse['album.perms.create_sub.' . $this->mSubGalIdx] = 'true';
		}
		
		return $this->traverseSubGalleries( $pGalHash, $pResponse, 1 );
	}

    /**
    * Function that returns link to display a piece of content
    * @param $pGalHash branch of gallery information from FisheyeGallery::getTree
    * @param $pResponse aggregate string containing response array
    * @param $pParentRandom depth of pGalHash - this is used to non-definitively uniquify album.parent and album.name entries
    * @return the url to display the gallery.
    */
	function traverseSubGalleries( &$pGalHash, &$pResponse, $pParentRandom ) {
		global $gBitUser;
		foreach( $pGalHash as $key=>$gallery) { 
			$this->mSubGalIdx++;

			// Any number greater than 2 digits crashes iPhoto2Gallery
			$randomizer = str_pad( rand( 1, 99 ), 2, '0' );
			if($gallery['content']['level'] != 0){
				// Fisheye allows directories to below to multiple parents - not in gallery. This confuses some clients
				// We pad with a random number for uniqueness
				$pResponse['album.parent.' . $this->mSubGalIdx] = $gallery['content']['cb_gallery_content_id'].$pParentRandom;
			} else {
				// the lightroom client is dumb, and can only handle one 0 level parent
				if( stripos( $_SERVER['HTTP_USER_AGENT'], 'lightroom' ) !== FALSE ) {
					$pResponse['album.parent.' . $this->mSubGalIdx] = 1;
				} else {
					$pResponse['album.parent.' . $this->mSubGalIdx] = 0;
				}
			}
			// append pParentRandom to make .name probably unique since Fisheye can handle one gallery linked to multiple parents
			$pResponse['album.name.' . $this->mSubGalIdx] = $gallery['content']['content_id'].$randomizer;
			$pResponse['album.title.' . $this->mSubGalIdx] = $this->cleanResponseValue( $gallery['content']['title'] );
		
			if( !empty( $gallery['content']['data'] ) ) {	
				$pResponse['album.summary.' . $this->mSubGalIdx] = $gallery['content']['data'];
				$pResponse['album.info.extrafields.' . $this->mSubGalIdx] = "Summary";
			}

			$pResponse['album.perms.add.' . $this->mSubGalIdx] = 'true';
			$pResponse['album.perms.write.' . $this->mSubGalIdx] = 'true';
			$pResponse['album.perms.del_alb.' . $this->mSubGalIdx] = 'true';
			$pResponse['album.perms.create_sub.' . $this->mSubGalIdx] = 'true';
				
			if( !empty( $gallery['children'] ) ) { 
				$this->traverseSubGalleries($gallery['children'],$pResponse, $randomizer );
			}
		}
		$ret = $this->mSubGalIdx;
		return $ret;
	}	

    function cmdFetchAlbums( $pParamHash ) {
		require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php' );
		global $gBitUser;
		if( $gBitUser->isRegistered() ) {
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
		} else {
			$response = $this->createResponse( FEG2REMOTE_PASSWORD_WRONG, 'Application not logged in' );
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
			$storeHash['title'] = !empty( $pParamHash['force_filename'] ) ? $pParamHash['force_filename'] : NULL;
			$storeHash['summary'] = !empty( $pParamHash['extrafield.Summary'] ) ? $pParamHash['extrafield.Summary'] : NULL;
			$storeHash['edit'] = !empty( $pParamHash['extrafield.Description'] ) ? $pParamHash['extrafield.Description'] : NULL;

			require_once (FISHEYE_PKG_PATH.'upload_inc.php');	
		
			$parentGallery = new FisheyeGallery();
			if( $parentGallery = $parentGallery->lookup(array('content_id' => $pParamHash['set_albumName'] ) ) ) {
				$parentGallery->load();
				$_REQUEST['gallery_additions'] = array($parentGallery->mGalleryId);
			}
			if( $errors = fisheye_store_upload( $uploadFile , $storeHash ) ){
			 	$response = $this->createResponse( FEG2REMOTE_UPLOAD_PHOTO_FAIL, 'Export Failed' );
			} else {
				$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'Image added', array( 'item_name'=>$uploadFile['name'] ) );
			}
		}
		
		return $response;
    }

    function cmdNewAlbum( $pParamHash ) {
		global $gBitUser;
		$response = array();

		if( empty( $pParamHash['newAlbumTitle'] ) ) {
			$pParamHash['newAlbumTitle'] = $gBitUser->getTitle()."'s Gallery";
		}

		$storeHash['title'] = !empty($pParamHash['newAlbumTitle']) ? $pParamHash['newAlbumTitle'] : '';
		$storeHash['edit']  = !empty($pParamHash['newAlbumDesc'])  ? $pParamHash['newAlbumDesc']  : '';
		$gallery = new FisheyeGallery();	
		$gallery->store( $storeHash );

		if($pParamHash['set_albumName']){
			$parentGallery = new FisheyeGallery();
			if( $parentGallery = $parentGallery->lookup(array('content_id' => $pParamHash['set_albumName'] ) ) ) {
				$parentGallery->load();
				$gallery->addToGalleries(array($parentGallery->mGalleryId));
			}
		}

		$response = $this->createResponse( FEG2REMOTE_SUCCESS, 'Gallery created', array( 'album_name' => $storeHash['title'] ) );

		return $response;
    }

    function sendResponse( $pResponse ) {
		global $gBitUser;
		print "#__GR2PROTO__\n";
//error_log( "#__GR2PROTO__".' : '.$gBitUser->mUserId );
		foreach ($pResponse as $k => $value) {
			print  "$k=$value\n";
//error_log( "$k=$value" );
		}
		// must be last
		print "auth_token=".$gBitUser->mTicket;
//error_log( "auth_token=".$gBitUser->mTicket );
//error_log( "#__end__" );
    }

	
	function createResponse( $pStatus, $pStatusText, $pExtra = NULL ) {
		$ret = array();
		
		// Each response must contain at least the keys: status and status_text. 
		$ret['status'] = $this->cleanResponseValue( $pStatus );
		// translate the text response for i18n
		$ret['status_text'] = $this->cleanResponseValue( tra( $pStatusText ) );
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
		$pValue = str_replace(array('#', '!', '='), array('\\#', '\\!', '\\='), $pValue);
		return $pValue;
    }


	function cleanResponseKey( $pKey ) {
		return str_replace(array('#', '!', '=', ':'), array('\\#', '\\!', '\\=', '\\:'), $pKey);
	}
	

}
