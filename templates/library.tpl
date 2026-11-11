{strip}
<div class="fe-app">
	<div class="fe-head">
		<div class="fe-head-main">
			<h1>{tr}Galleries{/tr}</h1>
			<p class="fe-sub">
				{if $gQueryUserId}{displayname user_id=$gQueryUserId nolink=TRUE} &middot; {/if}
				{$feList.cant} {if $feList.cant eq 1}{tr}gallery{/tr}{else}{tr}galleries{/tr}{/if}
				{if $feList.find} {tr}matching{/tr} &ldquo;{$feList.find|escape}&rdquo; &middot; <a class="lnk" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php{if $gQueryUserId}?user_id={$gQueryUserId}{/if}">{tr}Show all{/tr}</a>{/if}
			</p>
		</div>
		<div class="fe-actions">
			<form class="fe-search" method="get" action="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php" role="search">
				{if $gQueryUserId}<input type="hidden" name="user_id" value="{$gQueryUserId}" />{/if}
				<input type="hidden" name="sort" value="{$feList.sort}" />
				{booticon iname="fa-search"}
				<input type="search" name="find" value="{$feList.find|escape}" placeholder="{tr}Search galleries{/tr}" aria-label="{tr}Search galleries{/tr}" />
			</form>
			<form class="fe-field" method="get" action="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php">
				{if $gQueryUserId}<input type="hidden" name="user_id" value="{$gQueryUserId}" />{/if}
				{if $feList.find}<input type="hidden" name="find" value="{$feList.find|escape}" />{/if}
				<select name="sort" class="fe-select" data-fe-submit aria-label="{tr}Sort{/tr}">
					{html_options options=$feSortLabels selected=$feList.sort}
				</select>
				<noscript><button class="fe-btn" type="submit">{tr}Apply{/tr}</button></noscript>
			</form>
			{if $gBitUser->hasPermission('p_fisheye_create')}
				<a class="fe-btn" href="{$smarty.const.FISHEYE_PKG_URL}edit.php">{booticon iname="fa-folder-plus"}<span class="l">{tr}New gallery{/tr}</span></a>
			{/if}
			{if $gBitUser->hasPermission('p_fisheye_upload')}
				<a class="fe-btn fe-btn-primary" href="{$smarty.const.FISHEYE_PKG_URL}upload.php">{booticon iname="fa-upload"}<span class="l">{tr}Upload{/tr}</span></a>
			{/if}
		</div>
	</div>

	{if $feList.page > 1}
		<p class="fe-more"><a class="fe-btn" href="{$feBaseUrl|escape}&amp;page={$feList.page-1}">{booticon iname="fa-angle-left"}{tr}Earlier galleries{/tr}</a></p>
	{/if}

	{if $galleryList}
		<div class="fe-cards" data-fe-items>
			{include file="bitpackage:fisheye/library_items_inc.tpl"}
		</div>
	{else}
		<div class="fe-empty">
			<p>{if $feList.find}{tr}No gallery matches that search{/tr}{else}{tr}No galleries yet{/tr}{/if}</p>
			{if !$feList.find && $gBitUser->hasPermission('p_fisheye_create')}
				<a class="fe-btn fe-btn-primary" href="{$smarty.const.FISHEYE_PKG_URL}edit.php">{booticon iname="fa-folder-plus"}{tr}New gallery{/tr}</a>
			{/if}
		</div>
	{/if}

	{if $feList.page < $feList.num_pages}
		<p class="fe-more">
			<a class="fe-btn" href="{$feBaseUrl|escape}&amp;page={$feList.page+1}" data-fe-more data-fe-page="{$feList.page+1}" data-fe-pages="{$feList.num_pages}">{tr}More galleries{/tr}</a>
		</p>
	{/if}
</div>
{/strip}
