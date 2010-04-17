<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/templates/center_image_comments.php,v 1.3 2010/04/17 19:12:29 wjames5 Exp $
 * @package fisheye
 * @subpackage modules
 */

/**
 * A specialized version of liberty/modules/mod_last_comments.php for fisheye
 */
require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
global $gQueryUser, $gBitUser, $gLibertySystem, $moduleParams;
$params = $moduleParams['module_params'];
$moduleTitle = !empty($moduleParams['title'])? $moduleParams['title'] : 'Recent Image Comments';

$userId = NULL;
if( !empty( $gQueryUser->mUserId ) ) {
	$userId = $gQueryUser->mUserId;
}

$listHash = array(
	'user_id' => $userId,
	'max_records' => $moduleParams['module_rows'],
);

if (!empty($params['full'])) {
	$listHash['parse'] = TRUE;
}

if (!empty($params['pigeonholes'])) {
	$listHash['pigeonholes']['root_filter'] = $params['pigeonholes'];
}

if( !empty( $params['root_content_type_guid'] ) ) {
	if( empty($moduleTitle) && is_string( $params['root_content_type_guid'] ) ) {
		$moduleTitle = $gLibertySystem->getContentTypeName( $params['root_content_type_guid'] ).' '.tra( 'Comments' );
	}
	$listHash['root_content_type_guid'] = $params['root_content_type_guid'];
} else {
	// default to base image types
	$listHash['root_content_type_guid'] = array('fisheyeimage','fisheyegallery');
}
$gBitSmarty->assign( 'moduleTitle', $moduleTitle );

$lcom = new LibertyComment();
$modLastComments = $lcom->getList( $listHash );
$keys = array_keys( $modLastComments );
foreach( $keys as $k ) {
	$modLastComments[$k]['object'] = LibertyBase::getLibertyObject( $modLastComments[$k]['root_id'], $modLastComments[$k]['root_content_type_guid'] );
}
$gBitSmarty->assign( 'modLastComments', $modLastComments );
?>
