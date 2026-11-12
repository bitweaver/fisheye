{* One page of a timeline: photos in small rows under date headings. Also served alone as the "more" fragment. *}
{strip}
{foreach from=$feSections item=section}
	{if !$section.continued}
		<h2 class="fe-month"><a href="{$section.url|escape}">{$section.title|escape}</a><span>{$section.cant} {if $section.cant eq 1}{tr}photo{/tr}{else}{tr}photos{/tr}{/if}</span></h2>
	{/if}
	<div class="fe-just sm" data-fe-lightbox>
		{foreach from=$section.items item=item}
			{if $item.title}{assign var=feLabel value=$item.title}{else}{assign var=feLabel value=$item.file_name}{/if}
			<a href="{$item.display_url|escape}" title="{$feLabel|escape}" style="--r:{$item.aspect}">
				<img src="{$item.thumbnail_uri|escape}" alt="{$feLabel|escape}" loading="lazy" />
			</a>
		{/foreach}
	</div>
{/foreach}
{/strip}
