{* Gallery settings, and the form for a new gallery. *}
{strip}
<div class="fe-app fe-settings">
	<div class="fe-top">
		<ol class="fe-crumb">
			<li><a href="{$gContent->getLibraryUrl()|escape}">{tr}Galleries{/tr}</a></li>
			{if $gContent->isValid()}
				<li><a href="{feurl gallery=$gContent->mGalleryId}">{$gContent->getTitle()|escape}</a></li>
				<li aria-current="page">{tr}Settings{/tr}</li>
			{else}
				<li aria-current="page">{tr}New gallery{/tr}</li>
			{/if}
		</ol>
		{if $gContent->isValid()}
			<div class="fe-tools">
				<a href="{feurl gallery=$gContent->mGalleryId mode="organize"}" title="{tr}Select, move, rotate and drag photos{/tr}">{booticon iname="fa-th"}<span class="l">{tr}Organize{/tr}</span></a>
				<a href="{feurl page="image_order"}?gallery_id={$gContent->mGalleryId}" title="{tr}Exact positions and titles{/tr}">{booticon iname="fa-sort"}<span class="l">{tr}Image order{/tr}</span></a>
			</div>
		{/if}
	</div>

	<div class="fe-head">
		<div class="fe-head-main">
			<h1>{if $gContent->isValid()}{tr}Gallery settings{/tr}{else}{tr}New gallery{/tr}{/if}</h1>
		</div>
	</div>

	{formfeedback error=$errors warning=$feWarnings}

	{feurl page="edit" assign="feAction"}
	{form id="editGalleryForm" class="fe-form" action=$feAction}
		<input type="hidden" name="gallery_id" value="{$galleryId|escape}" />

		<div class="fe-fs">
			<h2>{tr}Name{/tr}</h2>
			<div>
				<input type="text" class="fe-input fe-input-lg" name="title" id="gallery-title" value="{if $smarty.request.title}{$smarty.request.title|escape}{else}{$gContent->getTitle()|escape}{/if}" maxlength="160" required="required" placeholder="{tr}Name this gallery{/tr}" aria-label="{tr}Name{/tr}" />
				<textarea class="fe-input" name="edit" id="gallery-desc" rows="4" placeholder="{tr}Describe this gallery{/tr}" aria-label="{tr}Description{/tr}">{if $smarty.request.edit}{$smarty.request.edit|escape}{else}{$gContent->mInfo.data|escape}{/if}</textarea>
			</div>
		</div>

		<div class="fe-fs">
			<h2>{tr}Opens in{/tr}</h2>
			<div>
				<div class="fe-vcards">
					{foreach from=$feLayouts item=modeName key=modeKey}
						<label class="fe-vcard">
							<input type="radio" name="gallery_pagination" value="{$modeKey}" {if $modeKey eq $feLayout}checked="checked"{/if} />
							{if $modeKey eq 'organize'}{booticon iname="fa-th"}
							{elseif $modeKey eq 'justified'}{booticon iname="fa-images"}
							{elseif $modeKey eq 'masonry'}{booticon iname="fa-th-large"}
							{else}{booticon iname="fa-th-list"}{/if}
							<b>{tr}{$modeName}{/tr}</b>
							<span>{$feLayoutNotes.$modeKey}</span>
						</label>
					{/foreach}
				</div>
				<p class="fe-sub">{tr}Visitors can still switch view on the gallery page.{/tr}</p>
			</div>
		</div>

		{if $gContent->isValid()}
			<div class="fe-fs">
				<h2>{tr}Cover{/tr}</h2>
				<div class="fe-coverpick">
					{if $feCover}<img src="{$feCover|escape}" alt="" />{/if}
					<div>
						<p>{if !$feCover}{tr}This gallery has no photos yet.{/tr}{elseif $gContent->mInfo.preview_content_id}{tr}A photo you chose.{/tr}{else}{tr}Picked automatically, until you choose one.{/tr}{/if}</p>
						{if $feCover}
							<a class="fe-btn" href="{feurl gallery=$gContent->mGalleryId mode="organize"}">{booticon iname="fa-image"}{tr}Choose in Organize{/tr}</a>
							<p class="fe-sub">{tr}Point at a photo there and click its star.{/tr}</p>
						{/if}
					</div>
				</div>
			</div>
		{/if}

		<div class="fe-fs">
			<h2>{tr}Inside{/tr}</h2>
			<div>
				<ul class="fe-parents" data-fe-parents data-fe-self="{$gContent->mGalleryId}">
					{foreach from=$feParents item=parentGallery key=parentId}
						<li><label class="fe-check"><input type="checkbox" name="gallery_additions[]" value="{$parentId}" checked="checked" /> <span>{booticon iname="fa-folder"} {$parentGallery.title|escape}</span></label></li>
					{/foreach}
				</ul>
				{* the row the page script copies for a gallery chosen in the dialog *}
				<template data-fe-parent-row><li><label class="fe-check"><input type="checkbox" name="gallery_additions[]" value="" checked="checked" /> <span>{booticon iname="fa-folder"} <span data-fe-parent-title></span></span></label></li></template>
				<p class="fe-sub" data-fe-parents-none {if $feParents}hidden{/if}>{tr}A top-level gallery. It is not inside another gallery.{/tr}</p>
				<p><button type="button" class="fe-btn" data-fe-parent-add hidden>{booticon iname="fa-folder-plus"}{tr}Put inside a gallery{/tr}</button></p>
				<p class="fe-sub">{tr}A gallery can sit inside more than one gallery. Untick one to take it out when you save.{/tr}</p>
			</div>
		</div>

		<div class="fe-fs">
			<h2>{tr}Sharing{/tr}</h2>
			<div class="fe-checks">
				<label class="fe-check"><input type="checkbox" name="allow_comments" id="allow_comments" value="y" {if !$gContent->isValid() || $gContent->getPreference('allow_comments') eq 'y'}checked="checked"{/if} /> <span>{tr}Let visitors comment{/tr}</span></label>
				<label class="fe-check"><input type="checkbox" name="link_original_images" id="link_original_images" value="y" {if $gContent->getPreference('link_original_images') eq 'y'}checked="checked"{/if} /> <span>{tr}Let visitors download original files{/tr}</span></label>
				{if $gBitUser->hasPermission('p_fisheye_create_public_gal')}
					<label class="fe-check"><input type="checkbox" name="is_public" id="is_public" value="y" {if $gContent->getPreference('is_public') eq 'y'}checked="checked"{/if} /> <span>{tr}Let other members add photos{/tr}<small>{tr}Anyone who can upload may add to this gallery.{/tr}</small></span></label>
				{/if}
				<div class="fe-services">
					{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"}
					{jstabs}
						{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_tab_tpl"}
					{/jstabs}
				</div>
			</div>
		</div>

		<div class="fe-formfoot">
			<input type="submit" class="fe-btn fe-btn-primary" name="savegallery" value="{if $gContent->isValid()}{tr}Save changes{/tr}{else}{tr}Create gallery{/tr}{/if}" />
			<input type="submit" class="fe-btn" name="cancelgallery" value="{tr}Cancel{/tr}" formnovalidate="formnovalidate" />
		</div>
	{/form}

	{if $feCanDelete}
		<div class="fe-form">
			<div class="fe-fs danger">
				<h2>{tr}Delete{/tr}</h2>
				<div>
					<p>{tr}The next step asks whether to keep photos that also appear in other galleries. Nothing is deleted until you confirm there.{/tr}</p>
					<a class="fe-btn" href="{feurl page="edit"}?gallery_id={$gContent->mGalleryId}&amp;delete=1">{booticon iname="fa-trash-alt"}{tr}Delete gallery{/tr}</a>
				</div>
			</div>
		</div>
	{/if}

	{* parent dialog, filled by the page script from the signed-in user's galleries *}
	<dialog class="fe-app fe-picker" data-fe-picker data-fe-url="{feurl page="organize"}" data-fe-tk="{$gBitUser->mTicket}" aria-label="{tr}Choose a gallery{/tr}">
		<h2>{tr}Put inside{/tr}</h2>
		<label class="fe-search">{booticon iname="fa-search"}<input type="search" data-fe-picker-find placeholder="{tr}Find a gallery{/tr}" autocomplete="off" /></label>
		<ul data-fe-picker-list data-fe-none="{tr}No galleries match.{/tr}"></ul>
		{if $gBitUser->hasPermission( 'p_fisheye_create' )}
				<div class="fe-picker-new"><input type="text" class="fe-input" data-fe-picker-name maxlength="160" placeholder="{tr}New gallery name{/tr}" aria-label="{tr}New gallery name{/tr}" /><button type="button" class="fe-btn" data-fe-picker-create>{booticon iname="fa-folder-plus"}{tr}Create{/tr}</button></div>
			{/if}
			<p class="fe-picker-foot"><button type="button" class="fe-btn" data-fe-picker-cancel>{tr}Cancel{/tr}</button></p>
	</dialog>
</div>
{/strip}
