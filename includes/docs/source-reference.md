# Fisheye source reference

> Generated from the current checkout and then intended for human review.
> Paths are relative to the package root.

## Inventory summary

| Artifact | Count |
|---|---:|
| PHP files | 38 |
| Smarty templates | 39 |
| JavaScript files | 8 |
| CSS files | 13 |

## Bootstrap and schema artifacts

- `admin/schema_inc.php`
- `admin/upgrade_inc.php`
- `includes/bit_setup_inc.php`

## First-party classes and interfaces

- `includes/classes/FisheyeBase.php:14` — `abstract class FisheyeBase extends LibertyMime`
- `includes/classes/FisheyeGallery.php:25` — `class FisheyeGallery extends FisheyeBase {`
- `includes/classes/FisheyeMetaGallery.php:23` — `class FisheyeMetaGallery extends FisheyeGallery {`
- `includes/classes/FisheyeImage.php:18` — `class FisheyeImage extends FisheyeBase {`
- `includes/classes/FisheyeRemote.php:50` — `class FisheyeRemote {`

## Web-facing PHP controllers

- `admin/admin_fisheye_inc.php`
- `admin/schema_inc.php`
- `admin/upgrade_inc.php`
- `edit.php`
- `edit_image.php`
- `fisheye_rss.php`
- `image_order.php`
- `index.php`
- `list_galleries.php`
- `main.php`
- `organize.php`
- `modules/mod_banner_rand.php`
- `modules/mod_images.php`
- `modules/mod_specials.php`
- `thumbnailer.php`
- `timeline.php`
- `upload.php`
- `view.php`
- `view_image.php`
- `view_image_details.php`

## Declared schema tables

- `fisheye_gallery`
- `fisheye_gallery_image_map`
- `fisheye_image`

## Plugin and module directories

- `liberty_plugins/`
- `modules/`

## Templates

- `modules/help_mod_images.tpl`
- `modules/mod_banner_rand.tpl`
- `modules/mod_images.tpl`
- `modules/mod_navigation.tpl`
- `modules/mod_specials.tpl`
- `templates/admin_fisheye.tpl`
- `templates/center_image_comments.tpl`
- `templates/center_list_galleries.tpl`
- `templates/center_list_images.tpl`
- `templates/edit_gallery.tpl`
- `templates/edit_image.tpl`
- `templates/edit_image_inc.tpl`
- `templates/gallery_icons_inc.tpl`
- `templates/html_head_inc.tpl`
- `templates/image_order.tpl`
- `templates/list_galleries.tpl`
- `templates/menu_fisheye.tpl`
- `templates/menu_fisheye_admin.tpl`
- `templates/resize_image_select.tpl`
- `templates/upload_fisheye.tpl`
- `templates/user_galleries.tpl`
- `templates/view_gallery.tpl`
- `templates/view_image.tpl`
- `templates/view_image_details.tpl`

## Reading cautions

- Presence in this inventory does not make a file a supported public API.
- Bundled third-party libraries must be distinguished from package-owned code.
- Base schema files do not prove the migration state of a deployed database.
- Controllers may rely on include files, globals, services, and template callbacks not visible from their filename alone.
