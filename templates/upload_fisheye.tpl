<div class="admin fisheye">
	<div class="header">
		<h1>{tr}Upload Images{/tr}</h1>
	</div>

	<div class="body">
		{form enctype="multipart/form-data" legend="Upload Images"}
			{formfeedback note=$quotaMessage}
			{formfeedback warning="The maximum file size you can upload is `$uploadMax` Megabytes"}
			{formfeedback error=$errors}

			{formhelp note="Here you can upload images. You can upload single image files, or you can upload archived files (.zip's, .sit's, .tar's, etc.) Archvied uploads will automatically be decompressed, and a gallery will created for every gallery in it. If you have nested folders, the hierarchy will be maintained for you with nested galleries." force=true}

			<input type="hidden" name="gallery_id" value="{$galleryId|escape}"/>
			<input type="hidden" name="image_id" value="{$imageId}"/>
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" />

			<div class="uploadarray">
				{assign var=uploads value=10 }
				{section name=ix loop=$uploads name=uploads start=0 step=1 max=10}
					<div class="row upload">
						{formlabel label="Upload Image `$smarty.section.uploads.iteration`" for="image-upload-`$smarty.section.uploads.iteration`"}
						{forminput}
							<input type="file" name="{$smarty.section.uploads.iteration}" size="40" id="image-upload-{$smarty.section.uploads.iteration}" />
						{/forminput}
					</div>
				{/section}
			</div>

			<div class="row">
				{formlabel label="Add Image(s) to these Galleries"}
				{forminput}
					{foreach from=$galleryList key=galId item=gal}
							<input type="checkbox" name="galleryAdditions[]" value="{$galId}"
								{if  $gContent->mGalleryId == $galId}
									checked="checked"
								{else}
									{section name=gx loop=$gContent->mInfo.parent_galleries}
										{if ($gContent->mInfo.parent_galleries[gx].gallery_id == $galId)}
											checked="checked"
										{/if}
									{/section}
								{/if}
							/>
							<a href="{$gBitLoc.FISHEYE_PKG_URL}view.php?gallery_id={$gal.gallery_id}">{$gal.title}</a>
							<br />
					{foreachelse}
						<div>{tr}No Galleries Found{/tr}. {tr}A gallery named <strong>{displayname hash=$gBitUser->mInfo nolink=1}'s Gallery</strong> will be created for you and images will be added to it.{/tr}</div>
					{/foreach}
				{/forminput}
			</div>

			<div class="row">
				{include file="bitpackage:fisheye/resize_image_select.tpl"}
			</div>

			<div class="row submit">
				<strong>{tr}Please don't press the save button more than once!<br />Depending on what you are uploading and the system, this can take a few minutes.{/tr}</strong><br/>
				<input type="submit" name="save_image" value="Save Image(s)"/>
			</div>
		{/form}
	</div> <!-- end .body -->
</div> <!-- end .fisheye -->
