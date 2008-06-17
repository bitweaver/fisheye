{strip}
{if $gContent->getLayout() == 'auto_flow'}
	{if $gBrowserInfo.browser eq 'ie'}
		<!-- we need this friggin table for MSIE that images don't float outside of the designated area - once again a hack for our favourite browser - grrr -->
		<table style="border:0;border-collapse:collapse;border-spacing:0; width:auto;"><tr><td>
	{/if}
	<div class="thumbnailblock">
		{foreach from=$gContent->mItems item=galItem key=itemContentId}
			{box class="box `$gContent->mInfo.thumbnail_size`-thmb `$galItem->mInfo.content_type_guid`"}
				{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$galItem->mInfo type=mini}
				{include file=$gLibertySystem->getMimeTemplate('view',$galItem->mInfo.attachment_plugin_guid) attachment=$galItem->mInfo}
				{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_titles' )}
					<h2>{$galItem->mInfo.title|escape}</h2>
				{/if}
				{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_descriptions' )}
					<p>{$galItem->mInfo.data|escape}</p>
				{/if}
			{/box}
		{foreachelse}
			<div class="norecords">{tr}This gallery is empty{/tr}. <a href="{$smarty.const.FISHEYE_PKG_URL}upload.php?gallery_id={$gContent->mGalleryId}">Upload pictures!</a></div>
		{/foreach}
	</div>
	{if $gBrowserInfo.browser eq 'ie'}
		</td></tr></table>
	{/if}
	<div class="clear"></div>
{elseif $gContent->getLayout() == 'simple_list'}
	{assign var=thumbsize value='small'}
	<table class="data">
		<caption>{tr}List of files{/tr} <span class="total">[ {$galInfo.total_records|default:0} ]</span></caption>
		<tr>
			{if $thumbsize}
				<th style="width:1%"></th>
			{/if}
			<th style="width:60%">
				{smartlink ititle=Name isort=title icontrol=$galInfo structure_id=$gContent->mStructureId}
			</th>
			{if $gBitSystem->isFeatureActive( 'fisheye_item_list_date' ) || $gBitSystem->isFeatureActive( 'fisheye_item_list_creator' )}
				<th style="width:10%">
					{smartlink ititle=Uploaded isort=created iorder=desc idefault=1 icontrol=$galInfo structure_id=$gContent->mStructureId}
				</th>
			{/if}
			{if $gBitSystem->isFeatureActive( 'fisheye_item_list_size' )}
				<th style="width:10%">{tr}Size{/tr} /<br />{tr}Duration{/tr}</th>
			{/if}
			{if $gBitSystem->isFeatureActive( 'fisheye_item_list_hits' )}
				<th style="width:10%">
					{smartlink ititle=Downloads isort="lch.hits" icontrol=$galInfo structure_id=$gContent->mStructureId}
				</th>
			{/if}
			<th style="width:20%">{tr}Actions{/tr}</th>
		</tr>
			{foreach from=$gContent->mItems item=item}
			<tr class="{cycle values="odd,even"}">
				{if $thumbsize}
					<td style="text-align:center;">
						{if $gBitSystem->isFeatureActive( 'site_fancy_zoom' )}
							{if $gContent->hasEditPermission() || $gGallery && $gGallery->getPreference( 'link_original_images' )}
								<a href="{$item->mInfo.source_url|escape}">
							{else}
								<a href="{$item->mInfo.thumbnail_url.large}">
							{/if}
						{/if}
						<img src="{$item->mInfo.thumbnail_url.$thumbsize}" alt="{$item->mInfo.title|escape}" title="{$item->mInfo.title|escape}" />
						{if $gBitSystem->isFeatureActive( 'site_fancy_zoom' )}
							</a>
						{/if}
					</td>
				{/if}
				<td>
					<h3><a href="{$item->mInfo.display_url}">{$item->mInfo.title|escape}</a></h3>
					{if $gBitSystem->isFeatureActive( 'fisheye_item_list_desc' ) && $item->mInfo.data}
						{$item->mInfo.parsed_data}
					{/if}
					{if $gBitSystem->isFeatureActive( 'fisheye_item_list_attid' )}
						<small>{$item->mInfo.wiki_plugin_link}</small>
						{assign var=br value=1}
					{/if}
					{if $gBitSystem->isFeatureActive( 'fisheye_item_list_name' )}
						{if $br}<br />{/if}
						{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
							<a href="{$item->mInfo.display_url}">
						{/if}
						{$item->mInfo.filename} <small>({$item->mInfo.mime_type})</small>
						{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
							</a>
						{/if}
					{/if}
				</td>
				{if $gBitSystem->isFeatureActive( 'fisheye_item_list_date' ) || $gBitSystem->isFeatureActive( 'fisheye_item_list_creator' )}
					<td>
						{if $gBitSystem->isFeatureActive( 'fisheye_item_list_date' )}
							{$item->mInfo.created|bit_short_date}<br />
						{/if}
						{if $gBitSystem->isFeatureActive( 'fisheye_item_list_creator' )}
							{tr}by{/tr}: {displayname hash=$item->mInfo}
						{/if}
					</td>
				{/if}
				{if $gBitSystem->isFeatureActive( 'fisheye_item_list_size' )}
					<td style="text-align:right;">
						{if $item->mInfo.download_url}
							{$item->mInfo.file_size|display_bytes}
						{/if}
						{if $item->mInfo.prefs.duration}
							{if $item->mInfo.download_url} / {/if}{$item->mInfo.prefs.duration|display_duration}
						{/if}
					</td>
				{/if}
				{if $gBitSystem->isFeatureActive( 'fisheye_item_list_hits' )}
					<td style="text-align:right;">
						{$item->mInfo.hits|default:"{tr}none{/tr}"}
					</td>
				{/if}
				<td class="actionicon">
					{if $gBitUser->hasPermission( 'p_treasury_download_item' ) && $item->mInfo.download_url}
						<a href="{$item->mInfo.download_url}">{biticon ipackage="icons" iname="emblem-downloads" iexplain="Download File"}</a>
					{/if}
					{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
						<a href="{$item->mInfo.display_url}">{biticon ipackage="icons" iname="document-open" iexplain="View File"}</a>
					{/if}
					{if $gContent->isOwner( $item->mInfo ) || $gBitUser->isAdmin()}
						<a href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$item->mInfo.content_id}&amp;action=edit">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit File"}</a>
						<a href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$item->mInfo.content_id}&amp;delete=1">{biticon ipackage="icons" iname="edit-delete" iexplain="Remove File"}</a>
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>

{else}
	<table class="thumbnailblock">
		{counter assign="imageCount" start="0" print=false}
		{assign var="max" value=100}
		{assign var="tdWidth" value="`$max/$cols_per_page`"}
		{foreach from=$gContent->mItems item=galItem key=itemContentId}
			{if $imageCount % $cols_per_page == 0}
				<tr > <!-- Begin Image Row -->
			{/if}

			<td style="width:{$tdWidth}%; vertical-align:top;"> <!-- Begin Image Cell -->
				{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$galItem->mInfo type=mini}
				{box class="box `$galItem->mInfo.content_type_guid`"}
					{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_titles' )}
						<h2>{$galItem->mInfo.title|escape}</h2>
					{/if}
					<a href="{$galItem->getDisplayUrl()|escape}">
						<img class="thumb" src="{$galItem->getThumbnailUri()}" alt="{$galItem->mInfo.title|escape|default:'image'}" />
					</a>
					{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_descriptions' )}
						<p>{$galItem->mInfo.data|escape}</p>
					{/if}
				{/box}
			</td> <!-- End Image Cell -->
			{counter}

			{if $imageCount % $cols_per_page == 0}
				</tr> <!-- End Image Row -->
			{/if}

		{foreachelse}
			<tr><td class="norecords">{tr}This gallery is empty{/tr}. <a href="{$smarty.const.FISHEYE_PKG_URL}upload.php?gallery_id={$gContent->mGalleryId}">Upload pictures!</a></td></tr>
		{/foreach}

		{if $imageCount % $cols_per_page != 0}</tr>{/if}
	</table>
{/if}
{/strip}
