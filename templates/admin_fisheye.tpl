{literal}
<script type="text/javascript">//<![CDATA[
function updateGalleryPagination() {
	BitBase.hideById('fixed_grid-pagination');
	BitBase.hideById('auto_flow-pagination');
	BitBase.hideById('position_number-pagination');
	BitBase.hideById('simple_list-pagination');
	BitBase.hideById('matteo-pagination');
	BitBase.hideById('galleriffic-pagination');

	var input = document.getElementById('gallery-pagination');
    var i  = input.selectedIndex;
    var select = input.options[i].value;
	BitBase.showById(select+'-pagination');
}
//]]></script>
{/literal}
{strip}

{form}
	{jstabs}
		{jstab title="Settings"}
			{legend legend="General Settings"}
				<div class="row">
					{formhelp note="To change the Image Processing engine, you need to change the setting in Liberty Settings" link="kernel/admin/index.php?page=liberty/Liberty Settings"}
				</div>

				{foreach from=$formGalleryGeneral key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{if $output.type eq 'text'}
								<input type="text" name="{$item}" id="{$item}" value="{$gBitSystem->getConfig($item)}"/>
							{elseif $output.type=='checkbox'}
								{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{/if}
							{formhelp note=`$output.note`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="List"}
			{legend legend="Gallery List Options"}
				<div class="row">
					{formhelp note="The options below determine what information is shown on the List Galleries page."}
				</div>

				{foreach from=$formGalleryListLists key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{formhelp note=`$output.note`}
						{/forminput}
					</div>
				{/foreach}

				<div class="row">
					{formlabel label="List Thumbnail Size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="list_thumbnail_size" selected=$gBitSystem->getConfig('fisheye_list_thumbnail_size')}
					{/forminput}
				</div>
			{/legend}
		{/jstab}

		{jstab title="Galleries"}
			{legend legend="Gallery Display Settings"}
				<input type="hidden" name="page" value="{$page}" />
				<div class="row">
					{formhelp note="The options below determine what information is shown on a gallery display page."}
				</div>

				{foreach from=$formGalleryLists key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}

				<div class="row">
					{formlabel label="Default sort order" for="fisheye_gallery_default_sort_mode"}
					{forminput}
						{html_options values=$sortOptions options=$sortOptions name="fisheye_gallery_default_sort_mode" id="fisheye_gallery_default_sort_mode" selected=$gBitSystem->getConfig('fisheye_gallery_default_sort_mode')}
						{formhelp note="This is the order of the images if they have not been sorted manually i.e. all the images with the same position value."}
					{/forminput}
				</div>
			{/legend}

			{legend legend="Default Gallery Display Settings"}
				<div class="row">
					{formhelp note="The settings below determine what the default display options will be set to for new galleries."}
				</div>

				<div class="row">
					{html_options id="gallery-pagination" name="default_gallery_pagination" id="gallery-pagination" options=$galleryPaginationTypes selected=$gBitSystem->getConfig('default_gallery_pagination',$smarty.const.FISHEYE_PAGINATION_GALLERIFFIC) onchange="updateGalleryPagination();"}

					<div id="fixed_grid-pagination">
						<input type="text" id="gallery-rows-per-page" name="rows_per_page" size="2" maxlength="2" value="{$gContent->mInfo.rows_per_page|default:$gBitSystem->getConfig('fisheye_gallery_default_rows_per_page')}"/> {tr}Rows per page{/tr}<br/>
						<input type="text" id="gallery-cols-per-page" name="cols_per_page" size="2" maxlength="2" value="{$gContent->mInfo.cols_per_page|default:$gBitSystem->getConfig('fisheye_gallery_default_cols_per_page')}"/> {tr}Columns per page{/tr}
						{formhelp note="The images will be displayed in a fixed grid. You can specify the number of thumbnails to display per page.<br /><strong>[rows] * [columns] = [number of images]</strong>."}
					</div>
					<div id="auto_flow-pagination">
						<input type="text" id="gallery-rows-per-page" name="total_per_page" size="2" maxlength="2" value="{$gBitSystem->getConfig('fisheye_gallery_default_rows_per_page')}"/> {tr}Total images per page{/tr}
						{formhelp note="The layout of the images on each gallery page will automatically adjust to the browsers width. You can specify the total number of thumbnails to display per page."}
					</div>
					<div id="position_number-pagination">
						{formhelp note="This option allows you to designate each specific image on each page. The image order number entered on the Image Order page will determine the exact location of each image. Fractional numbers indicate PAGE.POSITION and will specifiy variable images per page, such as: 1.1, 1.2, 2.1, 3.1, 3.2, 3.3"}
					</div>
					<div id="simple_list-pagination">
						<input type="text" id="gallery-rows-per-page" name="lines_per_page" size="2" maxlength="2" value="{$gBitSystem->getConfig('fisheye_gallery_default_rows_per_page')}"/> {tr}Total lines per page{/tr}
						{formhelp note="This option allows a single column display of images with mime details where available."}
					</div>
					<div id="matteo-pagination">
						<input type="text" id="gallery-rows-per-page" name="images_per_page" size="2" maxlength="2" value="{$gBitSystem->getConfig('fisheye_gallery_default_rows_per_page')}"/> {tr}Total images per page{/tr}
						{formhelp note="This option provides an ajax powered scrolling display using the mbGallery jquery library."}
					</div>
					<div id="galleriffic-pagination">
						<input type="text" id="galleriffic-style" name="galleriffic_style" size="2" maxlength="2" value="{$gBitSystem->getConfig('fisheye_gallery_default_galleriffic_style')}"/> {tr}Galleriffic layout style{/tr}
						{formhelp note="This option provides a javascript powered tabbed thumbnail list display using the galleriffic jquery library."}
					</div>
				</div>

				<div class="row">
					{formlabel label="Default Thumbnail Size" for="default_gallery_thumbnail_size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="default_gallery_thumbnail_size" id="default_gallery_thumbnail_size" selected=$gBitSystem->getConfig('fisheye_gallery_default_thumbnail_size')}
					{/forminput}
				</div>
			{/legend}
		{/jstab}

		{jstab title="Images"}
			{legend legend="Image Display Settings"}
				<div class="row">
					{formhelp note="The options below determine what information is displayed on the image display page."}
				</div>

				{foreach from=$formImageLists key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{formhelp note=`$output.note`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}

			{legend legend="Default Image Display Settings"}
				<div class="row">
					{formlabel label="Default Thumbnail Size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="default_image_thumbnail_size" selected=$gBitSystem->getConfig('fisheye_image_default_thumbnail_size')}
					{/forminput}
				</div>
			{/legend}
		{/jstab}

		<div class="row submit">
			<input type="submit" name="fisheyeAdminSubmit" value="{tr}Change Preferences{/tr}" />
		</div>
	{/jstabs}
{/form}

{/strip}
<script type="text/javascript">//<![CDATA[
updateGalleryPagination();
//]]></script>
