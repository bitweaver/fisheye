{* Upload photos into a gallery: drop or choose files, each sent on its own with a progress row. *}
{strip}
<div class="fe-app fe-uploadpage">
	<div class="fe-top">
		<ol class="fe-crumb">
			<li><a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gBitUser->mUserId}">{tr}Galleries{/tr}</a></li>
			{if $feTarget}<li><a href="{$feTarget.display_url|escape}" data-fe-target-link>{$feTarget.title|escape}</a></li>{/if}
			<li aria-current="page">{tr}Upload{/tr}</li>
		</ol>
	</div>

	{form enctype="multipart/form-data" id="photoupload" class="fe-upload" action="`$smarty.const.FISHEYE_PKG_URL`upload.php"}
		<input type="hidden" name="gallery_id" value="{$feTarget.gallery_id}" data-fe-target-id />
		<input type="hidden" name="gallery_additions[]" value="{$feTarget.gallery_id}" data-fe-target-id />
		<input type="hidden" name="save_image" value="save" />

		<div class="fe-head">
			<div class="fe-head-main">
				<h1>{tr}Upload photos{/tr}</h1>
				<p class="fe-sub">
					{if $feTarget}
						{tr}Adding to{/tr} <b data-fe-target-title>{$feTarget.title|escape}</b>
					{else}
						<span data-fe-target-none>{tr}A first gallery will be created for you{/tr}: </span><b data-fe-target-title>{$feNewGalleryName|escape}</b>
					{/if}
					<a href="#" class="lnk fe-change" data-fe-target-change hidden>{tr}Change gallery{/tr}</a>
				</p>
			</div>
		</div>

		{formfeedback note=$quotaMessage}
		{formfeedback error=$errors}

		<div class="fe-drop" data-fe-drop>
			{booticon iname="fa-upload"}
			<p><b>{tr}Drop photos or zip files here{/tr}</b></p>
			<p class="fe-sub">
				{tr}or{/tr} <label class="lnk" for="fe-files">{tr}choose files{/tr}</label> {tr}from this device.{/tr} {tr}The largest file accepted is{/tr} {$uploadMax} MB.
			</p>
			{* no name: the script sends each chosen file itself, so the form must not carry them again *}
			<input type="file" id="fe-files" class="fe-filesinput" multiple data-fe-files data-fe-max="{$feUploadMaxBytes}" />
		</div>

		<noscript>
			<p class="fe-nojs">
				<input type="file" name="file0" /> <input type="submit" class="fe-btn fe-btn-primary" value="{tr}Upload file{/tr}" />
			</p>
		</noscript>

		{* one line: strip would join the attributes of a wrapped tag together *}
		<div class="fe-uploadlist" data-fe-uploadlist hidden data-fe-added="{tr}added{/tr}" data-fe-of="{tr}of{/tr}" data-fe-skipped="{tr}skipped{/tr}" data-fe-failed="{tr}failed{/tr}" data-fe-s-waiting="{tr}Waiting{/tr}" data-fe-s-sending="{tr}Uploading{/tr}" data-fe-s-working="{tr}Processing{/tr}" data-fe-s-done="{tr}Added{/tr}" data-fe-s-large="{tr}Too large{/tr}" data-fe-s-empty="{tr}Empty file{/tr}" data-fe-s-error="{tr}Could not be uploaded{/tr}" data-fe-leave="{tr}Photos are still uploading.{/tr}">
			<div class="fe-listhead">
				<b data-fe-uploadsummary></b>
				<span class="fe-bar-sp"></span>
				<a class="fe-btn fe-btn-primary" href="{if $feTarget}{$feTarget.display_url|escape}{else}{$smarty.const.FISHEYE_PKG_URL}?user_id={$gBitUser->mUserId}{/if}" data-fe-uploaddone {if $onComplete}data-fe-oncomplete="{$onComplete|escape}"{/if} hidden>{booticon iname="fa-images"}{tr}View gallery{/tr}</a>
			</div>
			<ul class="fe-files" data-fe-filelist></ul>
		</div>

		<details class="fe-options">
			<summary>{tr}Options{/tr}</summary>
			<div class="fe-optgrid">
				{if function_exists('exif_read_data')}
					<label class="fe-check"><input type="checkbox" name="rotate_image" value="auto" checked="checked" /> <span>{tr}Turn photos upright{/tr}<small>{tr}Uses the orientation the camera recorded.{/tr}</small></span></label>
				{/if}
				<label class="fe-check"><input type="checkbox" name="use_filenames" value="true" /> <span>{tr}Name photos after their files{/tr}<small>{tr}Otherwise the name comes from the photo's own data when it has one.{/tr}</small></span></label>
				{if $gBitUser->hasPermission( 'p_fisheye_upload_nonimages' )}
					<label class="fe-check"><input type="checkbox" name="process_archive" value="true" checked="checked" /> <span>{tr}Unpack zip files{/tr}<small>{tr}Each folder inside becomes a gallery.{/tr}</small></span></label>
				{else}
					<input type="hidden" name="process_archive" value="true" />
				{/if}
				<label class="fe-field fe-resize" for="resize">{tr}Reduce originals to{/tr}
					<select name="resize" id="resize" class="fe-select">
						<option value="">{tr}Keep the original size{/tr}</option>
						<option value="1024">1024 {tr}pixels on the long side{/tr}</option>
						<option value="1600">1600 {tr}pixels on the long side{/tr}</option>
						<option value="2048">2048 {tr}pixels on the long side{/tr}</option>
						<option value="2560">2560 {tr}pixels on the long side{/tr}</option>
						<option value="3264">3264 {tr}pixels on the long side{/tr}</option>
					</select>
				</label>
			</div>
			<div class="fe-services">
				{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"}
				{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_upload_mini_tpl"}
				{jstabs}
					{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_tab_tpl"}
					{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_upload_tab_tpl"}
				{/jstabs}
			</div>
		</details>
	{/form}

	{* destination dialog, filled by the page script from the signed-in user's galleries *}
	<dialog class="fe-app fe-picker" data-fe-picker data-fe-url="{$smarty.const.FISHEYE_PKG_URL}organize.php" data-fe-tk="{$gBitUser->mTicket}" data-fe-gallery-url="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id=" aria-label="{tr}Choose a gallery{/tr}">
		<h2>{tr}Add photos to{/tr}</h2>
		<label class="fe-search">{booticon iname="fa-search"}<input type="search" data-fe-picker-find placeholder="{tr}Find a gallery{/tr}" autocomplete="off" /></label>
		<ul data-fe-picker-list data-fe-none="{tr}No galleries match.{/tr}"></ul>
		{if $gBitUser->hasPermission( 'p_fisheye_create' )}
				<div class="fe-picker-new"><input type="text" class="fe-input" data-fe-picker-name maxlength="160" placeholder="{tr}New gallery name{/tr}" aria-label="{tr}New gallery name{/tr}" /><button type="button" class="fe-btn" data-fe-picker-create>{booticon iname="fa-folder-plus"}{tr}Create{/tr}</button></div>
			{/if}
			<p class="fe-picker-foot"><button type="button" class="fe-btn" data-fe-picker-cancel>{tr}Cancel{/tr}</button></p>
	</dialog>
</div>
{/strip}
