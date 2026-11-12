{* Confirm deleting a gallery, with the choice of what happens to its contents. *}
{strip}
<div class="fe-app fe-settings">
	<div class="fe-top">
		<ol class="fe-crumb">
			<li><a href="{$gContent->getLibraryUrl()|escape}">{tr}Galleries{/tr}</a></li>
			<li><a href="{feurl gallery=$gContent->mGalleryId}">{$gContent->getTitle()|escape}</a></li>
			<li aria-current="page">{tr}Delete{/tr}</li>
		</ol>
	</div>

	{feurl page="edit" assign="feAction"}
	{form class="fe-confirmpage" action=$feAction}
		<input type="hidden" name="gallery_id" value="{$gContent->mGalleryId}" />
		<input type="hidden" name="delete" value="1" />
		{if $feCover}
			<img src="{$feCover|escape}" alt="" />
		{/if}
		<div>
			<h1>{tr}Delete this gallery?{/tr}</h1>
			<p><b>{$gContent->getTitle()|escape}</b></p>
			<p>
				{* one line: strip would drop the spaces between the parts *}
				{if !$feDelete.items}{tr}It is empty.{/tr}{else}{tr}It holds{/tr} {if $feDelete.photos}{$feDelete.photos} {if $feDelete.photos eq 1}{tr}photo{/tr}{else}{tr}photos{/tr}{/if}{/if}{if $feDelete.photos && $feDelete.galleries} {tr}and{/tr} {/if}{if $feDelete.galleries}{$feDelete.galleries} {if $feDelete.galleries eq 1}{tr}gallery{/tr}{else}{tr}galleries{/tr}{/if}{/if}.{/if}
			</p>
			{if $feDelete.others}
				<p>{$feDelete.others} {if $feDelete.others eq 1}{tr}item belongs to another member and is not deleted.{/tr}{else}{tr}items belong to other members and are not deleted.{/tr}{/if} {tr}Their owners find them under Not in a gallery.{/tr}</p>
			{/if}
			{if $feDelete.galleries || $feDelete.shared}
				<div class="fe-checks">
					<label class="fe-check"><input type="radio" name="recurse" value="" checked="checked" /> <span>{tr}Keep what is used elsewhere{/tr}<small>
						{if $feDelete.shared}{$feDelete.shared} {if $feDelete.shared eq 1}{tr}photo is also in another gallery and stays there.{/tr}{else}{tr}photos are also in other galleries and stay there.{/tr}{/if} {/if}
						{if $feDelete.galleries}{tr}Galleries inside this one are kept.{/tr} {/if}
						{tr}Photos that are only here are deleted.{/tr}
					</small></span></label>
					<label class="fe-check"><input type="radio" name="recurse" value="all" /> <span>{tr}Delete everything inside{/tr}<small>{tr}Every photo and gallery of yours inside is deleted, including those that also appear in other galleries.{/tr}</small></span></label>
				</div>
			{elseif $feDelete.items}
				<p>{tr}Its photos are deleted with it.{/tr}</p>
			{/if}
			<p>{tr}This cannot be undone.{/tr}</p>
			<div class="fe-formfoot">
				<input type="submit" class="fe-btn fe-btn-danger" name="confirm" value="{tr}Delete{/tr}" />
				<a class="fe-btn" href="{feurl page="edit"}?gallery_id={$gContent->mGalleryId}">{tr}Cancel{/tr}</a>
			</div>
		</div>
	{/form}
</div>
{/strip}
