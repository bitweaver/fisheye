<?php
//This holds the checkbox options for what to display on the 'list galleries' page
$formGalleryGeneral = array(
	"fisheye_menu_text" => array(
		'label' => 'Menu Text',
		'note' => '',
		'type' => 'text'
	),
/* Disabled for now - spiderr
	"feature_megaupload" => array(
		'label' => 'Use <a href="http://sourceforge.net/projects/megaupload">MegaUpload</a>',
		'note' => 'Upload progress meter that requires Perl and ExecCGI permission',
		'type' => 'checkbox'
	),
*/
	"feature_offline_thumbnailer" => array(
		'label' => 'Background Thumbnailer',
		'note' => 'Thumbnails will be queued and regenerated by a background command-line script. For more information, see '.FISHEYE_PKG_PATH.'thumbaniler.php or you can <a href="'.FISHEYE_PKG_URL.'thumbnailer.php">run it manually</a>',
		'type' => 'checkbox'
	)
);
$gBitSmarty->assign('formGalleryGeneral', $formGalleryGeneral);

$formGalleryListLists = array(
	"fisheye_list_title" => array(
		'label' => 'Gallery title',
		'note' => 'List the title of the gallery.',
	),
	"fisheye_list_thumbnail" => array(
		'label' => 'Thumbnail',
		'note' => 'Display a small thumbnail associated with a gallery',
	),
	"fisheye_list_description" => array(
		'label' => 'Description',
		'note' => 'List the description of a gallery',
	),
	"fisheye_list_user" => array(
		'label' => 'Creator',
		'note' => 'List the name of the user who created the gallery',
	),
	"fisheye_list_hits" => array(
		'label' => 'Hits',
		'note' => 'List number of hits this gallery has receieved',
	),
	"fisheye_list_created" => array(
		'label' => 'Creation date',
		'note' => 'List the creation date of the gallery',
	),
	"fisheye_list_lastmodif" => array(
		'label' => 'Last modification',
		'note' => 'List date this gallery was last modified',
	)
);
$gBitSmarty->assign('formGalleryListLists', $formGalleryListLists);

// This holds the checkbox options for what to display on a 'view gallery' page
$formGalleryLists = array(
	"fisheye_gallery_list_title" => array(
		'label' => 'Gallery title',
		'note' => 'When viewing a gallery, display the title of the gallery',
	),
	"fisheye_gallery_list_description" => array(
		'label' => 'Gallery description',
		'note' => 'When viewing a gallery, display the description of the gallery below the title',
	),
	"fisheye_gallery_list_image_titles" => array(
		'label' => 'Image titles',
		'note' => 'Show image titles underneath each thumbnail',
	),
	"fisheye_gallery_hide_modules" => array(
		'label' => 'Hide modules for galleries',
		'note' => 'When viewing a gallery, hide the left and right module columns',
	),
	"fisheye_gallery_list_image_descriptions" => array(
		'label' => 'Image description',
		'note' => 'Show image descriptions underneath each thumbnail',
	),
	"fisheye_gallery_div_layout" => array(
		'label' => '&lt;div&gt; based Layout',
		'note' => 'You can use a &lt;div&gt; based layout, which will adjust the number of images in each row to the width of the browser. Please visit the online help for more information.',
		'page' => 'FisheyePackage',
	)
);
$gBitSmarty->assign( 'formGalleryLists',$formGalleryLists );

// This holds the checkbox options for what to display on an 'image details' page
$formImageLists = array(
	"fisheye_image_list_title" => array(
		'label' => 'Image title',
		'note' => 'When viewing an image, display the title of the image',
	),
	"fisheye_image_list_description" => array(
		'label' => 'Image description',
		'note' => 'When viewing an image, display the description of the image below the title',
	),
	"fisheye_image_hide_modules" => array(
		'label' => 'Hide modules for images',
		'note' => 'When viewing an image, hide the left and right module columns',
	),
	"gallerybar_use_icons" => array(
		'label' => 'Use icons in the gallery bar',
		'note' => 'When viewing an image, show <strong>previous</strong> and <strong>next</strong> links as images instead of words',
	),
	"gallery_bar_use_thumbnails" => array(
		'label' => 'Use Thumbnails in gallery bar',
		'note' => 'When viewing an image, show previous and next <strong>thumbnails</strong> with the appropriate links.',
	),
);
$gBitSmarty->assign( 'formImageLists', $formImageLists);

$imageSizes = array(
	'avatar' => tra( 'Avatar ( 100 x 75 pixels )' ),
	'small'  => tra( 'Small ( 160 x 120 pixels )' ),
	'medium' => tra( 'Medium ( 400 x 300 pixels )' ),
	'large'  => tra( 'Large ( 800 x 600 pixels )' ),
);
$gBitSmarty->assign( 'imageSizes', $imageSizes );

//vd($_REQUEST);
if (!empty($_REQUEST['fisheyeAdminSubmit'])) {
	// General Settings
	foreach ($formGalleryGeneral as $item=>$data) {
		if( $data['type'] == 'checkbox' ) {
			simple_set_toggle($item, FISHEYE_PKG_NAME);
		} else {
			$gBitSystem->storePreference($item, $_REQUEST[$item], FISHEYE_PKG_NAME );
		}
	}

	// Gallery List Display Settings
	foreach ($formGalleryListLists as $item=>$data) {
		simple_set_toggle($item, FISHEYE_PKG_NAME);
	}

	// Gallery Display Settings
	foreach ($formGalleryLists as $item => $data) {
		simple_set_toggle($item, FISHEYE_PKG_NAME);
	}
	$gBitSystem->storePreference('fisheye_list_thumbnail_size', $_REQUEST['list_thumbnail_size'], FISHEYE_PKG_NAME);
	$gBitSystem->storePreference('fisheye_gallery_default_thumbnail_size', $_REQUEST['default_gallery_thumbnail_size'], FISHEYE_PKG_NAME);
	$gBitSystem->storePreference('fisheye_gallery_default_rows_per_page', $_REQUEST['rows_per_page'], FISHEYE_PKG_NAME);
	$gBitSystem->storePreference('fisheye_gallery_default_cols_per_page', $_REQUEST['cols_per_page'], FISHEYE_PKG_NAME);

	// Image Display Settings
	foreach ($formImageLists as $item => $data) {
		simple_set_toggle( $item, FISHEYE_PKG_NAME );
	}
	if( !empty( $_REQUEST['default_image_thumbnail_size'] ) ) {
		$gBitSystem->storePreference('fisheye_image_default_thumbnail_size', $_REQUEST['default_image_thumbnail_size'], FISHEYE_PKG_NAME );
	}

}

?>
