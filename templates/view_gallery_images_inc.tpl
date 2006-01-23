{strip}
{if $gBitSystem->mPrefs.fisheye_gallery_div_layout eq 'y'}
	{if $browserInfo.browser eq 'ie'}
		<!-- we need this friggin table for MSIE that images don't float outside of the designated area - once again a hack for our favourite browser - grrr -->
		<table style="border:0;border-collapse:collapse;border-spacing:0; width:auto;"><tr><td>
	{/if}
	<div class="thumbnailblock">
		{foreach from=$gContent->mItems item=galItem key=itemContentId}
			{box class="box `$gContent->mInfo.thumbnail_size`-thmb `$galItem->mInfo.content_type_guid`"}
				<a href="{$galItem->getDisplayUrl()|escape}">
					<img class="thumb" src="{$galItem->getThumbnailUrl()}" alt="{$galItem->mInfo.title|default:'image'}" />
				</a>
				{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_titles' )}
					<h3>{$galItem->mInfo.title}</h3>
				{/if}
				{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_descriptions' )}
					<p>{$galItem->mInfo.data}</p>
				{/if}
			{/box}
		{foreachelse}
			<div class="norecords">{tr}This gallery is empty{/tr}. <a href="{$smarty.const.FISHEYE_PKG_URL}upload.php?gallery_id={$gContent->mGalleryId}">Upload pictures!</a></div>
		{/foreach}
	</div>
	{if $browserInfo.browser eq 'ie'}
		</td></tr></table>
	{/if}
	<div class="clear"></div>
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
				{box class="box `$galItem->mInfo.content_type_guid`"}
					<a href="{$galItem->getDisplayUrl()|escape}">
						<img class="thumb" src="{$galItem->getThumbnailUrl()}" alt="{$galItem->mInfo.title|default:'image'}" />
					</a>
					{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_titles' )}
						<h2>{$galItem->mInfo.title}</h2>
					{/if}
					{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_descriptions' )}
						<p>{$galItem->mInfo.data}</p>
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
