<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/upload_inc.php,v 1.11 2006/05/18 18:41:08 squareing Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * fisheye_get_default_gallery_id
 */
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

/**
 * fisheye_store_upload
 */
function fisheye_store_upload( &$pFileHash, $pOrder = 10 ) {
	global $gBitSystem;
	if( !empty( $pFileHash ) && ( $pFileHash['size'] > 0 ) && is_file( $pFileHash['tmp_name'] ) ) {
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

		// when we're using xupload, we need to remove temp files manually
		@unlink( $pFileHash['tmp_name'] );
	}
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

		fisheye_process_directory( $destDir, $pParentGallery, $pRoot );
	} else {
		global $gBitUser;
		if( $gBitUser->hasPermission( 'p_fisheye_upload_nonimages' ) ) {
			fisheye_store_upload( $pFileHash );
		} else {
			$errors['upload'] = tra( 'Your upload could not be processed because it was determined to be a non-image and you only have permission to upload images.' );
		}
	}
	return $errors;
}

/**
 * Recursively builds a tree where each directory represents a gallery, and files are assumed to be images.
 */
function fisheye_process_directory( $pDestinationDir, &$pParentGallery, $pRoot=FALSE ) {
	global $gBitSystem;
	$errors = array();
	if( $archiveDir = opendir( $pDestinationDir ) ) {
		$order = 100;
		while( $fileName = readdir($archiveDir) ) {
			$sortedNames[] = $fileName;
		}
		sort( $sortedNames );
		foreach( $sortedNames as $fileName ) {
			if( !preg_match( '/^\./', $fileName ) && ( $fileName != 'Thumbs.db' ) ) {
				$scanFile = array(
					'type' => $gBitSystem->lookupMimeType( substr( $fileName, ( strrpos( $fileName, '.' ) + 1 )  ) ),
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
	return $errors;
}


// this function will process a directory and all it's sub directories without
// making any assumptions. hierarchy of sub directories is maintained and
// archives can be processed or simply added to the galleries.
function fisheye_process_ftp_directory( $pProcessDir ) {
	global $gBitSystem, $gBitUser;
	if( empty( $_REQUEST['galleryAdditions'] ) ) {
		$_REQUEST['galleryAdditions'] = array();
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
						$dirGallery->addToGalleries( $_REQUEST['galleryAdditions'] );
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
							$newImage->addToGalleries( $_REQUEST['galleryAdditions'] );

							// if we have a gallery to add these images to, load one of them
							if( !empty( $_REQUEST['galleryAdditions'][0] ) && @!is_object( $imageGallery ) ) {
								$imageGallery = new FisheyeGallery();
								$imageGallery->mGalleryId = $_REQUEST['galleryAdditions'][0];
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
