{strip}
{if $thumbnailList or $showEmpty}
	<div class="listing fisheye">
		<div class="header">
			<h2>{$fisheye_center_params.title|default:"{tr}Random Images{/tr}"}</h2>
		</div>

		<div class="clear"></div>

		<div class="body">
			{foreach from=$thumbnailList key=galleryId item=img}
				<a rel="ugc" href="{$img.display_url}">
					<img class="thumb" src="{$img.thumbnail_url}" alt="{$img.title|escape}" title="{$img.title|escape}" />
				</a>
			{foreachelse}
				{tr}No records found{/tr}
			{/foreach}
			<p><a rel="ugc" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gQueryUserId}">{tr}View More{/tr}...</a></p>
		</div><!-- end .body -->
	</div><!-- end .fisheye -->
{/if}
{/strip}
