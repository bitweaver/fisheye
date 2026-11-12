{* Timeline: every photo of one owner in date order, under year, month or day headings. *}
{strip}
<div class="fe-app">
<div class="fe-shell" data-fe-shell="light">
	{include file="bitpackage:fisheye/library_rail_inc.tpl"}
	<div class="fe-main">
	<a class="fe-btn fe-btn-quiet fe-railopen" href="{$gContent->getLibraryUrl()|escape}" data-fe-rail-toggle aria-expanded="false">{booticon iname="fa-columns"}{tr}Library{/tr}</a>
	<div class="fe-top">
		<ol class="fe-crumb">
			<li><a href="{$gContent->getLibraryUrl()|escape}">{tr}Galleries{/tr}</a></li>
			{if $gContent->mScope eq ''}
				<li aria-current="page">{tr}{$feType.title}{/tr}</li>
			{else}
				<li><a href="{$feTypeUrl|escape}">{tr}{$feType.title}{/tr}</a></li>
				{foreach from=$feCrumbs item=crumbTitle key=crumbUrl}
					<li><a href="{$crumbUrl|escape}">{$crumbTitle|escape}</a></li>
				{/foreach}
				<li aria-current="page">{if $gContent->mScope eq 'none'}{tr}No date taken{/tr}{else}{$gContent->bucketLabel($gContent->mScope)|escape}{/if}</li>
			{/if}
		</ol>
	</div>

	<div class="fe-head">
		<div class="fe-head-main">
			<h1>{$gContent->getTitle()|escape}</h1>
			<p class="fe-sub">
				{$feList.cant} {if $feList.cant eq 1}{tr}photo{/tr}{else}{tr}photos{/tr}{/if}
				{if $gContent->mScope eq '' && $feYearSpan} {if $feYearSpan.first eq $feYearSpan.last}{tr}from{/tr} {$feYearSpan.first}{else}{tr}from{/tr} {$feYearSpan.first} {tr}to{/tr} {$feYearSpan.last}{/if}{/if}
				{if $gContent->mBy eq 'unfiled'} {tr}that are in no gallery.{/tr}{if $feList.cant} {tr}Open a photo and choose Edit to put it in one.{/tr}{/if}{else}, {tr}across every gallery.{/tr}{/if}
				{if $gContent->mScope eq 'none'} {tr}Shown by the date they were uploaded.{/tr}{/if}
				{if $feUndated} <a class="lnk" href="{$feUndatedUrl|escape}">{$feUndated} {if $feUndated eq 1}{tr}photo has no date taken{/tr}{else}{tr}photos have no date taken{/tr}{/if}</a>.{/if}
			</p>
		</div>
		{if $feGroups|@count > 1}
			<div class="fe-actions">
				<nav class="fe-seg" aria-label="{tr}Group by{/tr}">
					{foreach from=$feGroups item=groupName key=groupKey}
						<a href="{$gContent->getDisplayUrl(null,$groupKey)|escape}" {if $groupKey eq $feGroup}aria-current="true"{/if}>{$groupName}</a>
					{/foreach}
				</nav>
			</div>
		{/if}
	</div>

	{if $feList.page > 1}
		<p class="fe-more"><a class="fe-btn" href="{$feBaseUrl|escape}&amp;page={$feList.page-1}">{booticon iname="fa-angle-left"}{tr}Later photos{/tr}</a></p>
	{/if}

	{if $feSections}
		<div class="fe-time">
			<div data-fe-items>
				{include file="bitpackage:fisheye/timeline_items_inc.tpl"}
			</div>
			{if $feYears|@count > 1}
				<nav class="fe-years" aria-label="{tr}Jump to year{/tr}">
					{foreach from=$feYears item=year key=yearKey}
						<a href="{$year.url|escape}" {if $year.current}aria-current="true"{/if} title="{$year.cant} {tr}photos{/tr}">{$yearKey}</a>
					{/foreach}
				</nav>
			{/if}
		</div>
	{else}
		<div class="fe-empty">
			<p>{if $gContent->mBy eq 'unfiled' && $gContent->mScope eq ''}{tr}Every photo is in a gallery.{/tr}{elseif $gContent->mScope eq ''}{tr}No photos yet{/tr}{else}{tr}No photos from this date{/tr}{/if}</p>
			{if $gContent->mBy ne 'unfiled' && $gContent->mScope eq '' && $gBitUser->hasPermission('p_fisheye_upload')}
				<a class="fe-btn fe-btn-primary" href="{feurl page="upload"}">{booticon iname="fa-upload"}{tr}Upload photos{/tr}</a>
			{/if}
		</div>
	{/if}

	{if $feList.page < $feList.num_pages}
		<p class="fe-more">
			<a class="fe-btn" href="{$feBaseUrl|escape}&amp;page={$feList.page+1}" data-fe-more data-fe-page="{$feList.page+1}" data-fe-pages="{$feList.num_pages}">{tr}Earlier photos{/tr}</a>
		</p>
	{/if}
	</div>
</div>
</div>
{/strip}
