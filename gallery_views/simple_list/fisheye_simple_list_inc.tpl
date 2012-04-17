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
									<a href="{$galItem->mInfo.source_url|escape}">
								{else}
									<a href="{$galItem->mInfo.thumbnail_url.large}">
								{/if}
							{/if}
							<img src="{$galItem->mInfo.thumbnail_url.$thumbsize}" alt="{$galItem->getTitle()|escape}" title="{$galItem->getTitle()|escape}" />
							{if $gBitSystem->isFeatureActive( 'site_fancy_zoom' )}
								</a>
							{/if}
						{else}
							<a href="{$galItem->getContentUrl()|escape}">
								<img class="thumb" src="{$galItem->getThumbnailUri()}" alt="{$galItem->getTitle()|escape|default:'image'}" />
							</a>				
						{/if}
					</td>
				{/if}
				<td>
					<h3><a href="{$galItem->getContentUrl()}">{$galItem->getTitle()|escape}</a></h3>
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
							<a href="{$galItem->getContentUrl()}">
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
							<a href="{$galItem->getContentUrl()}">{biticon ipackage="icons" iname="document-open" iexplain="View File"}</a>
						{/if}
						{if $gContent->isOwner( $galItem->mInfo ) || $gBitUser->isAdmin()}
							<a href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$galItem->mInfo.content_id}&amp;action=edit">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit File"}</a>
							<a href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$galItem->mInfo.content_id}&amp;delete=1">{biticon ipackage="icons" iname="edit-delete" iexplain="Remove File"}</a>
						{/if}
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>


