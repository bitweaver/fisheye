{strip}

<div class="edit fisheye">
	<div class="header">
		<h1>{if $gContent->mInfo.image_id}{tr}Edit Image{/tr}: {$gContent->getTitle()|escape} {else}{tr}Add New Image{/tr} {/if}</h1>
	</div>

	<div class="body">
		{form enctype="multipart/form-data"}
			{jstabs}
				{jstab title="Edit Image"}
						{formfeedback error=$errors}

						<input type="hidden" name="gallery_id" value="{$galleryId|escape}"/>
						<input type="hidden" name="image_id" value="{$imageId}"/>
						<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" />

						<div class="control-group pull-right">
							{formlabel label="Current Image"}
							{forminput}
								{if $gContent->mInfo.thumbnail_url.medium}
									<img src="{$gContent->mInfo.thumbnail_url.medium}?{math equation="1 + rand(1,9999)"}" alt="{$gContent->getTitle()|escape}" />
									<br />
									<small>
										<a href="{$gContent->getDownloadUrl()}">{tr}Full size{/tr}</a>
										{if $gContent->mInfo.width && $gContent->mInfo.height}
											: {$gContent->mInfo.width} x {$gContent->mInfo.height}
										{/if}
									</small>
								{else}
									<img src="{$smarty.const.FISHEYE_PKG_URL}image/no_image.png" alt="{$gContent->getTitle()|escape}" />
								{/if}
							{/forminput}
						</div>

						<div class="control-group">
							{formlabel label="Title" for="image-title"}
							{forminput}
								<input type="text" class="input-xlarge" name="title" id="image-title" value="{$gContent->getTitle(0,0)|escape}" maxlength="160" size="50"/>
							{/forminput}
						</div>

						<div class="control-group">
							{formlabel label="Description" for="image-desc"}
							{forminput}
								<textarea name="edit" class="input-xlarge" id="image-desc" rows="4" cols="50">{$gContent->mInfo.data|escape}</textarea>
							{/forminput}
						</div>

						<div class="control-group">
							{if $gContent->getDownloadUrl()}
								{formfeedback warning="{tr}Uploading a new image will replace the currently existing one.{/tr}"}
								{assign var=repl value=Replacement}
							{/if}
							{formlabel label="Upload $repl Image" for="image-upload"}
							{forminput}
								<input type="file" name="imageFile" id="image-upload"/>
							{/forminput}
						</div>

						<div class="control-group">
							{forminput}
								<label class="checkbox">
									<input type="checkbox" name="generate_thumbnails" value="1"/> {tr}Regenerate Thumbnails{/tr}
								</label>
							{/forminput}
						</div>

						<div class="control-group">
							{formlabel label="Rotate Image"}
							{forminput}
{if function_exists('exif_read_data')}
								<label class="radio"><input type="radio" name="rotate_image" value="auto"/> {biticon ipackage="fisheye" iname="rotate_auto" iexplain="Auto Rotate"}</label> &nbsp;&nbsp;&nbsp;&nbsp;
{/if}
								<label class="radio"><input type="radio" name="rotate_image" value="-90"/> {biticon ipackage="fisheye" iname="rotate_ccw" iexplain="Rotate Counter Clockwise"}</label> &nbsp;&nbsp;&nbsp;&nbsp;
								<label class="radio">{biticon ipackage="fisheye" iname="rotate_cw" iexplain="Rotate Clockwise"} <input type="radio" name="rotate_image" value="90"/></label>
								<label class="radio"> <input type="radio" name="rotate_image" value="" checked="checked"/> {tr}don't rotate{/tr}</label>
							{/forminput}
						</div>

						<div class="control-group">
							{include file="bitpackage:fisheye/resize_image_select.tpl"}
						</div>

						<div class="control-group">
							{formlabel label="Add This Image to These Galleries"}
							{forminput}
								{if $galleryTree}
									<div class="gallerytree">
										{$galleryTree}
									</div>
								{else}
							<p class="norecords">
								{tr}No Galleries Found{/tr}.<br />
							</p>
						{/if}
					{/forminput}
						</div>

						{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"}
				{/jstab}

				{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_tab_tpl"}
			{/jstabs}

			<div class="control-group submit">
				<input type="submit" class="btn" name="saveImage" value="Save Image"/>
			</div>
		{/form}
	</div> <!-- end .body -->
</div> <!-- end .fisheye -->

{/strip}
