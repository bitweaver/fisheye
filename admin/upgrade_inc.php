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

// query: create a fisheye_image_id_seq and bring the table up to date with the current max image_id used in the fisheye_image table - this basically for mysql
array( 'PHP' => '
	$query = $gBitDb->getOne("SELECT MAX(image_id) FROM `'.BIT_DB_PREFIX.'fisheye_image`");
	$tempId = $gBitDb->mDb->GenID("`'.BIT_DB_PREFIX.'fisheye_image_id_seq`", $query);
' ),

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
