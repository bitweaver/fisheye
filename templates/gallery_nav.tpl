{strip}
	{if $gGallery->mInfo.previous_image_id or $gGallery->mInfo.next_image_id}
		<div class="gallerynav">
	{else}
		<div class="gallerybar">
	{/if}
		<div class="path">
			{assign var=breadCrumbs value=$gContent->getBreadcrumbLinks()}
			{if $gGallery}
				{displayname user=$gGallery->mInfo.creator_user user_id=$gGallery->mInfo.creator_user_id real_name=$gGallery->mInfo.creator_real_name} :: <a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gGallery->mInfo.user_id}">{tr}Galleries{/tr}</a> &raquo;{if $breadCrumbs}{$breadCrumbs}{else}{$gGallery->getTitle()}{/if}
			{else}
				{displayname user=$gContent->mInfo.creator_user user_id=$gContent->mInfo.creator_user_id real_name=$gContent->mInfo.creator_real_name} :: <a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gContent->mInfo.user_id}">{tr}Galleries{/tr}</a> &raquo; {if $breadCrumbs}{$breadCrumbs}{else}{$gContent->getTitle()}{/if}
			{/if}
		</div>

		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}

		{if $gGallery}
			<div class="navigation">
				<span class="left">
					{if $gGallery->mInfo.previous_image_id}
						<a href="{$gContent->getImageUrl($gGallery->mInfo.previous_image_id)|escape}">
							{if $gBitSystem->isFeatureActive( 'gallerybar_use_icons' )}
								{booticon iname="icon-arrow-left"  ipackage="icons"  iexplain=previous iforce="icon"}
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
						<a href="{$gContent->getImageUrl($gGallery->mInfo.next_image_id)|escape}">
							{if $gBitSystem->isFeatureActive( 'gallerybar_use_icons' )}
								{biticon ipackage="icons" iname="go-next" iexplain=next iforce="icon"}
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
			</div><!-- end .navigation -->
		{/if}

		<div class="clear"></div>
	</div><!-- end .gallerybar -->
{/strip}
