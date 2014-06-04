{strip}
<div id="banner">
	<ul id="background">
		{foreach from=$modImages item=modImg}
		        <li><img src="/liberty/download_file.php?attachment_id={$modImg.image_id}" title="{$modImg.title|escape}" alt="{$modImg.title|escape}" /></li>
		{/foreach}
	</ul>
	<div id="overlay"></div>
</div>
{/strip}
