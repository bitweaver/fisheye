# Fisheye gallery and image lifecycle

## Content classes

```text
LibertyMime
└── FisheyeBase
    ├── FisheyeGallery
    └── FisheyeImage
```

Both galleries and images are Liberty content. `FisheyeBase` supplies gallery
membership, paths, breadcrumbs, thumbnails, editability, and shared navigation.

Stable content type GUIDs are `fisheyegallery` and `fisheyeimage`.

## Tables

- `fisheye_gallery` — gallery-specific data keyed to Liberty content.
- `fisheye_image` — image dimensions/metadata keyed to Liberty content.
- `fisheye_gallery_image_map` — gallery/item membership and position.

File bytes and attachment metadata remain under Liberty storage/MIME handling.

## Upload flow

`includes/upload_inc.php` coordinates incoming files. A safe path:

1. Verify create/upload permission and destination gallery access.
2. Validate upload error, size, filename, MIME/content, and image readability.
3. Move/process from a controlled temporary directory.
4. Store the Liberty attachment and Fisheye image row.
5. Extract dimensions/metadata.
6. Add the image to authorized galleries.
7. Generate or queue thumbnails.
8. Clean temporary files on success and failure.

Never use the client filename as a storage path and never trust MIME headers
alone.

## Gallery membership

`FisheyeGallery::addItem()` and `removeItem()` manage mapping and ordering.
`FisheyeBase::addToGalleries()` supports multi-gallery placement.

Membership is not ownership: removing an image from a gallery does not normally
expunge the image content/file. Recursive gallery expunge is a distinct,
high-risk operation.

## Image processing

`FisheyeImage` provides:

- Dimension and metadata extraction.
- Orientation helpers.
- Rotation and colorspace conversion.
- Original resize.
- Thumbnail generation/rendering.
- Storage path/branch integration.

Processor availability and resource limits vary. Large/decompression-bomb
images can exhaust memory even when upload byte size is modest. Bound decoded
dimensions and processing work.

## Thumbnails

Gallery thumbnails can point to a selected image. Image thumbnails are derived
storage artifacts. Database identity, original attachment, and generated files
must remain distinguishable.

Regeneration should be repeatable and concurrency-safe. Do not delete originals
while cleaning thumbnail variants.

## Metadata

EXIF and other metadata can contain private location/device/user information.
Do not expose all extracted fields automatically. Normalize orientation before
assuming width/height semantics.

## Permissions

Check:

- Image/gallery Liberty view permission.
- Gallery edit permission for membership changes.
- Image update permission for transforms.
- Expunge permission for permanent deletion.

Lists and gallery navigation must not reveal protected member titles/counts.

## Expunge

`FisheyeImage::expunge()` coordinates domain row, mappings, Liberty attachment,
and content deletion. `FisheyeGallery::expunge()` can optionally recurse.
Partial failure can orphan mappings or storage; use established APIs and
transactions where supported.

## Testing

- Valid/invalid/non-image uploads and oversized dimensions.
- One image in multiple galleries.
- Gallery ordering and thumbnail selection.
- Rotate/resize/colorspace with each configured processor.
- Metadata privacy.
- Protected gallery/image list behavior.
- Nonrecursive versus recursive deletion.
