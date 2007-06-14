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
		'tiki_thumbnail_queue' => 'liberty_process_queue',
	)),
	array( 'RENAMESEQUENCE' => array(
		"tiki_fisheye_gallery_id_seq" => "fisheye_gallery_id_seq",
	)),
	array('ALTER'=> array(
		'fisheye_gallery_image_map' => array(
			'item_position' => array( '`item_position`', 'F' ),
		),
	)),
)),

// Queries
array( 'QUERY' =>
	array( 'SQL92' => array(
	// Copy int positions to floats
	"UPDATE `".BIT_DB_PREFIX."fisheye_gallery_image_map` SET `item_position`=`position`",
	),
)),

// DataDict cleanup
array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'fisheye_gallery_image_map' => array( '`position`' ),
	)),
	array( 'CREATEINDEX' => array(
		'fisheye_gallery_image_map_pos_idx' => array( 'fisheye_gallery_image_map', '`gallery_content_id`,`item_position`', array( 'UNIQUE' ) ),
	)),
)),

		)
	),
);

if( isset( $upgrades[$gUpgradeFrom][$gUpgradeTo] ) ) {
	$gBitSystem->registerUpgrade( FISHEYE_PKG_NAME, $upgrades[$gUpgradeFrom][$gUpgradeTo] );
}
?>
