{strip}
<div class="edit fisheye">
	<div class="header">
		<h1>
			{if $gContent->mInfo.title}
				{tr}Edit Gallery {$gContent->mInfo.title}{/tr}
			{else}
				{tr}Create Image Gallery{/tr}
			{/if}
		</h1>
	</div>

	<div class="body">
		{form id="editGalleryForm" ipackage="fisheye" ifile="edit.php"}
			{jstabs}
				{jstab title="Edit Gallery"}
					{legend legend="Edit Gallery"}
						{formfeedback error=$errors warning=$fisheyeWarnings success=$fisheyeSuccess}

						<input type="hidden" name="gallery_id" value="{$galleryId|escape}"/>

						<div class="row">
							{formlabel label="Title" for="gallery-title"}
							{forminput}
								<input type="text" name="title" id="gallery-title" value="{$gContent->mInfo.title}" maxlength="160" size="50"/>
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Description" for="gallery-desc"}
							{forminput}
								<textarea name="edit" id="gallery-desc" rows="4" cols="50">{$gContent->mInfo.data}</textarea>
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Rows per page" for="gallery-rows-per-page"}
							{forminput}
								<input type="text" id="gallery-rows-per-page" name="rows_per_page" size="2" maxlength="2" value="{$gContent->mInfo.rows_per_page|default:$gBitSystem->getConfig('fisheye_gallery_default_rows_per_page')}"/>
								{if !$gBitSystem->isFeatureActive( 'fisheye_gallery_div_layout' )}
									{formhelp note="Number of rows of images per gallery page"}
								{/if}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Columns per page" for="gallery-cols-per-page"}
							{forminput}
								<input type="text" id="gallery-cols-per-page" name="cols_per_page" size="2" maxlength="2" value="{$gContent->mInfo.cols_per_page|default:$gBitSystem->getConfig('fisheye_gallery_default_cols_per_page')}"/>
								{if !$gBitSystem->isFeatureActive( 'fisheye_gallery_div_layout' )}
									{formhelp note="Number of columns of images per gallery page"}
								{else}
									{formhelp note="The gallery pages will be displayed using divs. This means that the rows and columns will adjust to the browsers width. You can specify what number of thumbnails to display per page.<br /><strong>[rows] * [columns] = [number of images]</strong>."}
								{/if}
							{/forminput}
						</div>

						{if $thumbnailSizes}
							<div class="row">
								{formlabel label="Thumbnail Size"}
								{forminput}
									{if $gContent->mInfo.thumbnail_size}
										{assign var=thumb_size value=$gContent->mInfo.thumbnail_size}
									{else}
										{assign var=thumb_size value=$gBitSystem->getConfig('fisheye_gallery_default_thumbnail_size')}
									{/if}
									{html_radios options=$thumbnailSizes name="thumbnail_size" checked=$thumb_size separator="<br />"}
								{/forminput}
							</div>
						{/if}

						{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile=content_edit_mini_tpl}
					{/legend}
				{/jstab}

				{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_tab_tpl}

				{jstab title="Advanced Options"}
					{if $galleryList}
						{legend legend="Advanced Options"}
							<div class="row">
								{formlabel label="`$gContent->mInfo.content_description` Belongs to These Galleries"}
								{forminput}
									{foreach from=$galleryList key=galId item=gal}
										{if $galId != $gContent->mGalleryId}
										<input type="checkbox" name="galleryAdditions[]" value="{$gal.gallery_id}"
											{if $gal.in_gallery}
												checked="checked"
											{/if}
										/>
										<a href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gal.gallery_id}">{$gal.title}</a>
										<br />
										{/if}
									{foreachelse}
										{tr}No Galleries Found{/tr}.
									{/foreach}
								{/forminput}
							</div>
						{/legend}
					{/if}
				{/jstab}
			{/jstabs}

			<div class="row submit">
				{if $gContent->isValid()}
					<input type="submit" name="cancelgallery" value="Cancel"/>
				{/if}
				<input type="submit" name="savegallery" value="Save Gallery"/>
			</div>
		{/form}

	</div>	<!-- end .body -->
</div>	<!-- end .fisheye -->
{/strip}
