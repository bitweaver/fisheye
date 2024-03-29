<div class="header">
	{include file="bitpackage:fisheye/gallery_icons_inc.tpl"}
	<h1>{$gContent->getTitle()|escape}</h1>
	<nav>
		{assign var=breadCrumbs value=$gContent->getBreadcrumbLinks(1)}
		<ol class="breadcrumb">
			<li>{displayname user=$gContent->mInfo.creator_user user_id=$gContent->mInfo.creator_user_id real_name=$gContent->mInfo.creator_real_name} :: <a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gContent->mInfo.user_id}">{tr}Galleries{/tr}</a></li>
			{if $breadCrumbs}
				{foreach from=$breadCrumbs item=breadTitle key=breadId}
					{if $breadId==$gContent->mGalleryId}<li class="active">{$breadTitle}</li>
					{else}<li><a href="#" onclick="changePhotoDrawer('{$breadId}');return false;">{$breadTitle}</a></li>{/if}
				{/foreach}
			{/if}
		</ol>
	</nav>
</div>

	{assign var=thumbsize value='small'}
	<table class="data">
		<caption>{tr}List of files{/tr} <span class="total">[ {$galInfo.total_records|default:0} ]</span></caption>
		<tr>
			{if $thumbsize}
				<th style="width:1%"></th>
			{/if}
			<th style="width:60%">
				{smartlink ititle=Name isort=title icontrol=$galInfo}
			</th>
			{if $gBitSystem->isFeatureActive( 'fisheye_item_list_date' ) || $gBitSystem->isFeatureActive( 'fisheye_item_list_creator' )}
				<th style="width:10%">
					{smartlink ititle=Uploaded isort=created iorder=desc idefault=1 icontrol=$galInfo}
				</th>
			{/if}
			{if $gBitSystem->isFeatureActive( 'fisheye_item_list_size' )}
				<th style="width:10%">{tr}Size{/tr} /<br />{tr}Duration{/tr}</th>
			{/if}
			{if $gBitSystem->isFeatureActive( 'fisheye_item_list_hits' )}
				<th style="width:10%">
					{smartlink ititle=Downloads isort="lch.hits" icontrol=$galInfo}
				</th>
			{/if}
			<th style="width:20%">{tr}Actions{/tr}</th>
		</tr>
			{foreach from=$gContent->mItems item=galItem}
			<tr class="{cycle values="odd,even"}">
				{if $thumbsize}
					<td style="text-align:center;">
						{if $galItem->mInfo.content_type_guid != 'fisheyegallery' }
							{if $gBitSystem->isFeatureActive( 'site_fancy_zoom' )}
								{if $gContent->hasUpdatePermission() || $gGallery && $gGallery->getPreference( 'link_original_images' )}
									<a href="{$galItem->getDownloadUrl()|escape}">
								{else}
									<a href="{$galItem->mInfo.thumbnail_url.large}">
								{/if}
							{/if}
							<img src="{$galItem->mInfo.thumbnail_url.$thumbsize}" alt="{$galItem->getTitle()|escape}" title="{$galItem->getTitle()|escape}" />
							{if $gBitSystem->isFeatureActive( 'site_fancy_zoom' )}
								</a>
							{/if}
						{else}
							<a href="{$galItem->getDisplayUrl()|escape}">
								<img class="thumb" src="{$galItem->getThumbnailUri()}" alt="{$galItem->getTitle()|escape|default:'image'}" />
							</a>
						{/if}
					</td>
				{/if}
				<td>
					<h3><a href="{$galItem->getDisplayUrl()}">{$galItem->getTitle()|escape}</a></h3>
					{if $gBitSystem->isFeatureActive( 'fisheye_item_list_desc' ) && $galItem->mInfo.data}
						{$galItem->mInfo.parsed_data}
					{/if}
					{if $gBitSystem->isFeatureActive( 'fisheye_item_list_attid' )}
						<small>{$galItem->mInfo.wiki_plugin_link}</small>
						{assign var=br value=1}
					{/if}
					{if $gBitSystem->isFeatureActive( 'fisheye_item_list_name' )}
						{if $br}<br />{/if}
						{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
							<a href="{$galItem->getDisplayUrl()}">
						{/if}
						{$galItem->mInfo.filename} <small>({$galItem->mInfo.mime_type})</small>
						{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
							</a>
						{/if}
					{/if}
				</td>
				{if $gBitSystem->isFeatureActive( 'fisheye_item_list_date' ) || $gBitSystem->isFeatureActive( 'fisheye_item_list_creator' )}
					<td>
						{if $gBitSystem->isFeatureActive( 'fisheye_item_list_date' )}
							{$galItem->mInfo.created|bit_short_date}<br />
						{/if}
						{if $gBitSystem->isFeatureActive( 'fisheye_item_list_creator' )}
							{tr}by{/tr}: {displayname hash=$galItem->mInfo}
						{/if}
					</td>
				{/if}
				{if $gBitSystem->isFeatureActive( 'fisheye_item_list_size' )}
					<td style="text-align:right;">
						{if $galItem->mInfo.download_url}
							{$galItem->mInfo.file_size|display_bytes}
						{/if}
						{if $galItem->mInfo.prefs.duration}
							{if $galItem->mInfo.download_url} / {/if}{$galItem->mInfo.prefs.duration|display_duration}
						{/if}
					</td>
				{/if}
				{if $gBitSystem->isFeatureActive( 'fisheye_item_list_hits' )}
					<td style="text-align:right;">
						{$galItem->mInfo.hits|default:"{tr}none{/tr}"}
					</td>
				{/if}
				<td class="actionicon">
					{if $galItem->mInfo.content_type_guid != 'fisheyegallery' }
						{if $gBitUser->hasPermission( 'p_treasury_download_item' ) && $galItem->mInfo.download_url}
							<a href="{$galItem->mInfo.download_url}">{biticon ipackage="icons" iname="emblem-downloads" iexplain="Download File"}</a>
						{/if}
						{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
							<a href="{$galItem->getDisplayUrl()}">{booticon iname="fa-folder-open" iexplain="View File"}</a>
						{/if}
						{if $gContent->isOwner( $galItem->mInfo ) || $gBitUser->isAdmin()}
							<a href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$galItem->mInfo.content_id}&amp;action=edit">{booticon iname="fa-pencil" iexplain="Edit File"}</a>
							<a href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$galItem->mInfo.content_id}&amp;delete=1">{booticon iname="fa-trash" iexplain="Remove File"}</a>
						{/if}
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>


