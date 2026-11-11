{strip}
{assign var=feUrl value="`$feBaseUrl`&mode=`$feMode`"}
{if $feList.sort ne 'position'}{assign var=feUrl value="`$feUrl`&sort=`$feList.sort`"}{/if}
<div class="fe-app{if $feDark} fe-stage{/if}" data-fe-themed>
<div class="fe-shell{if $feWide} is-rail-closed{/if}" data-fe-shell="{if $feWide}stage{else}light{/if}">
	{include file="bitpackage:fisheye/library_rail_inc.tpl"}
	<div class="fe-main">
	<a class="fe-btn fe-btn-quiet fe-railopen" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gContent->mInfo.user_id}" data-fe-rail-toggle aria-expanded="false">{booticon iname="fa-columns"}{tr}Library{/tr}</a>
	<div class="fe-top">
		<ol class="fe-crumb">
			<li><a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gContent->mInfo.user_id}">{tr}Galleries{/tr}</a></li>
			{foreach from=$gContent->getBreadcrumbLinks(1) item=breadTitle key=breadId}
				{if $breadId eq $gContent->mGalleryId}
					<li aria-current="page">{$breadTitle|escape}</li>
				{else}
					<li><a href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$breadId}">{$breadTitle|escape}</a></li>
				{/if}
			{/foreach}
		</ol>
		{* quiet tools on the breadcrumb line, so the title and description keep the full width *}
		<div class="fe-tools">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}
			{if $gContent->hasUserPermission( 'p_fisheye_download_gallery_arc' )}
				<a title="{tr}Download Gallery{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gContent->mGalleryId}&amp;download=1">{booticon iname="fa-download"}<span class="l">{tr}Download{/tr}</span></a>
			{/if}
			{if $gContent->hasUpdatePermission()}
				<a title="{tr}Image Order{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}image_order.php?gallery_id={$gContent->mGalleryId}">{booticon iname="fa-sort"}<span class="l">{tr}Image Order{/tr}</span></a>
				<a title="{tr}Settings{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit.php?gallery_id={$gContent->mGalleryId}">{booticon iname="fa-cog"}<span class="l">{tr}Settings{/tr}</span></a>
			{/if}
			{if $gContent->hasUpdatePermission() || $gContent->getPreference('is_public')}
				<a title="{tr}Upload{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}upload.php?gallery_id={$gContent->mGalleryId}">{booticon iname="fa-upload"}<span class="l">{tr}Upload{/tr}</span></a>
			{/if}
		</div>
	</div>

	<div class="fe-head">
		<div class="fe-head-main">
			<h1>{$gContent->getTitle()|escape}</h1>
			<p class="fe-sub">
				{$feList.cant} {if $feList.cant eq 1}{tr}photo{/tr}{else}{tr}photos{/tr}{/if}
				{if $gContent->getPreference('is_public')} &middot; {tr}Public{/tr}{/if}
			</p>
			{if $gContent->mInfo.data}<p class="fe-desc">{$gContent->mInfo.data|escape}</p>{/if}
		</div>
	</div>

	{formfeedback success=$fisheyeSuccess error=$fisheyeErrors warning=$fisheyeWarnings}
	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}
	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gContent->mInfo}

	<div class="fe-bar">
		<nav class="fe-seg" aria-label="{tr}View{/tr}">
			{foreach from=$feModes item=modeName key=modeKey}
				<a href="{$feBaseUrl|escape}&amp;mode={$modeKey}{if $feList.sort ne 'position'}&amp;sort={$feList.sort}{/if}" {if $modeKey eq $feMode}aria-current="true"{/if} title="{tr}{$modeName}{/tr}">
					{if $modeKey eq 'organize'}{booticon iname="fa-th"}
					{elseif $modeKey eq 'justified'}{booticon iname="fa-images"}
					{elseif $modeKey eq 'masonry'}{booticon iname="fa-th-large"}
					{else}{booticon iname="fa-th-list"}{/if}
					<span>{tr}{$modeName}{/tr}</span>
				</a>
			{/foreach}
		</nav>
		{include file="bitpackage:fisheye/theme_toggle_inc.tpl"}
		<span class="fe-bar-sp"></span>
		{if $feList.cant > 1}
			<form class="fe-field" method="get" action="{$smarty.const.FISHEYE_PKG_URL}view.php">
				<input type="hidden" name="gallery_id" value="{$gContent->mGalleryId}" />
				<input type="hidden" name="mode" value="{$feMode}" />
				<label for="fe-sort">{tr}Sort{/tr}</label>
				<select id="fe-sort" name="sort" class="fe-select" data-fe-submit>
					{html_options options=$feSortLabels selected=$feList.sort}
				</select>
				<noscript><button class="fe-btn" type="submit">{tr}Apply{/tr}</button></noscript>
			</form>
		{/if}
	</div>

	{if $feSubItems}
		{* a long list of nested galleries folds away so the photos stay near the top *}
		{assign var=feSubFold value=24}
		<div class="fe-subs">
			{foreach from=$feSubItems item=sub name=subs}
				{if $smarty.foreach.subs.index eq $feSubFold}
					</div>
					<details class="fe-subs-more">
						<summary class="fe-btn">{tr}Show all galleries{/tr} ({$feSubItems|@count})</summary>
						<div class="fe-subs">
				{/if}
				<a class="fe-subchip" href="{$sub.display_url|escape}">
					{if $sub.thumbnail_uri}<img src="{$sub.thumbnail_uri|escape}" alt="" loading="lazy" />{else}{booticon iname="fa-folder"}{/if}
					{$sub.title|escape}
					{if $sub.num_images}<span>{$sub.num_images}</span>{/if}
				</a>
			{/foreach}
		</div>
		{if $feSubItems|@count > $feSubFold}</details>{/if}
	{/if}

	{if $feList.page > 1}
		<p class="fe-more"><a class="fe-btn" href="{$feUrl|escape}&amp;page={$feList.page-1}">{booticon iname="fa-angle-left"}{tr}Earlier photos{/tr}</a></p>
	{/if}

	{if !$feItems}
		{if !$feSubItems}
			<div class="fe-empty">
				<p>{tr}This gallery is empty{/tr}</p>
				{if $gContent->hasUpdatePermission() || $gContent->getPreference('is_public')}
					<a class="fe-btn fe-btn-primary" href="{$smarty.const.FISHEYE_PKG_URL}upload.php?gallery_id={$gContent->mGalleryId}">{booticon iname="fa-upload"}{tr}Upload photos{/tr}</a>
				{/if}
			</div>
		{/if}
	{elseif $feMode eq 'details'}
		<div class="fe-rows-wrap">
			<table class="fe-rows">
				<thead>
					<tr>
						<th></th>
						<th><a href="{$feBaseUrl|escape}&amp;mode=details&amp;sort={if $feList.sort eq 'title_asc'}title_desc{else}title_asc{/if}">{tr}Title{/tr}</a></th>
						<th>{tr}File{/tr}</th>
						<th><a href="{$feBaseUrl|escape}&amp;mode=details&amp;sort={if $feList.sort eq 'taken_desc'}taken_asc{else}taken_desc{/if}">{tr}Taken{/tr}</a></th>
						<th><a href="{$feBaseUrl|escape}&amp;mode=details&amp;sort={if $feList.sort eq 'uploaded_desc'}uploaded_asc{else}uploaded_desc{/if}">{tr}Uploaded{/tr}</a></th>
						<th>{tr}Dimensions{/tr}</th>
						<th><a href="{$feBaseUrl|escape}&amp;mode=details&amp;sort={if $feList.sort eq 'size_desc'}size_asc{else}size_desc{/if}">{tr}Size{/tr}</a></th>
					</tr>
				</thead>
				<tbody data-fe-items>
					{include file="bitpackage:fisheye/gallery_items_inc.tpl"}
				</tbody>
			</table>
		</div>
	{else}
		<div class="{if $feMode eq 'organize'}fe-grid{elseif $feMode eq 'masonry'}fe-mason{else}fe-just{/if}" data-fe-items {if $feMode ne 'organize'}data-fe-lightbox{elseif $feCanEdit}data-fe-organize data-fe-url="{$smarty.const.FISHEYE_PKG_URL}organize.php" data-fe-gallery="{$gContent->mGalleryId}" data-fe-tk="{$gBitUser->mTicket}" {if $feList.sort eq 'position'}data-fe-sortable{/if}{/if}>
			{include file="bitpackage:fisheye/gallery_items_inc.tpl"}
		</div>
	{/if}

	{if $feList.page < $feList.num_pages}
		<p class="fe-more">
			<a class="fe-btn" href="{$feUrl|escape}&amp;page={$feList.page+1}" data-fe-more data-fe-page="{$feList.page+1}" data-fe-pages="{$feList.num_pages}">{tr}More photos{/tr}</a>
		</p>
	{/if}

	{if $feCanEdit && $feMode eq 'organize'}
		{* shown while photos are selected *}
		<div class="fe-bulk" data-fe-bulk role="toolbar" aria-label="{tr}Selected photos{/tr}" hidden>
			<b><span data-fe-count>0</span> {tr}selected{/tr}</b>
			<a href="#" class="x" data-fe-act="clear" title="{tr}Clear the selection{/tr}">{booticon iname="fa-times"}<span>{tr}Clear{/tr}</span></a>
			<span class="div"></span>
			<a href="#" data-fe-act="move" title="{tr}Move to another gallery{/tr}">{booticon iname="fa-folder-open"}<span>{tr}Move{/tr}</span></a>
			<a href="#" data-fe-act="copy" title="{tr}Copy to another gallery{/tr}">{booticon iname="fa-copy"}<span>{tr}Copy{/tr}</span></a>
			<a href="#" data-fe-act="rotate" data-fe-degrees="270" title="{tr}Rotate left{/tr}">{booticon iname="fa-undo"}<span>{tr}Left{/tr}</span></a>
			<a href="#" data-fe-act="rotate" data-fe-degrees="90" title="{tr}Rotate right{/tr}">{booticon iname="fa-redo"}<span>{tr}Right{/tr}</span></a>
			<a href="#" data-fe-act="remove" title="{tr}Remove from this gallery, keep the photo{/tr}">{booticon iname="fa-minus-circle"}<span>{tr}Remove{/tr}</span></a>
			<a href="#" data-fe-act="delete" data-fe-confirm="{tr}Delete the selected photos? They are removed from every gallery and this cannot be undone.{/tr}" data-fe-confirm-cancel="{tr}Cancel{/tr}" title="{tr}Delete{/tr}">{booticon iname="fa-trash-alt"}<span>{tr}Delete{/tr}</span></a>
			<span class="sp"></span>
			<a href="#" data-fe-labels aria-pressed="false" title="{tr}Show or hide the button names{/tr}">{booticon iname="fa-question-circle"}</a>
		</div>
		<dialog class="fe-app fe-picker" data-fe-picker data-fe-url="{$smarty.const.FISHEYE_PKG_URL}organize.php" data-fe-tk="{$gBitUser->mTicket}" aria-label="{tr}Choose a gallery{/tr}">
			<h2 data-fe-picker-title data-fe-move="{tr}Move to{/tr}" data-fe-copy="{tr}Copy to{/tr}">{tr}Move to{/tr}</h2>
			<label class="fe-search">{booticon iname="fa-search"}<input type="search" data-fe-picker-find placeholder="{tr}Find a gallery{/tr}" autocomplete="off" /></label>
			<ul data-fe-picker-list data-fe-none="{tr}No galleries match.{/tr}"></ul>
			{if $gBitUser->hasPermission( 'p_fisheye_create' )}
				<div class="fe-picker-new"><input type="text" class="fe-input" data-fe-picker-name maxlength="160" placeholder="{tr}New gallery name{/tr}" aria-label="{tr}New gallery name{/tr}" /><button type="button" class="fe-btn" data-fe-picker-create>{booticon iname="fa-folder-plus"}{tr}Create{/tr}</button></div>
			{/if}
			<p class="fe-picker-foot"><button type="button" class="fe-btn" data-fe-picker-cancel>{tr}Cancel{/tr}</button></p>
		</dialog>
	{/if}

	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}

	{if $gContent->getPreference('allow_comments') eq 'y'}
		<div class="fe-comments">{include file="bitpackage:liberty/comments.tpl"}</div>
	{/if}
	</div>
</div>
</div>
{/strip}
