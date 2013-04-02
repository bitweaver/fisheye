{strip}
{minifind}

<ul class="inline navbar sortby">
	<li>{booticon iname="icon-circle-arrow-right"  ipackage="icons"  iexplain="sort by"}</li>
	<li>{smartlink ititle="Created" isort="created" numPages=$gContent->mInfo.num_pages gallery_id=$gContent->mGalleryId gallery_path=$gContent->mGalleryPath page=$pageCount}</li>
	<li>{smartlink ititle="Last Modified" isort="last_modified" numPages=$gContent->mInfo.num_pages gallery_id=$gContent->mGalleryId gallery_path=$gContent->mGalleryPath page=$pageCount}</li>
	<li>{smartlink ititle="File Type" isort="file_type" numPages=$gContent->mInfo.num_pages gallery_id=$gContent->mGalleryId gallery_path=$gContent->mGalleryPath page=$pageCount}</li>
	<li>{smartlink ititle="File Size" isort="size" numPages=$gContent->mInfo.num_pages gallery_id=$gContent->mGalleryId gallery_path=$gContent->mGalleryPath page=$pageCount}</li>
</ul>

<table class="clear data">
	<caption>{tr}Downloadable Files{/tr}</caption>
	<tr>
		<th width="1%">&nbsp;</th>
		<th width="49%">
			{smartlink ititle="Title" isort="title" gallery_id=$gContent->mGalleryId gallery_path=$gContent->mGalleryPath page=$pageCount} and
			&nbsp;{smartlink ititle="Filename" isort="filename" gallery_id=$gContent->mGalleryId gallery_path=$gContent->mGalleryPath page=$pageCount}
		</th>
		<th width="50%">Date and Details</th>
	</tr>

	{section name=ix loop=$gContent->mItems}
		<tr class="{cycle values='odd,even'}">
			{assign var=item_id value=$gContent->mItems[ix]->mImageId}
			<td>
				<a href="{$smarty.const.BIT_ROOT_URL}{$gContent->mItems[ix]->mStorage.$item_id.storage_file}">
					<img class="thumb" src="{$gContent->mItems[ix]->getThumbnailUri()}" alt="{$gContent->mItems[ix]->getTitle()|escape|default:'image'}" />
				</a>
			</td>

			<td>
				{if $gBitSystem->isFeatureActive( 'fisheye_gallery_list_image_titles' )}
					<h2>{$gContent->mItems[ix]->getTitle()|escape}</h2>
				{/if}
				<a href="{$smarty.const.BIT_ROOT_URL}{$gContent->mItems[ix]->mStorage.$item_id.storage_file}">
					{$gContent->mItems[ix]->mStorage.$item_id.filename}
				</a>
			</td>

			<td>
				{$gContent->mItems[ix]->mInfo.data}
				<br />
				{tr}Created{/tr}: {$gContent->mItems[ix]->mInfo.created|bit_short_datetime} by {displayname login=$gContent->mItems[ix]->mInfo.creator_user real_name=$gContent->mItems[ix]->mInfo.creator_real_name}
				<br />
				Last Modified: {$gContent->mItems[ix]->mInfo.last_modified|bit_short_datetime} by {displayname login=$gContent->mItems[ix]->mInfo.modifier_user real_name=$gContent->mItems[ix]->mInfo.modifier_real_name}
				<br />
				File Type {$gContent->mItems[ix]->mStorage.$item_id.mime_type}
				<br />
				Size {$gContent->mItems[ix]->mStorage.$item_id.size|display_bytes}
			</td>
		<tr>
	{sectionelse}
		<li class="norecords">{tr}This gallery is empty{/tr}. <a href="{$smarty.const.FISHEYE_PKG_URL}upload.php?gallery_id={$gContent->mGalleryId}">Upload pictures!</a></li>
	{/section}
</table>
{/strip}
