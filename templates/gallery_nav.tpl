{strip}
	<div class="gallerybar">
		<span class="path">
			{displayname user=$gContent->mInfo.creator_user user_id=$gContent->mInfo.creator_user_id real_name=$gContent->mInfo.creator_real_name} :: <a href="{$gBitLoc.FISHEYE_PKG_URL}?user_id={$gContent->mInfo.user_id}">{tr}Galleries{/tr}</a> &raquo;
			{$gContent->getBreadcrumbLinks()}
		</span>

		{if $gBitSystem->isPackageActive( 'categories' )}
			{include file="bitpackage:categories/categories_nav.tpl"}
		{/if}

		{if $gGallery}
			<span class="navigation">
				<span class="left">
					{if $gGallery->mInfo.previous_image_id}
						<a href="{$gContent->getDisplayUrl($gGallery->mInfo.previous_image_id)|escape}">
							{if $gallerybar_use_icons eq 'y'}
								{biticon ipackage=liberty iname=nav_prev iexplain=previous}
							{else}
								&laquo;&nbsp;{tr}previous{/tr}
							{/if}
						</a>
					{else}&nbsp;{/if}
				</span>

				<span class="right">
					{if $gGallery->mInfo.next_image_id}
						<a href="{$gContent->getDisplayUrl($gGallery->mInfo.next_image_id)|escape}">
							{if $gallerybar_use_icons eq 'y'}
								{biticon ipackage=liberty iname=nav_next iexplain=next}
							{else}
								{tr}next{/tr}&nbsp;&raquo;
							{/if}
						</a>
					{else}&nbsp;{/if}
				</span>
			</span><!-- end .navigation -->
		{/if}

		<div class="clear"></div>
	</div><!-- end .structure -->
{/strip}