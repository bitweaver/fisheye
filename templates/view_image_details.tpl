<div class="display fisheye">
	<div class="gallerybar">
		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}
	</div>
	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gContent->mInfo}
	<div class="image">
		{if $gBitSystem->isFeatureActive('fisheye_image_list_description') and $gContent->mInfo.data ne ''}
			<p class="description">{$gContent->mInfo.parsed_data}</p>
		{/if}
		</div>
</div>	<!-- end .body -->

{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}

{if $gGallery && $gGallery->getPreference('allow_comments') eq 'y'}
	{include file="bitpackage:liberty/comments.tpl"}
{/if}


