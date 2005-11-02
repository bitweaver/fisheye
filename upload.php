<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/upload.php,v 1.1.1.1.2.12 2005/11/02 15:57:38 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem;
global $fisheyeErrors, $fisheyeWarnings, $fisheyeSuccess;

include_once( FISHEYE_PKG_PATH.'gallery_lookup_inc.php' );

if (!empty($_REQUEST['save_image'])) {
	// first of all set the execution time for this process to unlimited
	 set_time_limit(0);

	$upImages = array();
	$upArchives = array();
	$upErrors = array();
	foreach( array_keys( $_FILES ) as $key ) {
		if( preg_match( '/(^image|pdf)/i', $_FILES[$key]['type'] ) ) {
			$upImages[$key] = $_FILES[$key];
		} elseif( !empty( $_FILES[$key]['size'] ) ) {
			$upArchives[$key] = $_FILES[$key];
		}
	}

	$galleryAdditions = array();

	// No gallery was specified, let's try to find one or create one.
	if( empty( $_REQUEST['galleryAdditions'] ) ) {
		$_REQUEST['galleryAdditions'] = array( fisheye_get_default_gallery_id( $gBitUser->mUserId, $gBitUser->getDisplayName()."'s Gallery" ) );
	}

	$newGalleries = array();
	foreach( array_keys( $upArchives ) as $key ) {
		$upErrors = fisheye_process_archive( $upArchives[$key], $gContent, TRUE );
	}

	$order = 100;
	foreach( array_keys( $upImages ) as $key ) {
		fisheye_store_upload( $upImages[$key] );
	}

	if( !is_object( $gContent ) || !$gContent->isValid() ) {
		$gContent = new FisheyeGallery( $_REQUEST['galleryAdditions'][0] );
		$gContent->load();
	}

	if( empty( $upErrors ) ) {
		header( 'Location: '.$gContent->getDisplayUrl() );
		die;
	} else {
		$gBitSmarty->assign( 'errors', $upErrors );
	}
}

// settings that are useful to know about at upload time
$postMax = str_replace( 'M', '', ini_get( 'post_max_size' ));
$uploadMax = str_replace( 'M', '', ini_get( 'upload_max_filesize' ) );

if( $postMax < $uploadMax ) {
	$uploadMax = $postMax;
}


if( $gBitSystem->isPackageActive( 'quota' ) ) {
	require_once( QUOTA_PKG_PATH.'LibertyQuota.php' );
	$quota = new LibertyQuota();
	if( !$gBitUser->isAdmin() && !$quota->isUserUnderQuota( $gBitUser->mUserId ) ) {
		$gBitSystem->display( 'bitpackage:quota/over_quota.tpl', tra( 'You are over your quota.' ) );
		die;
	}
	if( !$gBitUser->isAdmin() ) {
		// Prevent people from uploading more than there quota
		$q = $quota->getUserQuota( $gBitUser->mUserId );
		$u = $quota->getUserUsage( $gBitUser->mUserId );
		$gBitSmarty->assign('quotaMessage', tra( 'Your remaining disk quota is' ).' '.round(($q-$u)/1000000, 2).' '.tra('Megabytes') );
		$qMegs = round( $q / 1000000 );
		if( $qMegs < $uploadMax ) {
			$uploadMax = $qMegs;
		}
	}
}

// Get a list of all existing galleries
$gFisheyeGallery = new FisheyeGallery();
$listHash = array( 'user_id' => $gBitUser->mUserId, 'show_empty' => true, 'max_records'=>-1, 'no_thumbnails'=>TRUE, 'sort_mode'=>'title_asc' );
$galleryList = $gFisheyeGallery->getList( $listHash );
$gBitSmarty->assign_by_ref('galleryList', $galleryList);

$gBitSmarty->assign( 'uploadMax', $uploadMax );

$gBitSystem->display( 'bitpackage:fisheye/upload_fisheye.tpl', 'Upload Images' );

	function fisheye_get_default_gallery_id( $pUserId, $pNewName ) {
		$gal = new FisheyeGallery();
		$getHash = array( 'user_id' => $pUserId, 'max_records' => 1, 'sort_mode' => 'created_desc' );
		if( $upList = $gal->getList( $getHash ) ) {
			$ret = key( $upList );
		} else {
			$galleryHash = array( 'title' => $pNewName );
			if( $gal->store( $galleryHash ) ) {
				$ret = $gal->mGalleryId;
			}
		}

		global $gContent;
		if( !is_object( $gContent ) || !$gContent->isValid() ) {
			$gContent = new FisheyeGallery( $ret );
			$gContent->load();
		}
		return $ret;
	}

	function fisheye_store_upload( &$pFileHash ) {
		if( !empty( $pFileHash ) && ($pFileHash['size'] > 0) && is_uploaded_file( $pFileHash['tmp_name'] ) ) {
			// make a copy for each image we need to store
			$storeHash = $_REQUEST;
			$image = new FisheyeImage();
			// Store/Update the image
			$storeHash['upload'] = &$pFileHash;
			$storeHash['upload']['process_storage'] = STORAGE_IMAGE;
			$storeHash['purge_from_galleries'] = TRUE;
			if( !$image->store( $storeHash ) ) {
				array_merge( $upErrors, array_values( $image->mErrors ) );
			}

			$image->addToGalleries( $_REQUEST['galleryAdditions'], $order );
			$order += 10;
		}
	}

	function liberty_process_archive( &$pFileHash ) {
		$cwd = getcwd();
		$dir = dirname( $pFileHash['tmp_name'] );
		$upExt = strtolower( substr( $pFileHash['name'], (strrpos( $pFileHash['name'], '.' ) + 1) ) );
		$baseDir = $dir.'/';
		if( is_uploaded_file( $pFileHash['tmp_name'] ) ) {
			global $gBitUser;
			$baseDir .= $gBitUser->mUserId;
		}
		$destDir = $baseDir.'/'.basename( $pFileHash['tmp_name'] );
		if( (is_dir( $baseDir ) || mkdir( $baseDir )) && @mkdir( $destDir ) ) {
			// Some commands don't nicely support extracting to other directories
			chdir( $destDir );
			list( $mimeType, $mimeExt ) = split( '/', $pFileHash['type'] );
			switch( $mimeExt ) {
				case 'x-rar-compressed':
				case 'x-rar':
					$shellResult = shell_exec( "rar x -w\"$destDir\" $pFileHash[tmp_name] " );
					break;
				case 'x-bzip2':
				case 'bzip2':
				case 'x-gzip':
				case 'gzip':
				case 'x-tgz':
				case 'x-tar':
				case 'tar':
					switch( $upExt ) {
						case 'gz':
						case 'tgz': $compressFlag = '-z'; break;
						case 'bz2': $compressFlag = '-j'; break;
						default: $compressFlag = ''; break;
					}
					$shellResult = shell_exec( "tar -x $compressFlag -f $pFileHash[tmp_name]  -C \"$destDir\"" );
					break;
				case 'x-zip-compressed':
				case 'x-zip':
				case 'zip':
					$shellResult = shell_exec( "unzip $pFileHash[tmp_name] -d \"$destDir\"" );
					break;
				case 'x-stuffit':
				case 'stuffit':
					$shellResult = shell_exec( "unstuff -d=\"$destDir\" $pFileHash[tmp_name] " );
					break;
				default:
					if( $upExt == 'zip' ) {
						$shellResult = shell_exec( "unzip $pFileHash[tmp_name] -d \"$destDir\"" );
					} elseif( $upExt == 'rar' ) {
						$shellResult = shell_exec( "rar x -w\"$destDir\" $pFileHash[tmp_name] " );
					} elseif( $upExt == 'sit' || $upExt == 'sitx' ) {
						print( "unstuff -d=\"$destDir\" $pFileHash[tmp_name] " );
						$shellResult = shell_exec( "unstuff -d=\"$destDir\" $pFileHash[tmp_name] " );
						vd( $shellResult );
					} else {
						$destDir = NULL;
					}
					break;
			}
		}
		chdir( $cwd );
		return $destDir;
	}


	// Recursively builds a tree where each directory represents a gallery, and files are assumed to be images.
	function fisheye_process_archive( &$pFileHash, &$pParentGallery, $pRoot=FALSE ) {
		$errors = NULL;
		if( $destDir = liberty_process_archive( $pFileHash ) ) {

			if( empty( $pParentGallery ) && !is_uploaded_file( $pFileHash['tmp_name'] ) ) {
				$pParentGallery = new FisheyeGallery();
				$galleryHash = array( 'title' => basename( $destDir ) );
				if( !$pParentGallery->store( $galleryHash ) ) {
					$errors = array_merge( $errors, array_values( $pParentGallery->mErrors ) );
				}
				global $gContent;
				$gContent = &$pParentGallery;
			}

			if( $archiveDir = opendir( $destDir ) ) {
				$order = 100;
				while( $fileName = readdir($archiveDir) ) {
					$sortedNames[] = $fileName;
				}
				sort( $sortedNames );
				foreach( $sortedNames as $fileName ) {
					if( !preg_match( '/^\./', $fileName ) ) {
						$scanFile = array(
							'name' => $fileName,
							'size' => filesize( "$destDir/$fileName" ),
							'tmp_name' => "$destDir/$fileName",
						);

						if( !empty( $_REQUEST['resize'] ) && is_numeric( $_REQUEST['resize'] ) ) {
							$scanFile['max_height'] = $scanFile['max_width'] = $_REQUEST['resize'];
						}

						if( is_dir( $destDir.'/'.$fileName ) ) {
							// We found a new Gallery!
							$newGallery = new FisheyeGallery();
							$galleryHash = array( 'title' => str_replace( '_', ' ', $fileName ) );
							if( $newGallery->store( $galleryHash ) ) {
								if( $pRoot ) {
									$newGallery->addToGalleries( $_REQUEST['galleryAdditions'] );
								}
								if( is_object( $pParentGallery ) ) {
									$pParentGallery->addItem( $newGallery->mContentId, $order );
								}
								//recurse down in!
								$errors = array_merge( $errors, fisheye_process_archive( $scanFile, $newGallery ) );
							} else {
								$errors = array_merge( $errors, array_values( $newGallery->mErrors ) );
							}
						} else {
							// support for the fileinfo pecl package: http://pecl.php.net/package/fileinfo
							if( function_exists( 'finfo_open' ) ) {
								$res = finfo_open(FILEINFO_MIME);
								$scanFile['type'] = finfo_file($res, $scanFile['tmp_name']);
								finfo_close($res);
							}
							if( empty( $scanFile['type'] ) ) {
								$scanFile['type'] = ( "image/unknown" );
							}
							$newImage = new FisheyeImage();
							$imageHash = array( 'upload' => $scanFile );
							if( $newImage->store( $imageHash ) ) {
								if( $pRoot ) {
									$newImage->addToGalleries( $_REQUEST['galleryAdditions'] );
								}
								if( !is_object( $pParentGallery ) ) {
									global $gBitUser;
									$pParentGallery = new FisheyeGallery( fisheye_get_default_gallery_id( $gBitUser->mUserId, $gBitUser->getDisplayName()."'s Gallery" ) );
									$pParentGallery->load();
								}
								$pParentGallery->addItem( $newImage->mContentId );
							} else {
								$errors = array_merge( $errors, array_values( $newImage->mErrors ) );
							}
						}
						$order += 10;
					}
				}
			}
		} else {
			fisheye_store_upload( $pFileHash );
		}
		return $errors;
	}




?>
