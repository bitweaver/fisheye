<?php

global $gBitSystem, $gUpgradeFrom, $gUpgradeTo;

$upgrades = array(

	'BWR1' => array(
		'BWR2' => array(
// de-tikify tables
array( 'DATADICT' => array(
	array( 'RENAMETABLE' => array(
		'tiki_fisheye_gallery' => 'fisheye_gallery',
		'tiki_fisheye_gallery_image_map' => 'fisheye_gallery_image_map',
		'tiki_fisheye_image' => 'fisheye_image',
		'tiki_thumbnail_queue' => 'liberty_thumbnail_queue',
	)),
	array( 'RENAMESEQUENCE' => array(
		"tiki_fisheye_gallery_id_seq" => "fisheye_gallery_id_seq",
	)),
)),
		)
	),
);

if( isset( $upgrades[$gUpgradeFrom][$gUpgradeTo] ) ) {
	$gBitSystem->registerUpgrade( FISHEYE_PKG_NAME, $upgrades[$gUpgradeFrom][$gUpgradeTo] );
}
?>
