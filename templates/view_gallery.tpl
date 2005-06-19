{strip}
{include file="bitpackage:fisheye/gallery_nav.tpl"}

<div class="listing fisheye">
	<div class="header">
		<div class="floaticon">
			{if $gBitSystem->isPackageActive( 'pdf' ) && $gContent->hasUserPermission( 'bit_p_pdf_generation' )}
				{if $structureInfo.root_structure_id}
					<a title="{tr}create PDF{/tr}" href="{$gBitLoc.PDF_PKG_URL}?structure_id={$structureInfo.root_structure_id}">{biticon ipackage="pdf" iname="pdf" iexplain="PDF"}</a>
				{else}
					<a title="{tr}create PDF{/tr}" href="{$gBitLoc.PDF_PKG_URL}?content_id={$gContent->mContentId}">{biticon ipackage="pdf" iname="pdf" iexplain="PDF"}</a>
				{/if}
			{/if}
			{if $gContent->hasUserPermission( 'bit_p_edit_fisheye' )}
				<a title="{tr}Edit{/tr}" href="{$gBitLoc.FISHEYE_PKG_URL}edit.php?gallery_id={$gContent->mGalleryId}">{biticon ipackage=liberty iname="config" iexplain="Edit"}</a>
				<a title="{tr}Image Order{/tr}" href="{$gBitLoc.FISHEYE_PKG_URL}image_order.php?gallery_id={$gContent->mGalleryId}">{biticon ipackage=liberty iname="current" iexplain="Image Order"}</a>
			{/if}
			{if $gContent->hasUserPermission( 'bit_p_upload_fisheye' )}
				<a title="{tr}Add Image{/tr}" href="{$gBitLoc.FISHEYE_PKG_URL}upload.php?gallery_id={$gContent->mGalleryId}">{biticon ipackage=liberty iname="upload" iexplain="Add Image"}</a>
			{/if}
			{if $gContent->hasUserPermission( 'bit_p_admin_fisheye' )}
				<a title="{tr}User Permissions{/tr}" href="{$gBitLoc.FISHEYE_PKG_URL}edit.php?gallery_id={$gContent->mGalleryId}&amp;delete=1">{biticon ipackage=liberty iname="delete" iexplain="Delete Gallery"}</a>
				<a title="{tr}User Permissions{/tr}" href="{$gBitLoc.FISHEYE_PKG_URL}edit_gallery_perms.php?gallery_id={$gContent->mGalleryId}">{biticon ipackage=liberty iname="permissions" iexplain="User Permissions"}</a>
			{/if}
		</div>

		<h1>{$gContent->mInfo.title}</h1>

		{if $gContent->mInfo.data}
			<h2>{$gContent->mInfo.data}</h2>
		{/if}
	</div>

	<div class="body">
		{formfeedback success=$fisheyeSuccess error=$fisheyeErrors warning=$fisheyeWarnings}
		<table class="thumbnailblock">
			{counter assign="imageCount" start="0" print=false}
			{assign var="max" value=100}
			{assign var="tdWidth" value="`$max/$cols_per_page`"}
			{section name=ix loop=$gContent->mItems}
				{assign var=item value=$gContent->mItems[ix]}
				{if $imageCount % $cols_per_page == 0}
					<tr > <!-- Begin Image Row -->
				{/if}

				<td style="width:{$tdWidth}%; vertical-align:top;"> <!-- Begin Image Cell -->
					{box class="box `$gContent->mItems[ix]->mInfo.content_type_guid`"}
						<a href="{$gContent->mItems[ix]->getDisplayUrl()|escape}">
							<img class="thumb" src="{$gContent->mItems[ix]->getThumbnailUrl()}" alt="{$gContent->mItems[ix]->mInfo.title|default:'image'}" />
						</a>
						{if $gBitSystemPrefs.fisheye_gallery_list_image_titles eq 'y'}
							<h2>{$gContent->mItems[ix]->mInfo.title}</h2>
						{/if}
						{if $gBitSystemPrefs.fisheye_gallery_list_image_descriptions eq 'y'}
							<p>{$gContent->mImages[ix]->mInfo.data}</p>
						{/if}
					{/box}
				</td> <!-- End Image Cell -->
				{counter}

				{if $imageCount % $cols_per_page == 0}
					</tr> <!-- End Image Row -->
				{/if}

			{sectionelse}
				<tr><td class="norecords">{tr}This gallery is empty{/tr}. <a href="{$gBitLoc.FISHEYE_PKG_URL}upload.php?gallery_id={$gContent->mGalleryId}">Upload pictures!</a></td></tr>
			{/section}

			{if $imageCount % $cols_per_page != 0}</tr>{/if}
		</table>
	</div>	<!-- end .body -->
	{libertypagination numPages=$gContent->mInfo.num_pages gallery_id=$gContent->mGalleryId gallery_path=$gContent->mGalleryPath page=$page}
	{if $gBitSystem->isPackageActive( 'categories' )}
		{include file="bitpackage:categories/categories_objects.tpl"}
	{/if}

</div>	<!-- end .fisheye -->

{/strip}
