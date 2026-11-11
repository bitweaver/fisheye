{* Image order: exact positions and titles for every image of a gallery. *}
{strip}
<div class="fe-app fe-order">
	<div class="fe-top">
		<ol class="fe-crumb">
			<li><a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gContent->mInfo.user_id}">{tr}Galleries{/tr}</a></li>
			<li><a href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gContent->mGalleryId}">{$gContent->getTitle()|escape}</a></li>
			<li aria-current="page">{tr}Image order{/tr}</li>
		</ol>
		<div class="fe-tools">
			<a href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gContent->mGalleryId}&amp;mode=organize" title="{tr}Select, move, rotate and drag photos{/tr}">{booticon iname="fa-th"}<span class="l">{tr}Organize{/tr}</span></a>
			<a href="{$smarty.const.FISHEYE_PKG_URL}edit.php?gallery_id={$gContent->mGalleryId}" title="{tr}Settings{/tr}">{booticon iname="fa-cog"}<span class="l">{tr}Settings{/tr}</span></a>
		</div>
	</div>

	<div class="fe-head">
		<div class="fe-head-main">
			<h1>{tr}Image order{/tr}</h1>
			<p class="fe-sub">{$feList.cant} {if $feList.cant eq 1}{tr}photo{/tr}{else}{tr}photos{/tr}{/if} {tr}in{/tr} {$gContent->getTitle()|escape}</p>
			<p class="fe-desc">{tr}Drag a row by its handle, or type a position. Dragging keeps the numbers in place and hands them to the rows in their new order. Nothing is stored until you save.{/tr}</p>
		</div>
	</div>

	{formfeedback hash=$formfeedback}

	{if $feList.cant > $feList.max_records}
		<p class="fe-empty">{tr}This gallery holds more photos than this page can save at once. Only the first are shown.{/tr}</p>
	{/if}

	{if $feItems}
	{form id="batch_order" class="fe-orderform"}
		<input type="hidden" name="gallery_id" value="{$gContent->mGalleryId}" />

		<div class="fe-bar fe-orderbar">
			<label class="fe-field" for="reorder_gallery">{tr}Re-order all by{/tr}</label>
			<select name="reorder_gallery" id="reorder_gallery" class="fe-select">
				<option value="">{tr}Typed positions{/tr}</option>
				<option value="photo_date">{tr}Photo date{/tr}</option>
				<option value="upload_date">{tr}Date uploaded{/tr}</option>
				<option value="caption">{tr}Title{/tr}</option>
				<option value="file_name">{tr}File name{/tr}</option>
				<option value="random">{tr}Random{/tr}</option>
			</select>
			<button type="button" class="fe-btn" data-fe-renumber title="{tr}Fill the position boxes with 10, 20, 30 in the order shown{/tr}">{booticon iname="fa-sort"}<span class="l">{tr}Renumber by tens{/tr}</span></button>
			<span class="fe-bar-sp"></span>
			<span class="fe-orderdirty" data-fe-dirty hidden>{tr}Unsaved changes{/tr}</span>
			<input type="submit" class="fe-btn" name="cancel" value="{tr}Back{/tr}" />
			<input type="submit" class="fe-btn fe-btn-primary" name="save_order" value="{tr}Save{/tr}" />
		</div>

		<div class="fe-rows-wrap">
			<table class="fe-rows fe-ordertable">
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th>{tr}Title{/tr}</th>
						<th>{tr}File{/tr}</th>
						<th title="{tr}Your favorites{/tr}">{booticon iname="fa-heart"}</th>
						<th title="{tr}Select for the batch command{/tr}"><input type="checkbox" data-fe-checkall="batch[]" aria-label="{tr}Select all{/tr}" /></th>
						<th class="n">{tr}Position{/tr}</th>
					</tr>
				</thead>
				<tbody>
					{assign var=fePage value=0}
					{foreach from=$feItems item=item key=contentId}
						{if $item.page_break}
							{assign var=fePage value=$fePage+1}
							<tr class="fe-pagebreak"><th colspan="7">{tr}Gallery page{/tr} {$fePage}</th></tr>
						{/if}
						<tr data-fe-row>
							<td class="g"><button type="button" class="fe-grip" data-fe-grip title="{tr}Drag to reorder. With the keyboard, use the up and down arrows.{/tr}" aria-label="{tr}Reorder{/tr}">{booticon iname="fa-grip-vertical"}</button></td>
							<td><a href="{$item.display_url|escape}"><img src="{$item.thumbnail_uri|escape}" alt="" loading="lazy" /></a></td>
							<td class="t"><input type="text" class="fe-input" name="image_title[{$contentId}]" value="{$item.title|escape}" maxlength="160" aria-label="{tr}Title{/tr}" /></td>
							<td class="m">
								{$item.file_name|escape}
								<small>
									{if $item.taken_text}{tr}Taken{/tr} {$item.taken_text} &middot; {/if}{tr}Uploaded{/tr} {$item.uploaded_text}
									 &middot; <a class="lnk" href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$contentId}&amp;gallery_id={$gContent->mGalleryId}">{tr}Edit details{/tr}</a>
								</small>
							</td>
							<td><label class="fe-favbox" title="{tr}Favorite{/tr}"><input type="checkbox" name="is_favorite[]" value="{$contentId}" {if $item.is_favorite}checked="checked"{/if} aria-label="{tr}Favorite{/tr}" />{booticon iname="fa-heart"}</label></td>
							<td><input type="checkbox" name="batch[]" value="{$contentId}" aria-label="{tr}Select{/tr}" /></td>
							<td class="n"><input type="text" class="fe-input fe-pos" inputmode="decimal" maxlength="15" name="imagePosition[{$contentId}]" value="{$item.position_text}" data-fe-position aria-label="{tr}Position{/tr}" /></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>

		<div class="fe-orderfoot">
			<span class="fe-bar-sp"></span>
			<label class="fe-field" for="batch_command">{tr}With selected{/tr}</label>
			<select name="batch_command" id="batch_command" class="fe-select">
				<option value=""></option>
				<option value="thumbnail">{tr}Regenerate thumbnails{/tr}</option>
				{if $gBitUser->hasPermission('p_fisheye_admin')}
					<option value="filenametoimagename">{tr}Rename from file name{/tr} ({tr}cannot be undone{/tr})</option>
				{/if}
				{if $gBitSystem->getConfig('image_processor') eq 'magickwand'}
					<option value="grayscale">{tr}Convert to grayscale{/tr} ({tr}cannot be undone{/tr})</option>
				{/if}
				{if $gBitSystem->isPackageActive( 'gatekeeper' )}
					<optgroup label="{tr}Set security to{/tr}">
						<option value="security:">{tr}None (publicly visible){/tr}</option>
						{foreach from=$securities key=secId item=sec}
							<option value="security:{$secId}">{$sec.security_description|escape}</option>
						{/foreach}
					</optgroup>
				{/if}
			</select>
			<input type="submit" class="fe-btn fe-btn-primary" name="save_order" value="{tr}Save{/tr}" />
		</div>
		<p class="fe-sub">{tr}The heart marks your favorites. Move, copy, rotate, remove, delete and the gallery cover are in Organize mode on the gallery page.{/tr}</p>
	{/form}
	{else}
		<div class="fe-empty"><p>{tr}This gallery has no photos yet.{/tr}</p></div>
	{/if}
</div>
{/strip}
