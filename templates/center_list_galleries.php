<?php
	global $gQueryUser;
	$gFisheyeGallery = new FisheyeGallery();

	/* Get a list of galleries which matches the imput paramters (default is to list every gallery in the system) */
	$_REQUEST['root_only'] = TRUE;
	$_REQUEST['get_thumbnails'] = TRUE;
	$galleryList = $gFisheyeGallery->getList( $_REQUEST );
	$gBitSmarty->assign_by_ref( 'galleryList', $galleryList['data'] );

	/* Process the input parameters this page accepts */
	if (!empty($gQueryUser) && $gQueryUser->isRegistered()) {
		$gBitSmarty->assign_by_ref('gQuerUserId', $gQueryUser->mUserId);
		$template = 'user_galleries.tpl';
	} else {
		$template = 'list_galleries.tpl';
	}

	if (!empty($_REQUEST['offset']) && is_numeric($_REQUEST['offset'])) {
		$gBitSmarty->assign_by_ref('iMaxRows', $iMaxRows);
	}
	if (!empty($_REQUEST['sort_mode'])) {
		$gBitSmarty->assign_by_ref('iSortMode', $_REQUEST['sort_mode']);
	}
	if (!empty($_REQUEST['search'])) {
		$gBitSmarty->assign_by_ref('iSearchString', $iSearchtring);
	}


?>
