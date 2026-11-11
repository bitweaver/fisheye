{* Photo details: name, the file itself, the galleries it is in. *}
{strip}
{if $gContent->getTitle()}{assign var=feLabel value=$gContent->getTitle()}{else}{assign var=feLabel value=$gContent->mInfo.image_file.file_name}{/if}
<div class="fe-app fe-settings fe-editphoto">
	<div class="fe-top">
		<ol class="fe-crumb">
			<li><a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gContent->mInfo.user_id}">{tr}Galleries{/tr}</a></li>
			{if $gGallery && $gGallery->isValid()}<li><a href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gGallery->mGalleryId}">{$gGallery->getTitle()|escape}</a></li>{/if}
			<li><a href="{$gContent->getDisplayUrl()|escape}">{$feLabel|escape}</a></li>
			<li aria-current="page">{tr}Edit{/tr}</li>
		</ol>
	</div>

	<div class="fe-head">
		<div class="fe-head-main">
			<h1>{tr}Photo details{/tr}</h1>
		</div>
	</div>

	{formfeedback error=$errors}

	{form enctype="multipart/form-data" class="fe-form"}
		<input type="hidden" name="gallery_id" value="{$galleryId|escape}" />
		<input type="hidden" name="image_id" value="{$imageId}" />
		<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" />

		<div class="fe-fs">
			<h2>{tr}Name{/tr}</h2>
			<div>
				<input type="text" class="fe-input fe-input-lg" name="title" id="image-title" value="{$gContent->getTitle(0,0)|escape}" maxlength="160" placeholder="{tr}Name this photo{/tr}" aria-label="{tr}Name{/tr}" />
				<textarea class="fe-input" name="edit" id="image-desc" rows="4" placeholder="{tr}Describe this photo{/tr}" aria-label="{tr}Description{/tr}">{$gContent->mInfo.data|escape}</textarea>
			</div>
		</div>

		<div class="fe-fs">
			<h2>{tr}Photo{/tr}</h2>
			{* the buttons here act at once through organize.php; only replacing the file goes through the form *}
			<div class="fe-photoedit" data-fe-photo data-fe-url="{$smarty.const.FISHEYE_PKG_URL}organize.php" data-fe-tk="{$gBitUser->mTicket}" data-fe-id="{$gContent->mContentId}">
				<div class="fe-photoframe">
					{if $gContent->mInfo.thumbnail_url.medium}
						<img src="{$gContent->mInfo.thumbnail_url.medium|escape}?{math equation="1 + rand(1,9999)"}" alt="" data-fe-photo-img />
					{else}
						<p class="fe-sub">{tr}This photo is still being prepared.{/tr}</p>
					{/if}
				</div>
				<div class="fe-phototools" data-fe-photo-tools hidden>
					<button type="button" class="fe-btn" data-fe-photo-act="rotate" data-fe-degrees="270">{booticon iname="fa-undo"}{tr}Rotate left{/tr}</button>
					<button type="button" class="fe-btn" data-fe-photo-act="rotate" data-fe-degrees="90">{booticon iname="fa-redo"}{tr}Rotate right{/tr}</button>
					<button type="button" class="fe-btn" data-fe-photo-act="rethumb" title="{tr}Use this when a preview looks wrong or is missing{/tr}">{booticon iname="fa-sync-alt"}{tr}Refresh previews{/tr}</button>
					<label class="fe-btn" for="image-upload">{booticon iname="fa-upload"}{tr}Replace file{/tr}</label>
				</div>
				<p class="fe-sub">
					{$gContent->mInfo.image_file.file_name|escape}
					{if $gContent->mInfo.width && $gContent->mInfo.height} &middot; <span data-fe-photo-size data-fe-w="{$gContent->mInfo.width}" data-fe-h="{$gContent->mInfo.height}">{$gContent->mInfo.width} &times; {$gContent->mInfo.height}</span> {tr}pixels{/tr}{/if}
					{if $gContent->getDownloadUrl()} &middot; <a class="lnk" rel="nofollow" href="{$gContent->getDownloadUrl()|escape}">{tr}Original file{/tr}</a>{/if}
				</p>

				{* shown as a plain file field without the page script; with it, the Replace file button opens the chooser *}
				<input type="file" name="imageFile" id="image-upload" data-fe-replace />
				<div class="fe-replace" data-fe-replace-confirm hidden>
					<p>{tr}Replace this photo with{/tr} <b data-fe-replace-name></b>? {tr}The new file takes its place in every gallery.{/tr}</p>
					<input type="submit" class="fe-btn fe-btn-primary" name="saveImage" value="{tr}Replace now{/tr}" />
					<button type="button" class="fe-btn" data-fe-replace-cancel>{tr}Cancel{/tr}</button>
				</div>

				<details class="fe-options fe-reduce" data-fe-photo-tools hidden>
					<summary>{tr}Reduce the original{/tr}</summary>
					<div class="fe-reducerow">
						<select id="fe-resize" class="fe-select" data-fe-photo-resize aria-label="{tr}Longest side{/tr}">
							<option value="1024">1024 {tr}pixels on the long side{/tr}</option>
							<option value="1600">1600 {tr}pixels on the long side{/tr}</option>
							<option value="2048" selected="selected">2048 {tr}pixels on the long side{/tr}</option>
							<option value="2560">2560 {tr}pixels on the long side{/tr}</option>
							<option value="3264">3264 {tr}pixels on the long side{/tr}</option>
						</select>
						<button type="button" class="fe-btn" data-fe-photo-act="resize" data-fe-confirm="{tr}Reduce the original file? The larger version is not kept.{/tr}" data-fe-confirm-cancel="{tr}Cancel{/tr}">{tr}Reduce now{/tr}</button>
					</div>
					<p class="fe-sub">{tr}Makes the stored file smaller. Only a larger photo changes, and it cannot be undone.{/tr}</p>
				</details>
			</div>
		</div>

		<div class="fe-fs">
			<h2>{tr}In galleries{/tr}</h2>
			<div>
				<ul class="fe-parents" data-fe-parents data-fe-self="">
					{foreach from=$feParents item=parentGallery key=parentId}
						<li><label class="fe-check"><input type="checkbox" name="gallery_additions[]" value="{$parentId}" checked="checked" /> <span>{booticon iname="fa-folder"} {$parentGallery.title|escape}</span></label></li>
					{/foreach}
				</ul>
				{* the row the page script copies for a gallery chosen in the dialog *}
				<template data-fe-parent-row><li><label class="fe-check"><input type="checkbox" name="gallery_additions[]" value="" checked="checked" /> <span>{booticon iname="fa-folder"} <span data-fe-parent-title></span></span></label></li></template>
				<p><button type="button" class="fe-btn" data-fe-parent-add hidden>{booticon iname="fa-folder-plus"}{tr}Add to a gallery{/tr}</button></p>
				<p class="fe-sub">{tr}A photo can be in more than one gallery. Untick one to take it out when you save. With none ticked it stays where it is.{/tr}</p>
			</div>
		</div>

		<div class="fe-fs">
			<h2>{tr}More{/tr}</h2>
			<div class="fe-checks">
				<div class="fe-services">
					{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"}
					{jstabs}
						{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_tab_tpl"}
					{/jstabs}
				</div>
			</div>
		</div>

		<div class="fe-formfoot">
			<input type="submit" class="fe-btn fe-btn-primary" name="saveImage" value="{tr}Save changes{/tr}" />
			<a class="fe-btn" href="{$gContent->getDisplayUrl()|escape}">{tr}Cancel{/tr}</a>
			<span class="fe-bar-sp"></span>
			<a class="fe-btn fe-btn-danger" rel="nofollow" href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?image_id={$gContent->mImageId}{if $gGallery && $gGallery->isValid()}&amp;gallery_id={$gGallery->mGalleryId}{/if}&amp;delete=1" data-fe-delete-photo="{$gContent->mContentId}" data-fe-url="{$smarty.const.FISHEYE_PKG_URL}organize.php" data-fe-tk="{$gBitUser->mTicket}" data-fe-after="{if $gGallery && $gGallery->isValid()}{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gGallery->mGalleryId}{else}{$smarty.const.FISHEYE_PKG_URL}?user_id={$gContent->mInfo.user_id}{/if}" data-fe-confirm="{tr}Delete this photo? It is removed from every gallery it is in. This cannot be undone.{/tr}" data-fe-confirm-ok="{tr}Delete{/tr}" data-fe-confirm-cancel="{tr}Cancel{/tr}">{booticon iname="fa-trash-alt"}{tr}Delete{/tr}</a>
		</div>
	{/form}

	{* gallery dialog, filled by the page script from the signed-in user's galleries *}
	<dialog class="fe-app fe-picker" data-fe-picker data-fe-url="{$smarty.const.FISHEYE_PKG_URL}organize.php" data-fe-tk="{$gBitUser->mTicket}" aria-label="{tr}Choose a gallery{/tr}">
		<h2>{tr}Add to{/tr}</h2>
		<label class="fe-search">{booticon iname="fa-search"}<input type="search" data-fe-picker-find placeholder="{tr}Find a gallery{/tr}" autocomplete="off" /></label>
		<ul data-fe-picker-list data-fe-none="{tr}No galleries match.{/tr}"></ul>
		{if $gBitUser->hasPermission( 'p_fisheye_create' )}
			<div class="fe-picker-new"><input type="text" class="fe-input" data-fe-picker-name maxlength="160" placeholder="{tr}New gallery name{/tr}" aria-label="{tr}New gallery name{/tr}" /><button type="button" class="fe-btn" data-fe-picker-create>{booticon iname="fa-folder-plus"}{tr}Create{/tr}</button></div>
		{/if}
		<p class="fe-picker-foot"><button type="button" class="fe-btn" data-fe-picker-cancel>{tr}Cancel{/tr}</button></p>
	</dialog>
</div>
{/strip}
