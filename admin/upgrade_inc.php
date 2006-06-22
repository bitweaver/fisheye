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
	array( 'RENAMECOLUMN' => array(
		'fisheye_gallery_image_map' => array(
			'`position`' => '`item_position` I4'
		),
	)),
	array('ALTER'=> array(
		'fisheye_gallery' => array(
			'image_comment' => array( '`image_comment`', 'C(1)' ), // , 'NULL' ),
		),
	)),
)),
		)
	),
);

if( isset( $upgrades[$gUpgradeFrom][$gUpgradeTo] ) ) {
	$gBitSystem->registerUpgrade( FISHEYE_PKG_NAME, $upgrades[$gUpgradeFrom][$gUpgradeTo] );
}
?>
