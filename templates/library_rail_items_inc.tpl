{* Rail tree items, one level; includes itself for nested galleries. Needs $railItems and $feRailCurrent. Also served alone for the rail script. *}
{strip}
{foreach from=$railItems item=railGal}
	<li {if $railGal.num_galleries}data-fe-node="{$railGal.gallery_id}"{if isset($railGal.children)} class="is-open"{/if}{/if}>
		{if $railGal.num_galleries}
			<a class="tw" href="{$railGal.display_url|escape}" data-fe-twist aria-expanded="{if isset($railGal.children)}true{else}false{/if}" title="{tr}Nested galleries{/tr}">{booticon iname="fa-angle-right"}</a>
		{else}
			<span class="tw"></span>
		{/if}
		<a class="g" href="{$railGal.display_url|escape}" {if $railGal.gallery_id eq $feRailCurrent}aria-current="page"{/if}>
			{if $railGal.gallery_id eq $feRailCurrent}{booticon iname="fa-folder-open"}{else}{booticon iname="fa-folder"}{/if}
			<span class="t">{if $railGal.title}{$railGal.title|escape}{else}{tr}Gallery{/tr} {$railGal.gallery_id}{/if}</span>
		</a>
		{if isset($railGal.children)}
			<ul>{include file="bitpackage:fisheye/library_rail_items_inc.tpl" railItems=$railGal.children}</ul>
		{/if}
	</li>
{/foreach}
{/strip}
