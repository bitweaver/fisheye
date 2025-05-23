<?php
/**
 * @package fisheye
 * @subpackage functions
 */



/**
 * fisheye_handle_upload
 */
function fisheye_handle_upload( &$pFiles, &$pRequest ) {
	global $gBitUser, $gContent, $gBitSystem, $fisheyeErrors, $fisheyeWarnings, $fisheyeSuccess, $gFisheyeUploads;

	// first of all set the execution time for this process to unlimited
	set_time_limit(0);

	$upImages = array();
	$upArchives = array();
	$upErrors = array();
	$upData = array();

	$i = 0;
	usort( $pFiles, 'fisheye_sort_uploads' );

	// No gallery was specified, let's try to find one or create one.
	if( empty( $_REQUEST['gallery_additions'] ) ) {
		if( $gBitUser->hasPermission( 'p_fisheye_create' )) {
			$_REQUEST['gallery_additions'] = array( fisheye_get_default_gallery_id( $gBitUser->mUserId, $gBitUser->getDisplayName()."'s Gallery" ) );
		} else {
			$gBitSystem->fatalError( tra( "You don't have permissions to create a new gallery. Please select an existing one to insert your images to." ));
		}
	}

	if( !is_object( $gContent ) || !$gContent->isValid() ) {
		$gContent = new FisheyeGallery( $_REQUEST['gallery_additions'][0] );
		if( !$gContent->load() ) {
			unset( $gContent );
			$_REQUEST['gallery_additions'] = array( fisheye_get_default_gallery_id( $gBitUser->mUserId, $gBitUser->getDisplayName()."'s Gallery" ) );
		}
	}


	foreach( array_keys( $pFiles ) as $key ) {
		$pFiles[$key]['type'] = $gBitSystem->verifyMimeType( $pFiles[$key]['tmp_name'] );
		if( preg_match( '/(^image|pdf|vnd)/i', $pFiles[$key]['type'] ) ) {
			$upImages[$key] = $pFiles[$key];
			// clone the request data so edit service values are passed into store process
			$upData[$key] = $_REQUEST;
			// add the form data for each upload
			if( !empty( $_REQUEST['imagedata'][$i] ) ) {
				array_merge( $upData[$key], $_REQUEST['imagedata'][$i] );
			}
		} elseif( !empty( $pFiles[$key]['tmp_name'] ) && !empty( $pFiles[$key]['name'] ) ) {
			$upArchives[$key] = $pFiles[$key];
		}

		$i++;
	}

	foreach( array_keys( $upArchives ) as $key ) {
		$upErrors = fisheye_process_archive( $upArchives[$key], $gContent, TRUE );
	}

	foreach( array_keys( $upImages ) as $key ) {
		// resize original if we the user requests it
		if( !empty( $_REQUEST['resize'] ) ) {
			$upImages[$key]['resize'] = $_REQUEST['resize'];
		}
		$upErrors = array_merge( $upErrors, fisheye_store_upload( $upImages[$key], $upData[$key], !empty( $_REQUEST['rotate_image'] )));
	}

	if( !empty( $gFisheyeUploads ) ){
		$_REQUEST['uploaded_objects'] = &$gFisheyeUploads;
		if( is_a( $gContent, 'LibertyContent' ) ) {	
			$gContent->invokeServices( "content_post_upload_function", $_REQUEST );
		}
	}

	return $upErrors;
}


/**
 * fisheye_sort_upload
 */
function fisheye_sort_uploads( $a, $b ) {
	return strnatcmp( $a['name'], $b['name'] );
}

/**
 * fisheye_get_default_gallery_id
 */
function fisheye_get_default_gallery_id( $pUserId, $pNewName ) {
	global $gBitUser;
	$gal = new FisheyeGallery();
	$getHash = array( 'user_id' => $pUserId, 'max_records' => 1, 'sort_mode' => 'created_desc', 'show_empty' => TRUE );
	$upList = $gal->getList( $getHash );
	if( !empty( $upList ) ) {
		$ret = key( $upList );
	} else { 
		$galleryHash = array( 'title' => $pNewName );
		if( $gal->store( $galleryHash ) ) {
			$ret = $gal->mGalleryId;
		}
	}

	global $gContent;
	if( $ret && (!is_object( $gContent ) || !$gContent->isValid()) ) {
		$gContent = new FisheyeGallery( $ret );
		$gContent->load();
	}
	return $ret;
}

/**
 * fisheye_store_upload
 */
function fisheye_store_upload( &$pFileHash, $pImageData = array(), $pAutoRotate=TRUE ) {
	global $gBitSystem, $gFisheyeUploads;
	$ret = array();

	// verifyMimeType to make sure we are working with the proper file type assumptions
	$pFileHash['type'] = $gBitSystem->verifyMimeType($pFileHash['tmp_name']);	
	if( !empty( $pFileHash ) && ( $pFileHash['size'] > 0 ) && is_file( $pFileHash['tmp_name'] ) && fisheye_verify_upload_item(  $pFileHash ) ) {
		// make a copy for each image we need to store
		$image = new FisheyeImage();
		// Store/Update the image
		$pImageData['_files_override'] = array( $pFileHash );
		$pImageData['process_storage'] = STORAGE_IMAGE;
		$pImageData['purge_from_galleries'] = TRUE;
		// store the image
		if( !$image->store( $pImageData ) ) {
			$ret = $image->mErrors;
		} else {
			$pFileHash['content_id'] = $image->getField( 'content_id' );
			$pFileHash['image_id'] = $image->getField( 'image_id' );
			$image->load();
			// play with image some more if user has requested it
			if( $pAutoRotate ) {
				$image->rotateImage( 'auto' );
			}
			if( !($galleryAdditions = BitBase::getParameter( $pImageData, 'gallery_additions' )) ) {
				global $gBitUser;
				$galleryAdditions = array( fisheye_get_default_gallery_id( $gBitUser->mUserId, $gBitUser->getDisplayName()."'s Gallery" ) );
			}
			$image->addToGalleries( $galleryAdditions );
			$gFisheyeUploads[] = $image;
		}

		// when we're using xupload, we need to remove temp files manually
		@unlink( $pFileHash['tmp_name'] );
	}
	return $ret;
}

/**
 * Recursively builds a tree where each directory represents a gallery, and files are assumed to be images.
 */
function fisheye_process_archive( &$pFileHash, &$pParentGallery, $pRoot=FALSE ) {
	global $gBitSystem, $gBitUser;
	$errors = array();

	if( ( $destDir = liberty_process_archive( $pFileHash ) ) && ( !empty( $_REQUEST['process_archive'] ) || !$gBitUser->hasPermission( 'p_fisheye_upload_nonimages' ) ) ) {
		if( empty( $pParentGallery ) && !is_file( $pFileHash['tmp_name'] ) ) {
			$pParentGallery = new FisheyeGallery();
			$galleryHash = array( 'title' => basename( $destDir ) );
			if( !$pParentGallery->store( $galleryHash ) ) {
				$errors = array_merge( $errors, array_values( $pParentGallery->mErrors ) );
			}
			global $gContent;
			$gContent = &$pParentGallery;
		}

		if( !empty( $pParentGallery ) ) {
			$pFileHash['gallery_id'] = $pParentGallery->getField( 'gallery_id' );
		}
		fisheye_process_directory( $destDir, $pParentGallery, $pRoot );
	} else {
		global $gBitUser;
		if( $gBitUser->hasPermission( 'p_fisheye_upload_nonimages' ) ) {
			$errors = array_merge( $errors, fisheye_store_upload( $pFileHash ));
		} else {
			$errors['upload'] = tra( 'Your upload could not be processed because it was determined to be a non-image and you only have permission to upload images.' );
		}
	}
	return $errors;
}

if( !function_exists( 'fisheye_verify_upload_item' ) ) {
// Possible override
function fisheye_verify_upload_item( $pScanFile ) {
	global $gBitUser;
	return $gBitUser->hasPermission( 'p_fisheye_upload_nonimages' ) || preg_match( '/^video\/*/', $pScanFile['type'] ) || preg_match( '/^image\/*/', $pScanFile['type'] ) || preg_match( '/pdf/i', $pScanFile['type'] );
}
}

/**
 * Recursively builds a tree where each directory represents a gallery, and files are assumed to be images.
 */
function fisheye_process_directory( $pDestinationDir, &$pParentGallery, $pRoot=FALSE ) {
	global $gBitSystem, $gBitUser;
	$errors = array();
	if( $archiveDir = opendir( $pDestinationDir ) ) {
		$order = 100;
		while( $fileName = readdir($archiveDir) ) {
			$sortedNames[] = $fileName;
		}
		sort( $sortedNames );
		foreach( $sortedNames as $fileName ) {
			if( $fileName == 'Thumbs.db' ) {
				unlink( "$pDestinationDir/$fileName" );
			}
			if( !preg_match( '/^\./', $fileName ) && ( $fileName != 'Thumbs.db' ) ) {
				$mimeResults = $gBitSystem->verifyFileExtension( $pDestinationDir.'/'.$fileName );
				$scanFile = array(
					'type' => $mimeResults[1],
					'name' => $fileName,
					'size' => filesize( "$pDestinationDir/$fileName" ),
					'tmp_name' => "$pDestinationDir/$fileName",
				);

				if( !empty( $_REQUEST['resize'] ) && is_numeric( $_REQUEST['resize'] ) ) {
					$scanFile['max_height'] = $scanFile['max_width'] = $_REQUEST['resize'];
				}

				if( is_dir( $pDestinationDir.'/'.$fileName ) ) {
					if( $fileName == '__MACOSX' ) {
						// Mac OS resources file
						unlink_r( $pDestinationDir.'/'.$fileName );
					} else {
						// We found a new Gallery!
						$newGallery = new FisheyeGallery();
						$galleryHash = array( 'title' => str_replace( '_', ' ', $fileName ) );
						if( $newGallery->store( $galleryHash ) ) {
							if( $pRoot ) {
								$newGallery->addToGalleries( $_REQUEST['gallery_additions'] );
							}
							if( is_object( $pParentGallery ) ) {
								$pParentGallery->addItem( $newGallery->mContentId, $order );
							}
							//recurse down in!
							$errors = array_merge( $errors, fisheye_process_archive( $scanFile, $newGallery ) );
						} else {
							$errors = array_merge( $errors, array_values( $newGallery->mErrors ) );
						}
					}
				} elseif( preg_match( '/.+\/*zip*/', $scanFile['type'] ) ) {
					//recurse down in!
					$errors = array_merge( $errors, fisheye_process_archive( $scanFile, $pParentGallery ) );
				} elseif( fisheye_verify_upload_item( $scanFile ) ) {
					$newImage = new FisheyeImage();
					$imageHash = array( '_files_override' => array( $scanFile ) );
					if( $newImage->store( $imageHash ) ) {
						if( $pRoot ) {
							$newImage->addToGalleries( $_REQUEST['gallery_additions'] );
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
				} elseif( is_file( $scanFile['tmp_name'] ) ) {
					// unknown file type, let's be tidy and clean it up
					unlink( $scanFile['tmp_name'] );
				}
				$order += 10;
			}
		}
		if ( !is_windows() ) {
			unlink_r( $pDestinationDir );
		}
	}
	return $errors;
}


// this function will process a directory and all it's sub directories without
// making any assumptions. hierarchy of sub directories is maintained and
// archives can be processed or simply added to the galleries.
function fisheye_process_ftp_directory( $pProcessDir ) {
	global $gBitSystem, $gBitUser;
	if( empty( $_REQUEST['gallery_additions'] ) ) {
		$_REQUEST['gallery_additions'] = array();
	}

	$errors = array();
	if( $archiveDir = opendir( $pProcessDir ) ) {
		$order = 100;
		while( $fileName = readdir($archiveDir) ) {
			$sortedNames[] = $fileName;
		}

		sort( $sortedNames );
		$order = 100;

		foreach( $sortedNames as $fileName ) {
			if( !preg_match( '/^\./', $fileName ) && ( $fileName != 'Thumbs.db' ) ) {
				$scanFile = array(
					'type'     => $gBitSystem->lookupMimeType( substr( $fileName, ( strrpos( $fileName, '.' ) + 1 )  ) ),
					'name'     => $fileName,
					'size'     => filesize( "$pProcessDir/$fileName" ),
					'tmp_name' => "$pProcessDir/$fileName",
				);

				if( is_dir( $pProcessDir.'/'.$fileName ) ) {
					// create a new gallery from directory
					$dirGallery = new FisheyeGallery();
					$galleryHash = array( 'title' => str_replace( '_', ' ', $fileName ) );
					if( $dirGallery->store( $galleryHash ) ) {
						$dirGallery->addToGalleries( $_REQUEST['gallery_additions'] );
						$errors = array_merge( $errors, fisheye_process_directory( $pProcessDir.'/'.$fileName, $dirGallery ) );
					} else {
						$errors = array_merge( $errors, array_values( $dirGallery->mErrors ) );
					}
					unset( $dirGallery );
				} else {
					if( preg_match( '/(^image|pdf)/i', $scanFile['type'] ) ) {
						// process image
						$newImage = new FisheyeImage();
						$imageHash = array( 'upload' => $scanFile );
						if( $newImage->store( $imageHash ) ) {
							$newImage->addToGalleries( $_REQUEST['gallery_additions'] );

							// if we have a gallery to add these images to, load one of them
							if( !empty( $_REQUEST['gallery_additions'][0] ) && @!is_object( $imageGallery ) ) {
								$imageGallery = new FisheyeGallery();
								$imageGallery->mGalleryId = $_REQUEST['gallery_additions'][0];
								$imageGallery->load();
							}

							if( @!is_object( $imageGallery ) ) {
								global $gBitUser;
								$galleryHash = array( 'title' => $gBitUser->getDisplayName()."'s Gallery" );
								$imageGallery = new FisheyeGallery();
								if( $imageGallery->store( $galleryHash ) ) {
									$imageGallery->load();
								} else {
									$errors = array_merge( $errors, array_values( $imageGallery->mErrors ) );
								}
							}

							$imageGallery->addItem( $newImage->mContentId );
						} else {
							$errors = array_merge( $errors, array_values( $newImage->mErrors ) );
						}
					} else {
						// create a new gallery from archive
						$archiveGallery = new FisheyeGallery();
						$galleryHash = array( 'title' => substr( $fileName, 0, ( str_replace( '_', ' ', strrpos( $fileName, '.' ) ) ) ) );
						if( !$archiveGallery->store( $galleryHash ) ) {
							$errors = array_merge( $errors, array_values( $archiveGallery->mErrors ) );
						}

						$errors = fisheye_process_archive( $scanFile, $archiveGallery, TRUE );
						unset( $archiveGallery );
					}
				}
				$order += 10;
			}
		}
	}
	return $errors;
}
?>
