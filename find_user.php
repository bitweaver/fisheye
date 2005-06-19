<?php
include_once( "../bit_setup_inc.php" );

if (empty($gBitLoc['styleSheet'])) {
	$gBitLoc['styleSheet'] = $gBitSystem->getStyleCss();
}
$gBitLoc['browserStyleSheet'] = $gBitSystem->getBrowserStyleCss();
$gBitLoc['customStyleSheet'] = $gBitSystem->getCustomStyleCss();
$gBitLoc['THEMES_STYLE_URL'] = $gBitSystem->getStyleUrl();

if (!empty($_REQUEST['submitUserSearch'])) {
	$searchParams = array('find' => $_REQUEST['find']);
	$gBitUser->getList($searchParams);	
	$foundUsers = $searchParams['data'];
} else {
	$foundUsers = NULL;
}
$smarty->assign_by_ref('foundUsers', $foundUsers);

$smarty->display('bitpackage:fisheye/find_user.tpl');
?>
