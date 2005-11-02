{strip}
{include file="bitpackage:fisheye/gallery_nav.tpl"}

<div class="display fisheye">
	<div class="floaticon">
	{if $gBitSystem->isPackageActive( 'pdf' ) && $gContent->hasUserPermission( 'bit_p_pdf_generation' )}
		{if $structureInfo.root_structure_id}
			<a title="{tr}create PDF{/tr}" href="{$smarty.const.PDF_PKG_URL}index.php?structure_id={$structureInfo.root_structure_id}">{biticon ipackage="pdf" iname="pdf" iexplain="PDF"}</a>
		{else}
			<a title="{tr}create PDF{/tr}" href="{$smarty.const.PDF_PKG_URL}index.php?content_id={$gContent->mContentId}">{biticon ipackage="pdf" iname="pdf" iexplain="PDF"}</a>
		{/if}
	{/if}
	{if $gContent->hasUserPermission('bit_p_admin')}
			<a title="{tr}Edit{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?image_id={$gContent->mImageId}">{biticon ipackage=liberty iname="edit" iexplain="Edit Image"}</a>
			<a title="{tr}Delete{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?image_id={$gContent->mImageId}&amp;delete=1">{biticon ipackage=liberty iname="delete" iexplain="Delete Image"}</a>
	{/if}
	</div>

	{formfeedback hash=$feedback}

	<div class="header">
		<h1>{$gGallery->mInfo.title}</h1>
	</div>

	<div class="body">
		{box class="box image"}
			<img src="{$gContent->mInfo.display_url}{$refresh}" alt="{$gContent->mInfo.title|default:$gContent->mInfo.image_file.filename}" title="{$gContent->mInfo.data|default:$gContent->mInfo.filename}" />

			{if $gBitSystem->isFeatureActive( 'fisheye_image_list_title' )}
				<h1>{$gContent->mInfo.title|default:$gContent->mInfo.image_file.filename}</h1>
			{/if}

			{if $gBitSystem->isFeatureActive( 'fisheye_image_list_description' ) and $gContent->mInfo.data ne ''}
				<p>{$gContent->mInfo.data}</p>
			{/if}
		{/box}

		<div class="pagination">
				{tr}View other sizes{/tr}<br />
				{foreach key=size from=$gContent->mInfo.image_file.thumbnail_url item=url}
				{if $url != $gContent->mInfo.display_url}<a href="{$gContent->getDisplayUrl(0,$size)|escape}">{/if}{$size}{if $url != $gContent->mInfo.display_url}</a>{/if}&nbsp;&bull;&nbsp;
				{/foreach}
				<a href="{$gContent->mInfo.image_file.source_url}">Original</a>
			{if $gContent->mInfo.width && $gContent->mInfo.height}
				{$gContent->mInfo.width}x{$gContent->mInfo.height}
			{/if}
		</div>
	</div>	<!-- end .body -->

	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view'}

</div>	<!-- end .fisheye -->

{/strip}
