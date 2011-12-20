<?php

$tables = array(

'fisheye_gallery' => "
	gallery_id I4 PRIMARY,
	content_id I4,
	rows_per_page I4,
	cols_per_page I4,
	thumbnail_size C(32),
	preview_content_id I4,
	image_comment C(1)
",

'fisheye_gallery_image_map' => "
	gallery_content_id I4 NOTNULL,
	item_content_id I4 NOTNULL,
	item_position F
",

'fisheye_image' => "
	image_id I4 PRIMARY,
	content_id I4 NOTNULL,
	photo_date I8,
	width I4,
	height I4
",
);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( FISHEYE_PKG_NAME, $tableName, $tables[$tableName] );
}

$indices = array (
	'fisheye_gallery_id_idx' => array( 'table' => 'fisheye_gallery', 'cols' => 'gallery_id', 'opts' => NULL ),
	'fisheye_gallery_content_idx' => array( 'table' => 'fisheye_gallery', 'cols' => 'content_id', 'opts' => array( 'UNIQUE' ) ),
	'fisheye_image_id_idx' => array( 'table' => 'fisheye_image', 'cols' => 'image_id', 'opts' => NULL ),
	'fisheye_image_content_idx' => array( 'table' => 'fisheye_image', 'cols' => 'content_id', 'opts' => array( 'UNIQUE' ) ),
);
$gBitInstaller->registerSchemaIndexes( FISHEYE_PKG_NAME, $indices );

$gBitInstaller->registerPackageInfo( FISHEYE_PKG_NAME, array(
	'description' => "FishEye is a package for creating image galleries",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>'
) );

// ### Sequences
$sequences = array (
	'fisheye_gallery_id_seq' => array( 'start' => 1 ),
	'fisheye_image_id_seq' => array( 'start' => 1 )
);
$gBitInstaller->registerSchemaSequences( FISHEYE_PKG_NAME, $sequences );

// ### Default Preferences
$gBitInstaller->registerPreferences( FISHEYE_PKG_NAME, array(
	array( FISHEYE_PKG_NAME, 'fisheye_list_title','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_list_created','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_list_user','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_list_hits','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_list_thumbnail','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_list_thumbnail_size','small'),
	array( FISHEYE_PKG_NAME, 'fisheye_gallery_list_title','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_gallery_list_description','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_gallery_list_image_titles','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_gallery_default_rows_per_page','5'),
	array( FISHEYE_PKG_NAME, 'fisheye_gallery_default_cols_per_page','3'),
	array( FISHEYE_PKG_NAME, 'fisheye_gallery_default_thumbnail_size','small'),
	array( FISHEYE_PKG_NAME, 'fisheye_image_list_title','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_image_list_description','y'),
	array( FISHEYE_PKG_NAME, 'fisheye_image_default_thumbnail_size','medium'),
	array( FISHEYE_PKG_NAME, 'fisheye_menu_text','Image Galleries'),
	// more intuitive if we can see all galleries we can upload images to
	array( FISHEYE_PKG_NAME, 'fisheye_show_public_on_upload','n'),
    array( FISHEYE_PKG_NAME, 'fisheye_show_all_to_admins','n'),
) );

// ### Default User Permissions
$gBitInstaller->registerUserPermissions( FISHEYE_PKG_NAME, array(
	array('p_fisheye_view', 'Can view image galleries', 'basic', FISHEYE_PKG_NAME),
	array('p_fisheye_create', 'Can create an image gallery', 'registered', FISHEYE_PKG_NAME),
	array('p_fisheye_update', 'Can update image gallery', 'editors', FISHEYE_PKG_NAME),
	array('p_fisheye_upload', 'Can upload images to gallery', 'registered', FISHEYE_PKG_NAME),
	array('p_fisheye_admin', 'Can admin image galleries', 'editors', FISHEYE_PKG_NAME),
	array('p_fisheye_upload_nonimages', 'Can upload non_image files', 'editors', FISHEYE_PKG_NAME),
	array('p_fisheye_change_thumb_size', 'Can set the thumbnail size for a gallery', 'editors', FISHEYE_PKG_NAME),
	array('p_fisheye_create_public_gal', 'Can create public galleries any user can load images into', 'editors', FISHEYE_PKG_NAME),
	array('p_fisheye_download_gallery_arc',' Can download an archived copy of Fisheye gallery', 'basic', FISHEYE_PKG_NAME)
) );

if( defined( 'RSS_PKG_NAME' )) {
	$gBitInstaller->registerPreferences( FISHEYE_PKG_NAME, array(
		array( RSS_PKG_NAME, FISHEYE_PKG_NAME.'_rss', 'y'),
	));
}

// ### Register content types
$gBitInstaller->registerContentObjects( FISHEYE_PKG_NAME, array( 
	'FisheyeGallery'=>FISHEYE_PKG_PATH.'FisheyeGallery.php',
	'FisheyeImage'=>FISHEYE_PKG_PATH.'FisheyeImage.php',
));

// Requirements
$gBitInstaller->registerRequirements( FISHEYE_PKG_NAME, array(
    'liberty' => array( 'min' => '2.1.4' ),
));

