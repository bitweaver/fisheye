{strip}
{include file="bitpackage:fisheye/gallery_nav.tpl"}

<div class="display fisheye">
	<div class="floaticon">
	{if $gBitSystem->isPackageActive( 'pdf' ) && $gContent->hasUserPermission( 'bit_p_pdf_generation' )}
		{if $structureInfo.root_structure_id}
			<a title="{tr}create PDF{/tr}" href="{$gBitLoc.PDF_PKG_URL}index.php?structure_id={$structureInfo.root_structure_id}">{biticon ipackage="pdf" iname="pdf" iexplain="PDF"}</a>
		{else}
			<a title="{tr}create PDF{/tr}" href="{$gBitLoc.PDF_PKG_URL}index.php?content_id={$gContent->mContentId}">{biticon ipackage="pdf" iname="pdf" iexplain="PDF"}</a>
		{/if}
	{/if}
	{if $gContent->hasUserPermission('bit_p_admin')}
			<a title="{tr}Edit{/tr}" href="{$gBitLoc.FISHEYE_PKG_URL}edit_image.php?image_id={$gContent->mImageId}">{biticon ipackage=liberty iname="edit" iexplain="Edit Image"}</a>
			<a title="{tr}Delete{/tr}" href="{$gBitLoc.FISHEYE_PKG_URL}edit_image.php?image_id={$gContent->mImageId}&amp;delete=1">{biticon ipackage=liberty iname="delete" iexplain="Delete Image"}</a>
	{/if}
	</div>

	<div class="header">
		<h1>{$gGallery->mInfo.title}</h1>
	</div>

	<div class="body">
		{box class="box image"}
			<img src="{$gContent->mInfo.display_url}{$refresh}" alt="{$gContent->mInfo.title|default:$gContent->mInfo.image_file.filename}" title="{$gContent->mInfo.data|default:$gContent->mInfo.filename}" />

			{if $gBitSystemPrefs.fisheye_image_list_title eq 'y'}
				<h1>{$gContent->mInfo.title|default:$gContent->mInfo.image_file.filename}</h1>
			{/if}

			{if $gBitSystemPrefs.fisheye_image_list_description eq 'y' and $gContent->mInfo.data ne ''}
				<p>{$gContent->mInfo.data}</p>
			{/if}
		{/box}

		<div class="pagination">
			{if $gContent->mInfo.width && $gContent->mInfo.height}
				{tr}View other sizes{/tr}<br />
				{foreach key=size from=$gContent->mInfo.image_file.thumbnail_url item=url}
				{if $url != $gContent->mInfo.display_url}<a href="{$gContent->getDisplayUrl(0,$size)|escape}">{/if}{$size}{if $url != $gContent->mInfo.display_url}</a>{/if}&nbsp;&bull;&nbsp;
				{/foreach}
				<a href="{$gContent->mInfo.image_file.source_url}">Original</a> {$gContent->mInfo.width}x{$gContent->mInfo.height}
			{/if}
		</div>
	</div>	<!-- end .body -->
	{if $gBitSystem->isPackageActive( 'categories' )}
		{include file="bitpackage:categories/categories_objects.tpl"}
	{/if}
</div>	<!-- end .fisheye -->

{/strip}
