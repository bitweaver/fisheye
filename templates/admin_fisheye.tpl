{strip}

{form}
	{jstabs}
		{jstab title="General Settings"}
			{legend legend="General Settings"}
				<div class="row">
					{formhelp note="To change the Image Processing engine, you need to change the setting in Liberty Settings" link="kernel/admin/index.php?page=liberty/Liberty Settings"}
				</div>

				{foreach from=$formGalleryGeneral key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
						{if $output.type=='text'}
							<input type="text" name="{$item}" id="{$item}" value="{$gBitSystemPrefs.$item}"/>
						{elseif $output.type=='checkbox'}
							{html_checkboxes name="$item" values="y" checked=`$gBitSystemPrefs.$item` labels=false id=$item}
						{/if}
							{formhelp note=`$output.note`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="List Settings"}
			{legend legend="Gallery List Options"}
				{formhelp note="The options below determine what information is shown on the List Galleries page."}

				{foreach from=$formGalleryListLists key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=`$gBitSystemPrefs.$item` labels=false id=$item}
							{formhelp note=`$output.note`}
						{/forminput}
					</div>
				{/foreach}
				<div class="row">
					{formlabel label="List Thumbnail Size"}
					{forminput}
						{html_radios values="avatar" output="Avatar (100x75)" name="list_thumbnail_size" checked=$gBitSystemPrefs.fisheye_list_thumbnail_size}<br />
						{html_radios values="small" output="Small (160x120)" name="list_thumbnail_size" checked=$gBitSystemPrefs.fisheye_list_thumbnail_size}<br />
						{html_radios values="medium" output="Medium (400x300)" name="list_thumbnail_size" checked=$gBitSystemPrefs.fisheye_list_thumbnail_size}<br />
						{html_radios values="large" output="Large (800x600)" name="list_thumbnail_size" checked=$gBitSystemPrefs.fisheye_list_thumbnail_size}
					{/forminput}
				</div>
			{/legend}
		{/jstab}

		{jstab title="Gallery Display Settings"}
			{legend legend="Gallery Display Settings"}
				<input type="hidden" name="page" value="{$page}" />
				{formhelp note="The options below determine what information is shown on a gallery display page."}

				{foreach from=$formGalleryLists key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=`$gBitSystemPrefs.$item` labels=false id=$item}
							{formhelp note=`$output.note`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}

			{legend legend="Default Gallery Display Settings"}
				{formhelp note="The settings below determine what the default display options will be set to for new galleries."}

				<div class="row">
					{formlabel label="Default number of rows and columns"}
					{forminput}
						<label>
							<input type="text" size="2" maxlength="2" name="rows_per_page" value="{$gBitSystemPrefs.fisheye_gallery_default_rows_per_page}"/>&nbsp;
							{tr}Rows Per Page{/tr}
						</label>
						<br />
						<label>
							<input type="text" size="2" maxlength="2" name="cols_per_page" value="{$gBitSystemPrefs.fisheye_gallery_default_cols_per_page}"/>&nbsp;
							{tr}Columns Per Page{/tr}
						</label>
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Default Thumbnail Size"}
					{forminput}
						{html_radios values="avatar" output="Avatar (100x100)" name="default_gallery_thumbnail_size"	checked=$gContent->mInfo.thumbnail_size}<br />
						{html_radios values="small" output="Small (160x120)" name="default_gallery_thumbnail_size" checked=$gBitSystemPrefs.fisheye_gallery_default_thumbnail_size}<br />
						{html_radios values="medium" output="Medium (400x300)" name="default_gallery_thumbnail_size" checked=$gBitSystemPrefs.fisheye_gallery_default_thumbnail_size}<br />
						{html_radios values="large" output="Large (800x600)" name="default_gallery_thumbnail_size" checked=$gBitSystemPrefs.fisheye_gallery_default_thumbnail_size}
					{/forminput}
				</div>

			{/legend}
		{/jstab}

		{jstab title="Image Display Settings"}
			{legend legend="Image Display Settings"}
				{formhelp note="The options below determine what information is displayed on the image display page."}

				{foreach from=$formImageLists key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=`$gBitSystemPrefs.$item` labels=false id=$item}
							{formhelp note=`$output.note`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}

			{legend legend="Default Image Display Settings"}
				<div class="row">
					{formlabel label="Default Thumbnail Size"}
					{forminput}
						{html_radios values="small" output="Small (160x120)" name="default_image_thumbnail_size" checked=$gBitSystemPrefs.fisheye_image_default_thumbnail_size}<br />
						{html_radios values="medium" output="Medium (400x300)" name="default_image_thumbnail_size" checked=$gBitSystemPrefs.fisheye_image_default_thumbnail_size}<br />
						{html_radios values="large" output="Large (800x600)" name="default_image_thumbnail_size" checked=$gBitSystemPrefs.fisheye_image_default_thumbnail_size}
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
