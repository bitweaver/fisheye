<div class="edit fisheye">
	{if $gContent->mInfo.title}{assign var=tabTitle value="{tr}Edit Gallery{/tr} `$gContent->mInfo.title`"}{else}{assign var=tabTitle value="{tr}Create Image Gallery{/tr}"}{/if}</h4>
	<div class="header">
		<h1>{$tabTitle}</h1>
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
								<textarea name="edit" id="gallery-desc" rows="4" cols="40">{$gContent->mInfo.data}</textarea>
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Rows per page" for="gallery-rows-per-page"}
							{forminput}
								<input type="text" id="gallery-rows-per-page" name="rows_per_page" size="2" maxlength="2" value="{$gContent->mInfo.rows_per_page|default:$gBitSystemPrefs.fisheye_gallery_default_rows_per_page}"/>
								{formhelp note="Number of rows of images per gallery page"}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Columns per page" for="gallery-cols-per-page"}
							{forminput}
								<input type="text" id="gallery-cols-per-page" name="cols_per_page" size="2" maxlength="2" value="{$gContent->mInfo.cols_per_page|default:$gBitSystemPrefs.fisheye_gallery_default_cols_per_page}"/>
								{formhelp note="Number of columns of images per gallery page"}
							{/forminput}
						</div>

					{if $thumbnailSizes}
						<div class="row">
							{formlabel label="Thumbnail Size"}
							{forminput}
								{if $gContent->mInfo.thumbnail_size}
									{assign var=thumb_size value=$gContent->mInfo.thumbnail_size}
								{else}
									{assign var=thumb_size value=$gBitSystemPrefs.fisheye_gallery_default_thumbnail_size}
								{/if}
								{html_radios options=$thumbnailSizes name="thumbnail_size" checked=$thumb_size separator="<br />"}
							{/forminput}
						</div>
					{/if}
					{/legend}
					{if $gLibertySystem->hasService( $smarty.const.LIBERTY_SERVICE_ACCESS_CONTROL ) }
						{legend legend="Security Settings"}
							{include file=$gLibertySystem->getServiceValue($smarty.const.LIBERTY_SERVICE_ACCESS_CONTROL,'edit_choose_tpl')}
						{/legend}
					{/if}
				{/jstab}

				{if $gBitSystem->isPackageActive( 'categories' )}
					{jstab title="Categorize"}
						{legend legend="Categorize"}
							{include file="bitpackage:categories/categorize.tpl"}
						{/legend}
					{/jstab}
				{/if}


				{jstab title="Advanced Options"}
					{if $gBitSystem->isPackageActive( 'nexus' )}
						{legend legend="Insert Link in Menu"}
							{include file="bitpackage:nexus/insert_menu_item_inc.tpl"}
						{/legend}
					{/if}

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
