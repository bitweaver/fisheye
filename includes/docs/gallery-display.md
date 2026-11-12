# Gallery display

How a gallery page is listed and drawn. Request entry is `view.php`, which
includes `includes/display_fisheye_gallery_inc.php` and renders
`templates/view_gallery.tpl`.

## Display modes

A gallery has one of four display modes. The value is stored in the gallery
preference `gallery_pagination`; the site default is the config key
`default_gallery_pagination`.

| Mode | Constant | Purpose |
|---|---|---|
| `organize` | `FISHEYE_LAYOUT_ORGANIZE` | Whole, uncropped photos on a mat with names and frame numbers. For sorting and picking. |
| `justified` | `FISHEYE_LAYOUT_JUSTIFIED` | "Showcase". Full-width rows at true aspect ratio on the dark stage. |
| `masonry` | `FISHEYE_LAYOUT_MASONRY` | "Columns". For mixed portrait and landscape sets, on the dark stage. |
| `details` | `FISHEYE_LAYOUT_DETAILS` | Sortable rows: title, file, date taken, uploaded, dimensions, size. |

- `FisheyeGallery::getLayout()` always returns one of these keys.
  `FisheyeGallery::normalizeLayout()` maps the older stored values
  (`fixed_grid`, `auto_flow`, `position_number` to `organize`; `simple_list`
  to `details`; `matteo`, `galleriffic` to `justified`), so stored data needs
  no migration. An unset or unknown value is `justified`.
- Read the raw preference only when the legacy value itself matters
  (`image_order.tpl` does, for page breaks). Page breaks in the integer part
  of `item_position` are no longer drawn by the gallery view; order is kept.
- A viewer switches mode with `?mode=<key>`. The choice is kept in the
  session cookie `fisheye_mode` and applies to every gallery until the
  browser closes. It never changes the stored gallery default.

## Listing

`FisheyeGallery::getItemList( &$pListHash )` returns one page of the
gallery's images as plain row hashes from a single query plus a count. No
`FisheyeImage` object is loaded per image, so page cost does not grow with
gallery size. Use it for display. `loadImages()` still loads full objects
and remains for callers that need them.

- Input keys: `page`, `max_records` (default config
  `fisheye_gallery_page_size`, 60), `sort` (a key of
  `getItemSortOptions()`; unknown values fall back to gallery order),
  `thumbnail_size`.
- Set on return: `cant`, `num_pages`, `page` (clamped), `offset`.
- Row keys beyond the columns: `display_url`, `thumbnail_uri`,
  `thumbnail_pending` (no thumbnail on disk yet), `aspect` (width / height,
  1.5 when dimensions are missing), `number` (1-based position in the list).
- Sort fragments are a fixed whitelist. Never pass request text to
  `ORDER BY`.
- Each row costs a few `stat` calls to find its thumbnail. Keep pages
  bounded; do not call it with a huge `max_records`.
- Gatekeeper rows the viewer may not see are dropped after the page is
  read, so a page can be short. The count does not account for them.

`FisheyeGallery::getSubItems()` returns the galleries nested directly in the
gallery, also as plain rows: one query for the rows with item count and
cover id, one for the cover files. The cover is the gallery's
`preview_content_id`, or its first image. Do not load an object per nested
gallery; real accounts hold hundreds, and each load costs several queries.
The view folds the list after 24 entries.

## Library

The package front page (`index.php`, which includes `list_galleries.php`) is
the signed-in visitor's own library. `user_id=N` shows another owner's
galleries and `all=1` lists every owner's. An anonymous visitor with neither
is asked to sign in. Every "Galleries" link comes from
`FisheyeBase::getLibraryUrl()`, which gives the bare front page for the
viewer's own content and adds `user_id` for anyone else's.

It lists root galleries as cards with a
cover mosaic, rendered by `templates/library.tpl` and, for the paging
fragment, `templates/library_items_inc.tpl`. Parameters: `user_id`, `find`
(title search), `sort` (a key of `getLibrarySortOptions()`), `page`.

`FisheyeGallery::getLibraryList( &$pListHash )` does the listing in four
queries however many galleries there are: count, page of rows, item counts
for that page, covers for that page. Rules learned the hard way:

- Do not put per-gallery counts in the select list as subqueries. With no
  owner filter they run for every gallery before the sort and limit.
- Covers are ranked on the map table alone with `ROW_NUMBER()`, and the file
  rows are joined only for the few that are kept. The chosen
  `preview_content_id` ranks first, then gallery order.
- A gallery that holds only nested galleries has no cover and shows a folder.
- The unfiltered site-wide list is inherently slow on a large install (the
  root-gallery count and sort scan every gallery). Prefer `user_id`.

`getList()` is unchanged and still feeds `templates/list_galleries.tpl`,
which remains only for the centre modules (`center_list_galleries`,
`center_list_images`).

## Library rail

The gallery view carries a collapsible rail of the owner's root galleries
(`templates/library_rail_inc.tpl`, data from `getRailList()`, one query).
The gallery being viewed is marked. It is open by default on
the light modes and closed on the stage; `js/fisheye.js` remembers the
viewer's choice in `localStorage` (`fe-rail-light`, `fe-rail-stage`). It is
hidden at phone width, where the breadcrumb leads back to the library.

The rail is a tree (`templates/library_rail_items_inc.tpl`, which includes
itself for each level). A gallery that holds galleries gets a twist.

- On load the tree is opened along the path to the current gallery, and one
  level below it when that gallery holds 40 nested galleries or fewer.
  `loadRailChildren()` attaches those levels with one query per level.
- Any other item fetches its nested galleries the first time it is opened
  (`view.php?gallery_id=N&rail=1`, `getRailChildren()`), then just shows
  and hides them.
- The control in the Galleries heading fetches the whole tree once
  (`rail=all`) and opens every level; pressing it again closes every
  level. The whole tree is limited to 8 levels and about 3,000 rows.
- Without script the twist is a plain link to the gallery.

## Cost notes

- The `bit_short_date` modifier costs a few milliseconds per call on a
  PHP without native `strftime`. Details mode makes two calls per row, which
  is most of its render time. Do not add date columns to the other modes.
- The first view of a page of thumbnails is slower on network storage
  because of the `stat` calls; repeat views are fast.

## Paging

The page shows page 1 and a "More photos" link to `&page=N`. `js/fisheye.js`
turns the link into in-place loading: it requests the same URL with
`&fragment=1`, which returns only `templates/gallery_items_inc.tpl` for that
page, and appends it. Without script the link opens that page on its own.
Links are built by the address helpers, see Addresses below.

## Styling

`css/fisheye.css` holds the interface. Rules are scoped under `.fe-app`,
prefixed `fe-`, sized in px, and use no CSS framework classes, so the page
chrome's framework version does not matter. `.fe-app.fe-stage` is the dark
stage. The default colours are neutral. A theme brands the interface by
overriding the `--fe-*` custom properties, usually `--fe-accent` and
`--fe-select`, with a selector that outranks `.fe-app`, such as
`body .fe-app`, because the theme stylesheet loads before this one. The
dark stage keeps its own accent and selection colours. Do not put theme
colours in this file. Icons come
from the shared listing in the themes package docs (`icons.md`).

Every preview that crops (Library covers, nested gallery chips, Showcase
rows, Details thumbnails) is anchored a quarter of the way down with
`object-position:50% 25%`, so the crop takes more from the bottom than the top. Centre
cropping cut off heads in portraits and a top anchor framed them awkwardly. Keep that anchor on any new cropped
preview.
Library covers are the exception at `50% 20%`: their square crop of a
landscape lead photo read better a little higher.

Thumbnail sizes are chosen for 2x displays. Organize, Showcase and Columns
request `medium`, Details requests `avatar`, and the Library asks for
`medium` on the first cover of each card (`lead_thumbnail_size`) and `small`
on the rest. Tiles and rows are drawn at no more than about half those pixel
sizes. If a tile or row height grows, check it against the thumbnail size
before shipping.

The comment block is shared markup from the content package and themes give
`.comment .post` a fixed light ground. `.fe-comments` therefore sets ground
and ink together from the `--fe-*` properties, so comments stay readable on
the dark stage.

The gallery description keeps the author's line breaks (`white-space:
pre-line`) and is capped at 110 characters wide.

On the dark stage the kernel's content section (`#bw-main-content`) takes
the stage ground too, and the footer loses its top margin, through
`:has(.fe-app.fe-stage)`, so the page shows no
light border around the stage. The section is an ancestor and cannot read
properties set on `.fe-app`, so a theme that changes the stage ground sets
`--fe-stage-bg` on `:root` rather than `--fe-bg` on `.fe-app.fe-stage`.

Gallery tools (content service icons, Download, Image Order, Settings,
Upload) sit in `.fe-tools` at the end of the breadcrumb line (`.fe-top`), as
quiet muted links that take a ground only on hover. They are kept out of
`.fe-head` so the title and description have the full width. On phones the
labels hide and the icons remain, so every tool link carries a `title`.

## Photo view and lightbox

`view_image.php` and `templates/view_image.tpl` draw one photo on the dark
stage: a top line (back to the gallery, position, tools), the photo with
previous and next, a details panel, and a film strip of neighbours. Comments
and content services follow below the view on the page.

- `includes/display_fisheye_image_inc.php` builds `$feView`: `position` and
  `cant`, `prev_url` and `next_url`, the film strip `items`, `image_url`
  (the largest thumbnail that exists), the `sizes` links, and `page_url`.
- Neighbours come from `FisheyeGallery::getItemList()` with
  `around_content_id` and `span`. That adds one ranking query over the
  gallery and one page query; no object is loaded per neighbour.
- `?sort=` is honoured and handed on in every link, so previous and next
  follow the order the visitor chose on the gallery page. The gallery page
  appends it to item links when the order is not the gallery's own.
- `?fragment=1`, or any request marked `X-Requested-With: XMLHttpRequest`,
  returns the view alone, without comments or services.

The lightbox is that fragment inside a `<dialog>`. `js/fisheye.js` opens it
for a plain click on a photo in Showcase and Columns (`data-fe-lightbox` on
the items container); Organize and Details link to the photo page.
Modified clicks keep their browser meaning.

- Opening pushes one history entry. Moving between photos replaces it, so
  the address always names the photo on screen and Back returns to the
  gallery in one step. Closing the dialog goes back the same way.
- Arrow keys and a swipe move between photos on the page and in the
  lightbox. Slideshow advances every four seconds and survives page loads
  through `sessionStorage`; it stops at the last photo or on close.
- The details panel state is remembered in `localStorage` (`fe-info`).
- Script fetches send `X-Requested-With`, which also stops an optimising
  proxy from injecting its own script hooks into fragments.
- If a fetch fails, the script falls back to loading the photo page.

The old `templates/gallery_nav.tpl` has been removed.

## Organize tools

Editors of a gallery get these in Organize mode. Nothing is drawn for
visitors without update permission.

- **Select:** the round mark on a tile selects it. Once something is
  selected a click anywhere on a tile selects too, and shift extends from
  the last tile touched. Escape or Clear empties the selection.
- **Bulk bar** (`.fe-bulk`): Move, Copy, rotate left and right, Set as
  cover (one photo), Remove, Delete, Clear.
- **Rotate one photo:** each tile shows rotate left and right on hover
  (`.fe-turn`). They turn that photo alone and ignore the selection. A bulk
  rotate clears the selection when it finishes, so a second rotate cannot
  turn the same photos again by accident.
- **Reorder:** drag a tile, or a selection, onto another tile. The marker
  shows whether it lands before or after. Dragging is offered only while
  the gallery is shown in its own order (`data-fe-sortable`).
- **Photo view and lightbox:** rotate left and right, set as cover, and
  rename in place from the details panel.

All of them post to `organize.php`, which answers in JSON
(`ok`, `message`, plus action data). Every request needs the session
ticket (`tk`); the page carries it with the address and gallery id on the
element the tools belong to (`data-fe-url`, `data-fe-tk`,
`data-fe-gallery`).

| Action | Needs | Notes |
|---|---|---|
| `reorder` | update on the gallery | `items[]` with `before` or `after` (content ids); neither means the end |
| `cover` | update on the gallery | first item becomes the gallery preview |
| `remove` | update on the gallery | only items that are also in another gallery; the rest are kept and reported |
| `move`, `copy` | update on both galleries | `destination` is a gallery id |
| `rotate` | update on each photo | `degrees` 90, 180 or 270; up to 12 photos are redrawn at once, more go to the thumbnail queue |
| `delete` | update on each photo | removes the photo from every gallery |
| `rename` | update on the photo | `title`; the description is kept |
| `galleries` | signed in | the user's own galleries for the destination dialog, optional `find` |

Gallery actions ignore ids that are not items of the gallery.

`FisheyeGallery::moveItems()` does the reorder. After a drag it numbers the
whole gallery 10, 20, 30 in the new order, because other code reads these
positions as the gallery's hard order. Rows that already hold their number
are not written. Map rows whose content no longer exists are left alone.

Exact positions are set on the Image Order page, see below.

`js/fisheye.js` starts on `DOMContentLoaded` when it is loaded from the
document head. Controls that are looked up once (rail, paged loading,
organize) depend on that.

### Tile corners and the selection bar

- The corners of an Organize tile: top left the running number badge
  (`.fe-num`) with the favorite heart beside it, top right the select
  circle, bottom left the Cover tag, bottom right the buttons that act on
  that photo alone: use as cover (star), rotate left, rotate right.
- The heart (`data-fe-fav`) shows filled and red when the photo is one of
  the signed-in user's favorites, and on hover it is offered either way.
  A click posts the `favorite` action with `on` set or empty. The filled
  heart is an inline SVG in the stylesheet (`--fe-heart`), so it does not
  need a solid face in the icon font.
- Set as cover is no longer in the selection bar. The caption under the tile is the
  name alone. The drag script renumbers `.fe-num`.
- The selection bar reads: count, Clear beside it, a divider, the actions,
  then a help button. Actions show as icons with tooltips. The help button
  (`data-fe-labels`) adds the names by setting `has-labels` on the bar, and
  the choice is kept in `localStorage` as `fe-bulk-labels`.

## Image order

`image_order.php` is the place for exact ordering: a position number for
every image of a gallery. Other packages can rely on that order.

- **Rows:** one `getItemList()` call for the whole gallery (up to 5,000
  images). No object is loaded per image.
- **Each row:** drag handle, thumbnail, title, file name with dates, batch
  select, position. Dates are plain UTC text, because the date modifiers are
  too slow to run on every row.
- **Dragging a row** (handle, or the up and down arrows on a focused
  handle) moves the row and keeps the numbers where they are on the page:
  each row takes the number of the place it lands in. A row dragged from 40
  to 10 becomes 10, and the old 10, 20 and 30 become 20, 30 and 40. Empty
  boxes are first numbered on after the highest position, in tens. It uses
  pointer events, so it works with touch as well as a mouse.
- **Typing** a position is still possible; typed rows move when saved.
- **Bar:** re-order the whole gallery by photo date, upload date, title,
  file name or random (applied on save, replaces the boxes), and Renumber
  by tens (fills the boxes in the order shown).
- **Batch commands:** regenerate thumbnails, rename from file name (admins),
  grayscale, security. Move, copy, rotate, remove, delete and the gallery
  cover are in Organize mode.
- **Favorites:** a heart per row marks the signed-in user's favorites
  (`users_favorites_map`, the mark other packages read when they treat
  favorites specially). It is a checkbox named `is_favorite[]`, stored on
  save, and only rows whose mark changed are written.
- **Saving** needs the session ticket and writes only what changed:
  positions with one statement each, and titles through the image object.
  An empty position box leaves that image alone. Typed values are reduced
  to digits and one decimal point. Nothing is stored before Save.
- A form cut short by the server's field limit (`max_input_vars`) is
  refused instead of saved. Three fields go up per image.
- Galleries that still store the legacy `position_number` layout show a
  "Gallery page" divider wherever the whole part of the position changes.
- Drag reorder in Organize mode can leave in-between values such as 623.33.
  Renumber by tens makes them round again.

## Upload

`upload.php` and `templates/upload_fisheye.tpl` add photos to one gallery.

- **Target:** the gallery in the request, else the user's newest root
  gallery. With neither, the upload handler creates a first gallery. "Change
  gallery" opens the destination dialog (`organize.php`, action
  `galleries`). The form sends the target as `gallery_id` and
  `gallery_additions[]`.
- **Sending:** `js/fisheye.js` takes dropped or chosen files, sorts them by
  name, and posts them one at a time as `file0` with the rest of the form
  (options, service fields, ticket). One at a time keeps the gallery order
  and avoids waiting on the session lock. Each file has a row with size,
  progress and outcome.
- **Answer:** a request marked `X-Requested-With: XMLHttpRequest` gets JSON:
  `ok`, `errors`, and `uploaded` (image id, content id, title). A missing
  or wrong ticket answers 403, and a post that arrives without its file
  (larger than the server accepts) answers 413.
- **Before sending** the script skips empty files and files over the
  server's limit (`data-fe-max`, from `upload_max_filesize` and
  `post_max_size`).
- **Options** are folded away: turn photos upright, name photos after their
  files, unpack zip files, reduce originals. Fields added by content
  services are drawn in the same block and travel with every file.
- **Without script** a single file input and a submit button remain, and
  the handler redirects to the gallery as before.
- `on_complete=refreshparent` still reloads the opening window and closes
  the page once every file is in.
- The file input has no `name`, so the files are not sent a second time
  with the form fields.

When a tag inside `{strip}` is wrapped over several lines, start the
continuation lines with a space or keep the tag on one line: strip joins
the lines, and `hidden` followed by `data-x` becomes one broken attribute.

## Gallery picker

The dialog that chooses a gallery (move and copy in Organize, Change gallery
on Upload, Put inside a gallery on the settings form) is filled by the
`galleries` action of `organize.php`, which calls
`FisheyeGallery::getPickerList()`.

- Without a search the answer is the whole tree: top-level galleries by
  title, each with `children` in gallery order. It reuses the rail queries
  (`getRailList()`, `loadRailChildren()` with every level), so it costs one
  query per level and stops at the same depth and row limits.
- With a search the answer is a flat list of matching titles at any depth.
- `fillPicker()` in `js/fisheye.js` draws both shapes. A gallery that cannot
  be chosen is either left out with everything nested in it (settings, where
  choosing it would make a loop) or kept as a plain label so the galleries
  nested in it stay available (Organize, where moving photos into a nested
  gallery is common).

- Every picker ends with a New gallery row for users who may create
  galleries. It posts the `newgallery` action, which makes a top-level
  gallery with comments allowed, and then chooses that gallery as if its row
  had been clicked. In Organize that means Move or Copy runs at once.

## Gallery settings

`edit.php` with `templates/edit_gallery.tpl`. The same form creates a
gallery.

- Sections: Name, Opens in (the four display modes as radio cards), Cover,
  Inside, Sharing, and Delete outside the form.
- **Opens in** stores `gallery_pagination` only when the choice differs from
  what the gallery already resolves to. A gallery that still holds a legacy
  value keeps it until its owner picks another view.
- **Inside** lists the galleries this one sits in as ticked boxes named
  `gallery_additions[]`. More are added through the gallery picker. No tree
  of every gallery is built for the page. On save a new parent is refused
  when it is one of this gallery's own nested galleries
  (`getNestedGalleryIds()`), and the form is shown again with a warning.
- **Cover** only shows the current cover. It is chosen in Organize mode.
- Rows per page, columns per page and thumbnail size are no longer on the
  form, because no display mode reads them. `verifyGalleryData()` keeps the
  stored values on save, then falls back to the site default.
- `load()` used to read the site defaults for those columns from the
  gallery's own preferences, which never hold them. It now reads the site
  settings.
- Save and the confirmed delete check the session ticket.
- Service fields from other packages are included at the end of Sharing.
  When no package adds a tab, the empty tab strip is hidden by CSS.

## Site settings

`admin/admin_fisheye_inc.php` with `templates/admin_fisheye.tpl`, shown
inside the site administration pages, so it keeps the shared form helpers
instead of the `fe-` classes.

Only settings that code still reads are offered:

| Setting | Read by |
|---|---|
| `fisheye_menu_text` | site menu |
| `liberty_offline_thumbnailer` | thumbnail generation |
| `default_gallery_pagination` | `getLayout()`, the view a gallery opens in |
| `fisheye_gallery_page_size` | `getItemList()`, photos per page, 12 to 500, default 60 |
| `fisheye_gallery_default_sort_mode` | order of photos that share a position |
| `fisheye_gallery_hide_modules`, `fisheye_image_hide_modules` | `view.php`, `view_image.php` |
| `fisheye_image_default_thumbnail_size` | photo page and lightbox image size |
| `fisheye_list_*` (title, thumbnail, user, hits, created, lastmodif) | `list_galleries.tpl` through the center piece |

The form validates each choice against the options it offers. The default
view is stored only when it changes, for the same reason as on the gallery
form. Settings that were removed from the form are left in the database
untouched: the per-page counts and their mis-named copies, the galleriffic
style, the default and list thumbnail sizes, the title and description
toggles of the old gallery and image pages, the div layout switch, the
gallery bar switches, extended upload slots, and the two switches that
widened the gallery tree on the old edit forms (`fisheye_show_public_on_upload`,
`fisheye_show_all_to_admins`).

## Thumbnails still being made

- `image/generating_thumbnails.png` stands in for a thumbnail that does not
  exist yet. It is drawn from `image/generating_thumbnails.svg` with
  `rsvg-convert -w 400 -h 300`: transparent, one mid grey, no words, so it
  reads on the light ground and on the dark stage. Code recognises a pending
  thumbnail by this file name, so the name must stay.
- When the site queues thumbnails for the background thumbnailer
  (`liberty_offline_thumbnailer`), a photo uploaded on its own is still
  thumbnailed inside the upload request. The uploader sends `fe_pending`,
  the number of files still to go; `upload.php` sets `thumbnail_immediate`
  for the store when that is 1, and `FisheyeImage::store()` then takes the
  same path as a site without the background thumbnailer. Batches and zip
  contents stay queued. A posted `thumbnail_immediate` is discarded.

## Light and dark

- The dark stage is the class `fe-stage` on the page's `.fe-app` root. By
  default Showcase, Columns, the photo page and the lightbox are dark, and
  Organize and Details are light.
- A viewer can override that with the sun and moon toggle
  (`templates/theme_toggle_inc.tpl`) in the gallery bar and the photo view's
  top bar. The choice is the cookie `fisheye_theme` (`light` or `dark`), set
  for a year on the site root path, and applies to every gallery, photo page
  and lightbox. It is a viewer preference: nothing is stored on the gallery
  or the user.
- `FisheyeBase::getViewerTheme()` reads the cookie, or a `theme` request
  parameter that also sets it, so the toggle's links work without script.
  The page script sets the cookie itself and flips `fe-stage` on every
  `[data-fe-themed]` root and the lightbox without a reload.
- The rail's default (closed in Showcase and Columns) follows the display
  mode, not the theme.
- The Library, upload, image order and settings pages stay light.

## Photo details

`edit_image.php` with `templates/edit_image.tpl`, on the same form layout as
the gallery settings.

- Sections: Name, Photo, In galleries, More (service fields), and Delete
  outside the form.
- **Photo** is not form state. Rotate left, Rotate right, Refresh previews
  and Reduce the original are buttons that act at once through
  `organize.php` (`rotate`, `rethumb`, `resize`) and redraw the preview in
  place. Reduce accepts only the listed sizes, refuses a photo that is
  already that small, and asks first because it cannot be undone.
- **Replace file** is the one file action that goes through the form:
  choosing a file shows a confirmation with its name, and Replace now
  submits. Without the page script the file field is shown and Save sends
  it.
- The request parameters the old form posted (`rotate_image`, `resize`,
  `generate_thumbnails`) are still honoured by `edit_image.php`.
- **In galleries** lists the photo's galleries as ticked boxes named
  `gallery_additions[]` and adds more through the gallery picker, like
  Inside on the gallery settings. With nothing ticked the photo stays where
  it is.
- **Delete** is a red-text button at the right end of the form's foot,
  opposite Cancel. With the page script it asks in a dialog (`ask()` in
  `js/fisheye.js`, also used by the bulk delete and Reduce), posts the
  `delete` action and goes to the gallery. The trash icon in the photo view
  does the same. Without the script, or by address, `?delete=1` shows
  `templates/delete_image.tpl`, the package's own confirmation page.
- Save and the confirmed delete check the session ticket.
- The `ajax` and `ajax_edit` request branches and
  `templates/edit_image_inc.tpl` are left as they were; nothing in this
  package calls them any more.

## Timelines (meta galleries)

A meta gallery is a query, not stored rows: every photo one user owns,
newest first, whichever galleries the photos are in. Nothing is stored for
it. The class is `FisheyeMetaGallery` (extends `FisheyeGallery`), the page is
`timeline.php`, the templates are `timeline.tpl` and
`timeline_items_inc.tpl`.

| Type | Column of `liberty_content` | Address |
|---|---|---|
| Date taken | `event_time` | `timeline.php?by=taken` |
| Date uploaded | `created` | `timeline.php?by=uploaded` |

- **Ids.** A meta gallery has a string id: `meta-taken`, `meta-taken-2026`,
  `meta-taken-2026-06`, `meta-taken-2026-06-14`, `meta-taken-none`, and the
  same under `meta-uploaded`. `parseId()` and `lookupMeta()` read them.
  On the page the part after the type is the `in` parameter.
- **No date taken.** Photos whose `event_time` is empty or zero are left
  out of the Date taken timeline. They are listed by `in=none`, in upload
  order, and the page links to them with a count.
- **Who sees it.** Only the owner of the photos and holders of
  `p_fisheye_admin` (`canView()`). A timeline crosses every gallery, so
  gallery permissions cannot answer for it. Administrators pass `user_id`.
- **Headings.** Year, month or day (`group`), whichever are finer than the
  scope; month is the default. Days and months follow the server time zone
  and are counted in PHP from one query of the owner's dates
  (`getBuckets()`), so no database date function is needed. A heading links
  to that scope. The year list at the side links to each year.
- **Paging.** Pages are counted in photos, not headings. The "more"
  fragment leaves out the first heading when the page before ended in the
  same one (`getBucketBefore()`).
- **Photo view.** Item links carry `meta=<id without the prefix>`.
  `image_lookup_inc.php` then makes the meta gallery the photo's gallery, so
  previous, next, the count and the back link follow the timeline. A meta
  gallery reports no update permission, no comments and no preferences.
- **Entry points.** "Browse by" in the library rail, and two buttons on the
  owner's own Library page.

### How it shares the item list

`FisheyeGallery::getItemList()` builds its query from four protected
methods, which `FisheyeMetaGallery` overrides:

| Method | Stored gallery | Meta gallery |
|---|---|---|
| `getItemListParts()` | map rows of the gallery | the owner's photo content rows |
| `getItemPosition()` | `ROW_NUMBER()` over the gallery | a count of the photos ahead of the item |
| `decorateItemRow()` | gallery path and photo address | photo address with `meta=`, and `sort_time` |
| `markItemFavorites()` | join on the gallery map | the page's ids |

When the parts set `plain_set`, the page's ids are read from
`liberty_content` alone and the image and file joins run only for those
ids. Without that a 40,000 photo owner joins every row before the limit.

### Cost, measured on a 58,000 photo owner

No index was added. Each query reads the owner's rows through the existing
user index and takes 20 to 40 ms: the count, the page of ids, the dates for
the headings, and the position of one photo.

Two plans went wrong on large owners and are held in place in the SQL:

- The timeline order is written `column + 0`. With the bare column the
  database walked the site-wide `created` index backwards and discarded
  every other user's rows (0.3 to 1.8 s).
- `getRailList()` tests `LOWER( content_type_guid )`. Without the test the
  rail query read every gallery on the site for such an owner (1.9 s, now
  45 ms). The bare column would bring in the type index, which is worse.

## Service locations

Places where other packages can add markup to the gallery and photo pages.
How locations work: `liberty/includes/docs/services-and-plugins.md`.

| Page | Location | Where it lands |
|---|---|---|
| Gallery (`view_gallery.tpl`) | `icon` | First in `.fe-tools`, the quiet tools on the breadcrumb line. A provider prints one `<a>` holding a `{booticon}` and a `<span class="l">` label, like its neighbours |
| Gallery | `action` | Beside the title, in `.fe-actions` of the `.fe-head` row. A provider prints one `<a class="fe-btn fe-btn-primary">` (or `fe-btn`). The `.fe-actions` element is only drawn when a provider printed something |
| Gallery | `nav`, `body` | Under the title and description, above the photos |
| Gallery | `view` | After the photos, before the comments |
| Photo (`view_image.tpl`) | `icon` | In the tools of the top line; not in the lightbox fragment |
| Photo | `nav`, `body`, `view` | Below the photo, before the comments; not in the lightbox fragment |

The library's gallery cards and the timeline pages offer no location.
`.fe-tools a` and `.fe-btn` are styled by `fisheye.css`, so a provider's link
in `icon` or `action` needs no styles of its own.

## Addresses

With the `pretty_urls` feature on, no link, redirect, form action or script
endpoint of the package carries `.php`. The package `.htaccess` maps a name
to its script, `gallery/<path>/<id>` to `view.php` and `image/<id>/<path>`
to `view_image.php`. With the feature off the same helpers give the plain
script addresses, so templates never choose.

| Helper (`includes/bit_setup_inc.php`) | Pretty | Plain |
|---|---|---|
| `fisheye_page_url( 'upload', array( 'gallery_id' => 5 ) )` | `upload?gallery_id=5` | `upload.php?gallery_id=5` |
| `fisheye_gallery_url( 5, array( 'mode' => 'organize' ) )` | `gallery/5?mode=organize` | `view.php?gallery_id=5&mode=organize` |
| `fisheye_gallery_url( '' )` | `gallery/` | `view.php?gallery_id=` |

- Templates use `{feurl page="upload"}` and
  `{feurl gallery=$id mode="organize"}`. The result is escaped. A query
  written after `{feurl page="x"}` in the template is kept as is.
  `{feurl page="edit" assign="feAction"}` hands the address to `{form
  action=$feAction}`, so a form posts back to the address the page was
  opened by.
- `$feBaseUrl` on the gallery page ends in `?` or `&`, ready for a
  parameter to be appended.
- A `gallery_path` parameter becomes part of a pretty gallery address.
- The gallery sort form sends `gallery_id` only for plain addresses, because
  a get form drops the query of its action.
- The old script addresses still answer. Nothing redirects them.

