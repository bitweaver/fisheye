{strip}
{if $thumbnailList or $showEmpty}
	<div class="listing fisheye">
		<div class="header">
			<h1>{tr}Random Images{/tr}</h1>
		</div>

		<div class="clear"></div>

		<div class="body">
			{foreach from=$thumbnailList key=galleryId item=gal}
				{if $fisheye_list_thumbnail eq 'y'}
					<a href="{$gal.display_url}">
						<img class="thumb" src="{$gal.thumbnail_url}" alt="{$gal.title}" title="{$gal.title}" />
					</a>
				{/if}
			{foreachelse}
				{tr}No records found{/tr}
			{/foreach}
			<p><a href="{$gBitLoc.FISHEYE_PKG_URL}list_galleries.php?user_id={$gQueryUserId}">{tr}View More{/tr}...</a></p>
		</div><!-- end .body -->
	</div><!-- end .fisheye -->
{/if}
{/strip}
