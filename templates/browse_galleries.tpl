{strip}

<div class="display fisheye">
	<div class="header">
		<h1>{tr}Image Galleries{/tr}</h1>
	</div>

	<div class="body">

		<table class="data">
			<tr>
				<th><a href="{$gBitLoc.FISHEYE_PKG_URL}browse.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'title_desc'}title_asc{else}title_desc{/if}">{tr}Gallery Name{/tr}</a></th>
			</tr>

			{section name=ix loop=$galleryList}
				<tr>
					<td>{$galleryList[ix].title}</td>
				</tr>
			{/section}
		</table>

	</div>	<!-- end .body -->
</div>	<!-- end .fisheye -->

{/strip}
