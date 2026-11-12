{* Confirm deleting one photo. Reached without the page script, or by address; with the script a dialog asks instead. *}
{strip}
{if $gContent->getTitle()}{assign var=feLabel value=$gContent->getTitle()}{else}{assign var=feLabel value=$gContent->mInfo.image_file.file_name}{/if}
<div class="fe-app fe-settings">
	<div class="fe-top">
		<ol class="fe-crumb">
			<li><a href="{$gContent->getLibraryUrl()|escape}">{tr}Galleries{/tr}</a></li>
			{if $gGallery && $gGallery->isValid()}<li><a href="{feurl gallery=$gGallery->mGalleryId}">{$gGallery->getTitle()|escape}</a></li>{/if}
			<li><a href="{$gContent->getDisplayUrl()|escape}">{$feLabel|escape}</a></li>
			<li aria-current="page">{tr}Delete{/tr}</li>
		</ol>
	</div>

	{feurl page="edit_image" assign="feAction"}
	{form class="fe-confirmpage" action=$feAction}
		<input type="hidden" name="image_id" value="{$gContent->mImageId}" />
		{if $gGallery && $gGallery->isValid()}<input type="hidden" name="gallery_id" value="{$gGallery->mGalleryId}" />{/if}
		<input type="hidden" name="delete" value="1" />
		{if $gContent->mInfo.thumbnail_url.medium}
			<img src="{$gContent->mInfo.thumbnail_url.medium|escape}" alt="" />
		{/if}
		<div>
			<h1>{tr}Delete this photo?{/tr}</h1>
			<p><b>{$feLabel|escape}</b></p>
			<p>
				{* one line: strip would drop the space between the sentences *}
				{if $feParentCount > 1}{tr}It is removed from all{/tr} {$feParentCount} {tr}galleries it is in.{/tr}{else}{tr}It is removed from its gallery.{/tr}{/if} {tr}This cannot be undone.{/tr}
			</p>
			<div class="fe-formfoot">
				<input type="submit" class="fe-btn fe-btn-danger" name="confirm" value="{tr}Delete{/tr}" />
				<a class="fe-btn" href="{$gContent->getDisplayUrl()|escape}">{tr}Cancel{/tr}</a>
			</div>
		</div>
	{/form}
</div>
{/strip}
