<?php
/**
 * @version $Header$
 * @package fisheye
 * @subpackage functions
 */

/**
 * required setup
 */
include_once( "../kernel/setup_inc.php" );

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
$gBitSmarty->assignByRef('foundUsers', $foundUsers);

$gBitSmarty->display('bitpackage:fisheye/find_user.tpl');
?>
