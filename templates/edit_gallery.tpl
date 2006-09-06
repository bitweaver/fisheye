{literal}

{/literal}
{strip}
<div class="edit fisheye">
	<div class="header">
		<h1>
			{if $gContent->mInfo.title}
				{tr}Edit Gallery{/tr}: {$gContent->mInfo.title|escape}
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
							{formlabel label="Title" for="gallery-title" mandatory="y"}
							{forminput}
								<input type="text" name="title" id="gallery-title" value="{$gContent->mInfo.title|escape}" maxlength="160" size="50"/>
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Description" for="gallery-desc"}
							{forminput}
								<textarea name="edit" id="gallery-desc" rows="4" cols="50">{$gContent->mInfo.data}</textarea>
							{/forminput}
						</div>

						{if $gBitUser->hasPermission('p_fisheye_create_public_gal')}
							<div class="row">
								{formlabel label="Public Gallery" for="is_public"}
								{forminput}
									<input type="checkbox" name="is_public" id="is_public" value="y" {if $gContent->getPreference('is_public') eq 'y'}checked="checked"{/if} />
									{formhelp note="Allow other users to upload to this this gallery."}
								{/forminput}
							</div>
						{/if}

						<div class="row">
							{formlabel label="Gallery Pagination" for="gallery-pagination"}
							{forminput}
								{html_options name="gallery_pagination" id="gallery-pagination" options=$galleryPaginationTypes selected=$gContent->getPreference('gallery_pagination',$gBitSystem->getConfig('default_gallery_pagination','fixed_grid')}
							{/forminput}
						</div>



						<div class="row" id="matrix-pagination">
							{forminput}
								<input type="text" id="gallery-rows-per-page" name="rows_per_page" size="2" maxlength="2" value="{$gContent->mInfo.rows_per_page|default:$gBitSystem->getConfig('fisheye_gallery_default_rows_per_page')}"/> {tr}Rows per page{/tr}<br/>
								<input type="text" id="gallery-cols-per-page" name="cols_per_page" size="2" maxlength="2" value="{$gContent->mInfo.cols_per_page|default:$gBitSystem->getConfig('fisheye_gallery_default_cols_per_page')}"/> {tr}Columns per page{/tr}
								{formhelp note="The images will be displayed in a fixed grid. You can specify what number of thumbnails to display per page.<br /><strong>[rows] * [columns] = [number of images]</strong>."}
							{/forminput}
						</div>

						<div class="row" id="">
							{formlabel label="" for="gallery-cols-per-page"}
							{forminput}
								<input type="text" id="gallery-cols-per-page" name="cols_per_page" size="2" maxlength="2" value="{$gContent->mInfo.cols_per_page|default:$gBitSystem->getConfig('fisheye_gallery_default_cols_per_page')}"/> {tr}Total images per page{/tr}
								{formhelp note="The images per page pages will automatically adjust to the browsers width. You can specify what number of thumbnails to display per page.<br /><strong>[rows] * [columns] = [number of images]</strong>."}
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

						<div class="row">
							{formlabel label="Image Comments" for=allow_comments}
							{forminput}
								<input type="checkbox" name="allow_comments" id="allow_comments" value="y" {if $gContent->getPreference('allow_comments') eq 'y'}checked="checked"{/if} />
								{formhelp note="Allow posting comments for an image."}
							{/forminput}
						</div>

						{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile=content_edit_mini_tpl}
					{/legend}
				{/jstab}

				{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_tab_tpl}

				{if $galleryList}
					{jstab title="Gallery Memberships"}
						{legend legend="Gallery Memberships"}
							{tr}If you would like this gallery to be a sub-gallery, check the parent gallery below. It is possible to belong to multiple galleries. If no parent is checked, this gallery will appear as a top-level gallery.{/tr}
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
										<a href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gal.gallery_id}">{$gal.title|escape}</a>
										<br />
										{/if}
									{foreachelse}
										{tr}No Galleries Found{/tr}.
									{/foreach}
								{/forminput}
							</div>
						{/legend}
					{/jstab}
				{/if}
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
