{strip}
<div id="xsbanner" class="col-xs-12 visible-xs">
	<div id="banner-xs" class="visible-xs">
	</div>
</div>
<div id="banner" class="col-xs-12 hidden-xs">
	<div id="banner">
		<ul id="background">
			{foreach from=$modImages item=modImg}
				<li><img src="/liberty/download_file.php?attachment_id={$modImg.image_id}" title="{$modImg.title|escape}" alt="{$modImg.title|escape}" /></li>
			{/foreach}
		</ul>
	</div>
	<div id="overlay">
		<div id="banner-md" class="visible-sm visible-md">
		</div>
		<div id="banner-lg" class="visible-lg">
		</div>
	</div>
</div>
<div class="clear"></div>
{/strip}
