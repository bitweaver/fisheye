{strip}
	<div class="gallerybar">
		<span class="path">
			{if $gGallery}
				{displayname user=$gGallery->mInfo.creator_user user_id=$gGallery->mInfo.creator_user_id real_name=$gGallery->mInfo.creator_real_name} :: <a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gGallery->mInfo.user_id}">{tr}Galleries{/tr}</a> &raquo;
			{else}
				{displayname user=$gContent->mInfo.creator_user user_id=$gContent->mInfo.creator_user_id real_name=$gContent->mInfo.creator_real_name} :: <a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gContent->mInfo.user_id}">{tr}Galleries{/tr}</a> &raquo;
			{/if}
			{$gContent->getBreadcrumbLinks()}
		</span>

		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}

		{if $gGallery}
			<span class="navigation">
				<span class="left">
					{if $gGallery->mInfo.previous_image_id}
						<a href="{$gContent->getDisplayUrl($gGallery->mInfo.previous_image_id)|escape}">
							{if $gallerybar_use_icons eq 'y'}
								{biticon ipackage="icons" iname="go-previous" iexplain=previous}
							{else}
								&laquo;&nbsp;{tr}previous{/tr}
							{/if}
							{if $gBitSystem->isFeatureActive( 'gallery_bar_use_thumbnails' )}
								<br />
								<img src="{$gGallery->mInfo.previous_image_avatar}" />
							{/if}
						</a>
					{else}&nbsp;{/if}
				</span>

				<span class="right">
					{if $gGallery->mInfo.next_image_id}
						<a href="{$gContent->getDisplayUrl($gGallery->mInfo.next_image_id)|escape}">
							{if $gallerybar_use_icons eq 'y'}
								{biticon ipackage="icons" iname="go-next" iexplain=next}
							{else}
								{tr}next{/tr}&nbsp;&raquo;
							{/if}
							{if $gBitSystem->isFeatureActive( 'gallery_bar_use_thumbnails' )}
								<br />
								<img src="{$gGallery->mInfo.next_image_avatar}" />
							{/if}
						</a>
					{else}&nbsp;{/if}
				</span>
			</span><!-- end .navigation -->
		{/if}

		<div class="clear"></div>
	</div><!-- end .structure -->
{/strip}
