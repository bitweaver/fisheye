{* Site settings for the gallery package, shown inside the site administration pages. *}
{strip}
{form}
	<input type="hidden" name="page" value="{$page}" />
	{jstabs}
		{jstab title="Settings"}
			{legend legend="General Settings"}
				<div class="form-group">
					{formhelp note="To change the Image Processing engine, you need to change the setting in Liberty Settings" link="kernel/admin/index.php?page=liberty/Liberty Settings"}
				</div>

				{foreach from=$formGalleryGeneral key=item item=output}
					<div class="form-group">
						{if $output.type=='checkbox'}
							{forminput label="checkbox"}
								{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item} {$output.label}
								{formhelp note=$output.note}
							{/forminput}
						{else}
							{formlabel label=$output.label for=$item}
							{forminput}
								<input type="text" class="form-control" name="{$item}" id="{$item}" value="{$gBitSystem->getConfig($item)|escape}"/>
								{formhelp note=$output.note}
							{/forminput}
						{/if}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="Galleries"}
			{legend legend="Gallery Pages"}
				<div class="form-group">
					{formlabel label="New galleries open in" for="gallery-pagination"}
					{forminput}
						{html_options name="default_gallery_pagination" id="gallery-pagination" options=$galleryPaginationTypes selected=$defaultGalleryLayout class="form-control"}
						{formhelp note="The view a gallery opens in until its owner chooses another in the gallery settings. Visitors can switch view on the gallery page."}
					{/forminput}
				</div>

				<div class="form-group">
					{formlabel label="Photos per page" for="fisheye_gallery_page_size"}
					{forminput}
						<input type="number" class="form-control" name="fisheye_gallery_page_size" id="fisheye_gallery_page_size" min="{$smarty.const.FISHEYE_PAGE_SIZE_MIN}" max="{$smarty.const.FISHEYE_PAGE_SIZE_MAX}" step="1" value="{$galleryPageSize}" />
						{formhelp note="How many photos a gallery page loads at first. The rest arrive as the visitor scrolls or asks for more."}
					{/forminput}
				</div>

				<div class="form-group">
					{formlabel label="Order of unsorted photos" for="fisheye_gallery_default_sort_mode"}
					{forminput}
						{html_options options=$sortOptions name="fisheye_gallery_default_sort_mode" id="fisheye_gallery_default_sort_mode" selected=$gBitSystem->getConfig('fisheye_gallery_default_sort_mode') class="form-control"}
						{formhelp note="This is the order of the images if they have not been sorted manually i.e. all the images with the same position value."}
					{/forminput}
				</div>

				{foreach from=$formGalleryLists key=item item=output}
					<div class="form-group">
						{forminput label="checkbox"}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item} {$output.label}
							{formhelp note=$output.note}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="Photos"}
			{legend legend="Photo Pages"}
				<div class="form-group">
					{formlabel label="Photo size" for="default_image_thumbnail_size"}
					{forminput}
						{html_options options=$imageSizes name="default_image_thumbnail_size" id="default_image_thumbnail_size" selected=$gBitSystem->getConfig('fisheye_image_default_thumbnail_size') class="form-control"}
						{formhelp note="The size of the photo shown on a photo page and in the lightbox."}
					{/forminput}
				</div>

				{foreach from=$formImageLists key=item item=output}
					<div class="form-group">
						{forminput label="checkbox"}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item} {$output.label}
							{formhelp note=$output.note}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="List module"}
			{legend legend="Gallery List Columns"}
				<div class="form-group">
					{formhelp note="The options below determine what the gallery list center piece shows. The Library page does not use them."}
				</div>

				{foreach from=$formGalleryListLists key=item item=output}
					<div class="form-group">
						{forminput label="checkbox"}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item} {$output.label}
							{formhelp note=$output.note}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}
	{/jstabs}

	<div class="form-group submit">
		<input type="submit" class="btn btn-default" name="fisheyeAdminSubmit" value="{tr}Change Preferences{/tr}" />
	</div>
{/form}
{/strip}
