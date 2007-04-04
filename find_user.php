<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/find_user.php,v 1.5 2007/04/04 14:31:30 squareing Exp $
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
$gBitThemes->mStyles['browserStyleSheet'] = $gBitThemes->getBrowserStyleCss();
$gBitThemes->mStyles['customStyleSheet'] = $gBitThemes->getCustomStyleCss();
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
