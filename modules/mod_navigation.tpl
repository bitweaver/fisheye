{strip}
{if $gGallery}
	{bitmodule title="$moduleTitle" name="fisheye_navigation"}
		<div class="pull-left">
			{if $gGallery->mInfo.previous_image_id}
				<a href="{$gContent->getImageUrl($gGallery->mInfo.previous_image_id)|escape}">
					<img src="{$gGallery->mInfo.previous_image_avatar}" />
					<br />
					&laquo;&nbsp;{tr}previous{/tr}
				</a>
			{else}&nbsp;{/if}
		</div>

		<div class="pull-right">
			{if $gGallery->mInfo.next_image_id}
				<a href="{$gContent->getImageUrl($gGallery->mInfo.next_image_id)|escape}">
					<img src="{$gGallery->mInfo.next_image_avatar}" />
					<br />
					{tr}next{/tr}&nbsp;&raquo;
				</a>
			{else}&nbsp;{/if}
		</div>
	{/bitmodule}
{/if}
{/strip}

