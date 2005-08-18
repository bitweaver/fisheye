<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/edit_gallery_perms.php,v 1.1.1.1.2.3 2005/08/18 09:02:13 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

require_once( FISHEYE_PKG_PATH.'FisheyeGallery.php');
require_once( FISHEYE_PKG_PATH.'FisheyeImage.php');

global $gBitSystem, $fisheyePermNameMap;

// Make sure an gallery has been specified
if (empty($_REQUEST['gallery_id'])) {
	$gBitSmarty->assign('msg', tra("No gallery specified"));
	$gBitSystem->display( "error.tpl" );
	die;
}

include_once( FISHEYE_PKG_PATH.'gallery_lookup_inc.php' );

if (empty($gContent->mContentId)) {
	$gBitSmarty->assign( 'msg', tra( "The specified gallery does not exist" ));
	$gBitSystem->display("error.tpl");
	die;
} elseif ($gContent->mInfo['user_id'] != $gBitUser->mUserId && $gContent->mInfo['perm_level'] < FISHEYE_PERM_ADMIN) {
	// This user does not own this gallery and they have not been granted the permission to edit user permissions for this gallery
	$gBitSmarty->assign( 'msg', tra( "You cannot edit this image gallery" ) );
	$gBitSystem->display( "error.tpl" );
	die;
}

if (!empty($_REQUEST['submitNewPermissions'])) {
	$gContent->grantUserPermissions($_REQUEST['new_perm_user_id'], $_REQUEST['new_perm_level']);
	$fisheyeSuccess[] = $_REQUEST['found_username']." given ".$fisheyePermNameMap[$_REQUEST['name_perm_level']]." permissions";
}elseif (!empty($_REQUEST['remove_perm_user_id'])) {
	$gContent->revokeUserPermission($_REQUEST['remove_perm_user_id']);
	$fisheyeSuccess[] = tra("User permissions successfully revoked");
}

$userPerms = $gContent->loadPermissions();
$gBitSmarty->assign_by_ref('userPerms', $gContent->mPerms);

if (!empty($_REQUEST['submitUpdatePerms'])) {
	$existingPerms = $_REQUEST['existingPerms'];
	foreach ($userPerms as $userPerm) {
		if ($existingPerms[$userPerm['user_id']]['perm_level'] != $userPerm['perm_level']) {
			// Permisson level for this user has been altered
			$gContent->grantUserPermissions($userPerm['user_id'], $existingPerms[$userPerm['user_id']]);
			$fisheyeSuccess[] = $userPerm['real_name']." given ".$fisheyePermNameMap[$existingPerms[$userPerm['user_id']]]." permissions.";
		}
	}
	$userPerms = $gContent->getAllUserPermissions();
}

$gBitSystem->display('bitpackage:fisheye/edit_gallery_perms.tpl');

?>
