{strip}

<div class="edit fisheye">
	<div class="header">
		<h1>{if $gContent->mInfo.image_id}{tr}Edit Image{/tr}: {$gContent->getTitle()|escape} {else}{tr}Add New Image{/tr} {/if}</h1>
	</div>

	<div class="body">
		{form enctype="multipart/form-data"}
			{jstabs}
				{jstab title="Edit Image"}
					{legend legend="Edit Image"}

						{formfeedback error=$errors}

						<input type="hidden" name="gallery_id" value="{$galleryId|escape}"/>
						<input type="hidden" name="image_id" value="{$imageId}"/>
						<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" />

						<div class="row">
							{formlabel label="Current Image"}
							{forminput}
								{if $gContent->mInfo.thumbnail_url.medium}
									<img src="{$gContent->mInfo.thumbnail_url.medium}?{math equation="1 + rand(1,9999)"}" alt="{$gContent->getTitle()|escape}" />
									<br />
									<small>
										<a href="{$gContent->getSourceUrl()}">{tr}Full size{/tr}</a>
										{if $gContent->mInfo.width && $gContent->mInfo.height}
											: {$gContent->mInfo.width} x {$gContent->mInfo.height}
										{/if}
									</small>
								{else}
									<img src="{$smarty.const.FISHEYE_PKG_URL}image/no_image.png" alt="{$gContent->getTitle()|escape}" />
								{/if}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Title" for="image-title"}
							{forminput}
								<input type="text" name="title" id="image-title" value="{$gContent->getTitle(0,0)|escape}" maxlength="160" size="50"/>
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Description" for="image-desc"}
							{forminput}
								<textarea name="edit" id="image-desc" rows="4" cols="50">{$gContent->mInfo.data|escape}</textarea>
							{/forminput}
						</div>

						<div class="row">
							{if $gContent->mInfo.source_url}
								{formfeedback warning="{tr}Uploading a new image will replace the currently existing one.{/tr}"}
								{assign var=repl value=Replacement}
							{/if}
							{formlabel label="Upload $repl Image" for="image-upload"}
							{forminput}
								<input type="file" name="imageFile" id="image-upload"/>
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Regenerate Thumbnails"}
							{forminput}
								<input type="checkbox" name="generate_thumbnails" value="1"/>
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Rotate Image"}
							{forminput}
{if function_exists('exif_read_data')}
								<label><input type="radio" name="rotate_image" value="auto"/> {biticon ipackage="fisheye" iname="rotate_auto" iexplain="Auto Rotate"}</label> &nbsp;&nbsp;&nbsp;&nbsp;
{/if}
								<label><input type="radio" name="rotate_image" value="-90"/> {biticon ipackage="fisheye" iname="rotate_ccw" iexplain="Rotate Counter Clockwise"}</label> &nbsp;&nbsp;&nbsp;&nbsp;
								<label>{biticon ipackage="fisheye" iname="rotate_cw" iexplain="Rotate Clockwise"} <input type="radio" name="rotate_image" value="90"/></label>
								<br />
								<label> <input type="radio" name="rotate_image" value="" checked="checked"/> {tr}don't rotate{/tr}</label>
							{/forminput}
						</div>

						{include file=$gLibertySystem->getMimeTemplate('edit',$gContent->mInfo.attachment_plugin_guid) attachment=$gContent->mInfo}

						<div class="row">
							{include file="bitpackage:fisheye/resize_image_select.tpl"}
						</div>

						<div class="row">
							{formlabel label="Add This Image to These Galleries"}
							{forminput}
								{if $galleryTree}
<div class="gallerytree">
	{$galleryTree}
</div>						{else}
							<p class="norecords">
								{tr}No Galleries Found{/tr}.<br />
							</p>
						{/if}
					{/forminput}
						</div>

						{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"}
					{/legend}
				{/jstab}

				{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_tab_tpl"}
			{/jstabs}

			<div class="row submit">
				<input type="submit" name="saveImage" value="Save Image"/>
			</div>
		{/form}
	</div> <!-- end .body -->
</div> <!-- end .fisheye -->

{/strip}
