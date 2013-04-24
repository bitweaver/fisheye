{strip}

<div class="display fisheye">
	<div class="header">
		<h1>{tr}Image Galleries{/tr}</h1>
	</div>

	<div class="body">

		<table class="table data">
			<tr>
				<th><a href="{$smarty.const.FISHEYE_PKG_URL}browse.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'title_desc'}title_asc{else}title_desc{/if}">{tr}Gallery Name{/tr}</a></th>
			</tr>

			{section name=ix loop=$galleryList}
				<tr>
					<td>{$galleryList[ix].title|escape}</td>
				</tr>
			{/section}
		</table>

	</div>	<!-- end .body -->
</div>	<!-- end .fisheye -->

{/strip}
