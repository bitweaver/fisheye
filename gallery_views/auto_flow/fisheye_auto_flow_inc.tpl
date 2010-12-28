{strip}
{include file="bitpackage:fisheye/gallery_nav.tpl"}
<div class="display fisheye">
	<div class="header">
		{include file="bitpackage:fisheye/gallery_icons_inc.tpl"}
		<h1>{$gContent->getTitle()|escape}</h1>
	</div>

	<div class="body">
		{formfeedback success=$fisheyeSuccess error=$fisheyeErrors warning=$fisheyeWarnings}

		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gContent->mInfo}
		{if $gContent->mInfo.data}
			<p>{$gContent->mInfo.data|escape}</p>
		{/if}

		{if $gBrowserInfo.browser eq 'ie'}
			<!-- we need this friggin table for MSIE that images don't float outside of the designated area - once again a hack for our favourite browser - grrr -->
			<table style="border:0;border-collapse:collapse;border-spacing:0; width:auto;"><tr><td>
		{/if}
		<div class="thumbnailblock">
			{foreach from=$gContent->mItems item=galItem key=itemContentId}
				{box class="box `$gContent->mInfo.thumbnail_size`-thmb `$galItem->mInfo.content_type_guid`"}
					{include file=$gLibertySystem->getMimeTemplate('inline',$galItem->mInfo.attachment_plugin_guid) attachment=$galItem->mInfo.image_file}
					{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_titles' )}
						<h2>{$galItem->mInfo.title|escape}</h2>
					{/if}
					{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$galItem->mInfo type=mini}
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

	</div>	<!-- end .body -->

	{libertypagination numPages=$gContent->mInfo.num_pages gallery_id=$gContent->mGalleryId gallery_path=$gContent->mGalleryPath page=$pageCount}

	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}

	{if $gContent->getPreference('allow_comments') eq 'y'}
		{include file="bitpackage:liberty/comments.tpl"}
	{/if}
</div>	<!-- end .fisheye -->
{/strip}
