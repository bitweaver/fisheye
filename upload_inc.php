<?php
function fisheye_get_default_gallery_id( $pUserId, $pNewName ) {
	$gal = new FisheyeGallery();
	$getHash = array( 'user_id' => $pUserId, 'max_records' => 1, 'sort_mode' => 'created_desc' );
	$upList = $gal->getList( $getHash );
	if( !empty( $upList['data'] ) ) {
		$ret = key( $upList['data'] );
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

function fisheye_store_upload( &$pFileHash, $pOrder = 10 ) {
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

		$image->addToGalleries( $_REQUEST['galleryAdditions'], $pOrder );
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
				$shellResult = shell_exec( "unrar x $pFileHash[tmp_name] \"$destDir\"" );
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
					$shellResult = shell_exec( "unrar x $pFileHash[tmp_name] \"$destDir\"" );
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
	global $gBitSystem;
	$errors = array();
	if( $destDir = liberty_process_archive( $pFileHash ) && !empty( $_REQUEST['process_archive'] ) ) {

		if( empty( $pParentGallery ) && !is_uploaded_file( $pFileHash['tmp_name'] ) ) {
			$pParentGallery = new FisheyeGallery();
			$galleryHash = array( 'title' => basename( $destDir ) );
			if( !$pParentGallery->store( $galleryHash ) ) {
				$errors = array_merge( $errors, array_values( $pParentGallery->mErrors ) );
			}
			global $gContent;
			$gContent = &$pParentGallery;
		}

		fisheye_process_directory( $destDir, $pParentGallery, $pRoot );
	} else {
		global $gBitUser;
		if( $gBitUser->hasPermission( 'bit_p_fisheye_upload_nonimages' ) ) {
			fisheye_store_upload( $pFileHash );
		}
	}
	return $errors;
}

// Recursively builds a tree where each directory represents a gallery, and files are assumed to be images.
function fisheye_process_directory( $pDestinationDir, &$pParentGallery, $pRoot=FALSE ) {
	if( $archiveDir = opendir( $pDestinationDir ) ) {
		$order = 100;
		while( $fileName = readdir($archiveDir) ) {
			$sortedNames[] = $fileName;
		}
		sort( $sortedNames );
		foreach( $sortedNames as $fileName ) {
			if( !preg_match( '/^\./', $fileName ) ) {
				$scanFile = array(
					'name' => $fileName,
					'size' => filesize( "$pDestinationDir/$fileName" ),
					'tmp_name' => "$pDestinationDir/$fileName",
				);

				if( !empty( $_REQUEST['resize'] ) && is_numeric( $_REQUEST['resize'] ) ) {
					$scanFile['max_height'] = $scanFile['max_width'] = $_REQUEST['resize'];
				}

				if( is_dir( $pDestinationDir.'/'.$fileName ) ) {
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
}
?>
