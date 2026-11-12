{* One photo: the photo page, and (as a fragment) the lightbox on gallery pages. *}
{strip}
{if $gContent->getTitle()}{assign var=feLabel value=$gContent->getTitle()}{else}{assign var=feLabel value=$gContent->mInfo.image_file.file_name}{/if}
{if !$feFragment}<div class="fe-app{if $feDark} fe-stage{/if} fe-photo" data-fe-themed>{/if}

<div class="fe-view" data-fe-view data-fe-page="{$feView.page_url|escape}" {if $gContent->hasUpdatePermission() && !$liberty_preview}data-fe-url="{feurl page="organize"}" data-fe-tk="{$gBitUser->mTicket}" data-fe-id="{$gContent->mContentId}" {if $gGallery && $gGallery->mContentId}data-fe-gallery="{$gGallery->mGalleryId}"{/if}{/if} {if $feView.prev_url}data-fe-prev="{$feView.prev_url|escape}"{/if} {if $feView.next_url}data-fe-next="{$feView.next_url|escape}"{/if}>
	<div class="fe-view-top">
		{if $feView.gallery_url}
			<a class="fe-view-back" href="{$feView.gallery_url|escape}" data-fe-close title="{tr}Back to gallery{/tr}">{booticon iname="fa-arrow-left"}<span class="t">{$feView.gallery_title|escape}</span></a>
			{if $feView.position}<span class="n">{$feView.position} {tr}of{/tr} {$feView.cant}</span>{/if}
		{else}
			<a class="fe-view-back" href="{$gContent->getLibraryUrl()|escape}" title="{tr}Galleries{/tr}">{booticon iname="fa-arrow-left"}<span class="t">{tr}Galleries{/tr}</span></a>
		{/if}
		{if !$liberty_preview}
			{include file="bitpackage:fisheye/theme_toggle_inc.tpl"}
			<div class="fe-tools">
				{if !$feFragment}{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}{/if}
				{if $feView.next_url}
					<a href="{$feView.next_url|escape}" data-fe-play title="{tr}Slideshow{/tr}">{booticon iname="fa-play"}</a>
				{/if}
				{if $gContent->mInfo.image_file.original && $gContent->mInfo.image_file.source_url}
					<a rel="nofollow" href="{$gContent->mInfo.image_file.source_url|escape}" title="{tr}Original file{/tr}">{booticon iname="fa-download"}</a>
				{/if}
				{if $gContent->hasUpdatePermission()}
					<a href="#" data-fe-act="rotate" data-fe-degrees="270" title="{tr}Rotate left{/tr}">{booticon iname="fa-undo"}</a>
					<a href="#" data-fe-act="rotate" data-fe-degrees="90" title="{tr}Rotate right{/tr}">{booticon iname="fa-redo"}</a>
					{if $gGallery && $gGallery->hasUpdatePermission() && $feView.position}
						<a href="#" data-fe-act="cover" title="{tr}Use as the gallery cover{/tr}" {if $gGallery->mInfo.preview_content_id eq $gContent->mContentId}class="is-on"{/if}>{booticon iname="fa-star"}</a>
					{/if}
					<a rel="nofollow" href="{feurl page="view_image"}?image_id={$gContent->mImageId}&amp;rethumb=1" title="{tr}Regenerate thumbnails{/tr}">{booticon iname="fa-sync-alt"}</a>
					<a href="{feurl page="edit_image"}?image_id={$gContent->mImageId}" title="{tr}Edit{/tr}">{booticon iname="fa-edit"}</a>
					<a rel="nofollow" href="{feurl page="edit_image"}?image_id={$gContent->mImageId}{if $gGallery && $gGallery->mContentId}&amp;gallery_id={$gGallery->mGalleryId}{/if}&amp;delete=1" title="{tr}Delete{/tr}" data-fe-delete-photo="{$gContent->mContentId}" data-fe-url="{feurl page="organize"}" data-fe-tk="{$gBitUser->mTicket}" data-fe-after="{if $gGallery && $gGallery->isValid()}{$gGallery->getDisplayUrl()|escape}{else}{$gContent->getLibraryUrl()|escape}{/if}" data-fe-confirm="{tr}Delete this photo? It is removed from every gallery it is in. This cannot be undone.{/tr}" data-fe-confirm-ok="{tr}Delete{/tr}" data-fe-confirm-cancel="{tr}Cancel{/tr}">{booticon iname="fa-trash-alt"}</a>
				{/if}
				<a href="#" data-fe-full title="{tr}Full screen{/tr}">{booticon iname="fa-expand"}</a>
				<a href="#" data-fe-info-toggle aria-pressed="true" title="{tr}Details{/tr}">{booticon iname="fa-info-circle"}<span class="l">{tr}Details{/tr}</span></a>
			</div>
		{/if}
	</div>

	<div class="fe-view-body">
		<div class="fe-view-img">
			{if $feView.prev_url}<a class="nav prev" href="{$feView.prev_url|escape}" data-fe-nav title="{tr}Previous{/tr}">{booticon iname="fa-angle-left"}</a>{/if}
			{if $gContent->mInfo.image_file.thumbnail_url.panorama}
				{include file=$gLibertySystem->getMimeTemplate('view',$gContent->mInfo.attachment_plugin_guid) attachment=$gContent->mInfo.image_file}
			{elseif $feView.image_url}
				<img src="{$feView.image_url|escape}{$refresh}" alt="{$feLabel|escape}" />
			{else}
				<p class="fe-view-none">{tr}This photo is still being prepared.{/tr}</p>
			{/if}
			{if $feView.next_url}<a class="nav next" href="{$feView.next_url|escape}" data-fe-nav title="{tr}Next{/tr}">{booticon iname="fa-angle-right"}</a>{/if}
		</div>

		<aside class="fe-info">
			<h1><span data-fe-title>{$feLabel|escape}</span>{if $gContent->hasUpdatePermission() && !$liberty_preview} <a href="#" class="fe-rename" data-fe-rename title="{tr}Rename{/tr}">{booticon iname="fa-edit"}</a>{/if}</h1>
			{if $gContent->mInfo.data ne ''}<div class="fe-desc">{$gContent->mInfo.parsed_data}</div>{/if}
			<dl>
				{if $gContent->mInfo.event_time}
					<div class="r"><dt>{tr}Taken{/tr}</dt><dd>{$gContent->mInfo.event_time|bit_short_datetime}</dd></div>
				{/if}
				<div class="r"><dt>{tr}Uploaded{/tr}</dt><dd>{$gContent->mInfo.created|bit_short_date}</dd></div>
				<div class="r"><dt>{tr}By{/tr}</dt><dd>{displayname hash=$gContent->mInfo}</dd></div>
				{if $gContent->mInfo.image_file.file_name}
					<div class="r"><dt>{tr}File{/tr}</dt><dd>{$gContent->mInfo.image_file.file_name|escape}{if $gContent->mInfo.image_file.file_size}<small>{$gContent->mInfo.image_file.file_size|display_bytes}</small>{/if}</dd></div>
				{/if}
				{if $gContent->mInfo.width && $gContent->mInfo.height}
					<div class="r"><dt>{tr}Pixels{/tr}</dt><dd>{$gContent->mInfo.width} &times; {$gContent->mInfo.height}
						<small>{tr}Prints sharply up to{/tr} {math equation="w/300" w=$gContent->mInfo.width format="%.1f"} &times; {math equation="h/300" h=$gContent->mInfo.height format="%.1f"} {tr}in at 300 dpi{/tr}</small>
					</dd></div>
				{/if}
				{if $gContent->mInfo.parent_galleries}
					<div class="r"><dt>{tr}In galleries{/tr}</dt><dd>
						{foreach from=$gContent->mInfo.parent_galleries item=parentGallery key=parentId}
							<a class="chip" href="{feurl gallery=$parentId}">{$parentGallery.title|escape}</a>
						{/foreach}
					</dd></div>
				{/if}
				{if $feView.sizes}
					<div class="r"><dt>{tr}Sizes{/tr}</dt><dd>
						{foreach from=$feView.sizes item=sizeUrl key=sizeName name=sizes}
							<a rel="nofollow" href="{$sizeUrl|escape}">{tr}{$sizeName|capitalize}{/tr}</a>{if !$smarty.foreach.sizes.last}, {/if}
						{/foreach}
						{if $gContent->mInfo.image_file.original && $gContent->mInfo.image_file.source_url}, <a rel="nofollow" href="{$gContent->mInfo.image_file.source_url|escape}">{tr}Original{/tr}</a>{/if}
					</dd></div>
				{/if}
			</dl>
			{* shown only inside the lightbox, where comments and services are left out *}
			<p class="fe-view-page"><a href="{$feView.page_url|escape}">{tr}Open the photo page{/tr}</a></p>
		</aside>
	</div>

	{if $feView.items|@count > 1}
		<div class="fe-strip">
			{foreach from=$feView.items item=item}
				<a href="{$item.display_url|escape}" data-fe-nav {if $item.content_id eq $gContent->mContentId}aria-current="true"{/if} title="{if $item.title}{$item.title|escape}{else}{$item.file_name|escape}{/if}">
					<img src="{$item.thumbnail_uri|escape}" alt="" />
				</a>
			{/foreach}
		</div>
	{/if}
</div>

{if !$feFragment}
	{if !$liberty_preview}
		<div class="fe-photo-below">
			{formfeedback hash=$feedback}
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gContent->mInfo}
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}
			{if $gGallery && $gGallery->isCommentable()}
				<div class="fe-comments">{include file="bitpackage:liberty/comments.tpl"}</div>
			{/if}
		</div>
	{/if}
	</div>
{/if}
{/strip}
