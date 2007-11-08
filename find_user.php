<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/find_user.php,v 1.6 2007/11/08 21:59:34 squareing Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
include_once( "../bit_setup_inc.php" );

if (empty($gBitThemes->mStyles['styleSheet'])) {
	$gBitThemes->mStyles['styleSheet'] = $gBitThemes->getStyleCss();
}
if( !defined( 'THEMES_STYLE_URL' ) ) {
	define( 'THEMES_STYLE_URL', $gBitThemes->getStyleUrl() );
}

if (!empty($_REQUEST['submitUserSearch'])) {
	$searchParams = array('find' => $_REQUEST['find']);
	$gBitUser->getList($searchParams);	
	$foundUsers = $searchParams['data'];
} else {
	$foundUsers = NULL;
}
$gBitSmarty->assign_by_ref('foundUsers', $foundUsers);

$gBitSmarty->display('bitpackage:fisheye/find_user.tpl');
?>
