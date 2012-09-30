{strip}
<div id="banner">
	<div id="background">
		{foreach from=$modImages item=modImg}
		        <img src="{$modImg.thumbnail_url}" title="{$modImg.title|escape}" alt="{$modImg.title|escape}" />
		{/foreach}
	</div>
</div>
{/strip}
