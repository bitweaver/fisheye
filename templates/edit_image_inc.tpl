{strip}
{form legend="Edit Image Details"}
	<input type="hidden" name="gallery_id" value="{$smarty.request.gallery_id}"/>
	<input type="hidden" name="image_id" value="{$imageId}"/>
	<input type="hidden" name="ajax" value="{$smarty.request.ajax}" />

	<div class="control-group">
		{formlabel label="Title" for="image-title"}
		{forminput}
			<input type="text" name="title" id="image-title" value="{$gContent->getTitle('',FALSE)|escape}" maxlength="160" size="40"/>
		{/forminput}
	</div>

	<div class="control-group">
		{formlabel label="Description" for="image-desc"}
		{forminput}
			<textarea name="edit" id="image-desc" rows="4" cols="40">{$gContent->mInfo.data|escape}</textarea>
		{/forminput}
	</div>

	<div class="control-group">
		{formlabel label="Current Image"}
		{forminput}
			{if $gContent->mInfo.thumbnail_url.medium}
				<img src="{$gContent->mInfo.thumbnail_url.medium}" alt="{$gContent->getTitle()|escape}" />
				<br />
				<small>
					{if $gContent->mInfo.width && $gContent->mInfo.height}
						{tr}Full size{/tr} - <a href="{$gContent->getDownloadUrl()}">{$gContent->mInfo.width} x {$gContent->mInfo.height}</a>
					{elseif $gContent->getDownloadUrl()}
						<a href="{$gContent->getDownloadUrl()}">{tr}Full size{/tr}</a>
					{/if}
				</small>
			{else}
				<img src="{$smarty.const.FISHEYE_PKG_URL}image/no_image.png" alt="{$gContent->getTitle()|escape}" />
			{/if}
		{/forminput}
	</div>

	<div class="control-group">
		{formlabel label="Rotate Image"}
		{forminput}
			<label><input type="radio" name="rotate_image" value="-90"/> {biticon ipackage="fisheye" iname="rotate_ccw" iexplain="Rotate Counter Clockwise"}</label> &nbsp;&nbsp;&nbsp;&nbsp;
			<label>{biticon ipackage="fisheye" iname="rotate_cw" iexplain="Rotate Clockwise"} <input type="radio" name="rotate_image" value="90"/></label>
			<br />
			<label><input type="radio" name="rotate_image" value="" checked="checked"/> {tr}don't rotate{/tr}</label>
		{/forminput}
	</div>

	<div class="control-group">
		{include file="bitpackage:fisheye/resize_image_select.tpl"}
	</div>

	<div class="control-group submit">
		<input type="submit" class="btn" name="saveImage" value="Save Image"/>
	</div>
{/form}
{/strip}
