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

// Recursively builds a tree where each directory represents a gallery, and files are assumed to be images.
function fisheye_process_archive( &$pFileHash, &$pParentGallery, $pRoot=FALSE ) {
	global $gBitSystem, $gBitUser;
	$errors = array();
	if( ($destDir = liberty_process_archive( $pFileHash )) && (!empty( $_REQUEST['process_archive'] ) || !$gBitUser->hasPermission( 'bit_p_fisheye_upload_nonimages' )) ) {
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
		} else {
			$errors['upload'] = tra( 'Your upload could not be processed because it was determined to be a non-image and you only have permission to upload images.' );
		}
	}
	return $errors;
}

// Recursively builds a tree where each directory represents a gallery, and files are assumed to be images.
function fisheye_process_directory( $pDestinationDir, &$pParentGallery, $pRoot=FALSE ) {
	$errors = array();
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
