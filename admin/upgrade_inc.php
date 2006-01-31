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
)),
array( 'PHP' => '
	global $gBitSystem;
	$current = $gBitSystem->mDb->GenID( "tiki_fisheye_gallery_id_seq" );
	$gBitSystem->mDb->DropSequence( "tiki_fisheye_gallery_id_seq");
	$gBitSystem->mDb->CreateSequence( "fisheye_gallery_id_seq", $current );
' ),

		)
	),
);

if( isset( $upgrades[$gUpgradeFrom][$gUpgradeTo] ) ) {
	$gBitSystem->registerUpgrade( FISHEYE_PKG_NAME, $upgrades[$gUpgradeFrom][$gUpgradeTo] );
}
?>
