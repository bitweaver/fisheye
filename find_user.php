<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/find_user.php,v 1.1.1.1.2.3 2005/08/05 22:59:52 squareing Exp $
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
include_once( "../bit_setup_inc.php" );

if (empty($gBitSystem->mStyles['styleSheet'])) {
	$gBitSystem->mStyles['styleSheet'] = $gBitSystem->getStyleCss();
}
$gBitSystem->mStyles['browserStyleSheet'] = $gBitSystem->getBrowserStyleCss();
$gBitSystem->mStyles['customStyleSheet'] = $gBitSystem->getCustomStyleCss();
if( !defined( 'THEMES_STYLE_URL' ) ) {
	define( 'THEMES_STYLE_URL', $gBitSystem->getStyleUrl() );
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
