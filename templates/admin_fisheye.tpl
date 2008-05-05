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
					{formlabel label="Default number of rows and columns"}
					{forminput}
						<label>
							<input type="text" size="2" maxlength="2" name="rows_per_page" value="{$gBitSystem->getConfig('fisheye_gallery_default_rows_per_page')}"/>&nbsp;
							{tr}Rows Per Page{/tr}
						</label>
						<br />
						<label>
							<input type="text" size="2" maxlength="2" name="cols_per_page" value="{$gBitSystem->getConfig('fisheye_gallery_default_cols_per_page')}"/>&nbsp;
							{tr}Columns Per Page{/tr}
						</label>
						{formhelp note="If you have chosen the &lt;div&gt; layout method, these number will merely be multiplied to generate the maximum number of images per page."}
					{/forminput}
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
