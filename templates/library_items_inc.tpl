{* One page of gallery cards for the library. Also served alone as the "more" fragment. *}
{strip}
{foreach from=$galleryList item=gal}
	{assign var=feCovers value=$gal.covers|@count}
	<a class="fe-card" rel="ugc" href="{$gal.display_url|escape}">
		<span class="fe-cover n{$feCovers}">
			{foreach from=$gal.covers item=cover}
				<img src="{$cover|escape}" alt="" loading="lazy" />
			{foreachelse}
				{booticon iname="fa-folder"}
			{/foreach}
		</span>
		<span class="fe-card-t">
			<span class="t">{if $gal.title}{$gal.title|escape}{else}{tr}Gallery{/tr} {$gal.gallery_id}{/if}</span>
			{if $gal.is_hidden eq 'y' || $gal.is_private eq 'y' || $gal.access_answer}{booticon iname="fa-lock" iexplain="Restricted"}{/if}
		</span>
		<span class="fe-card-m">
			{$gal.num_images} {if $gal.num_images eq 1}{tr}photo{/tr}{else}{tr}photos{/tr}{/if}
			{if $gal.num_galleries}, {$gal.num_galleries} {if $gal.num_galleries eq 1}{tr}gallery inside{/tr}{else}{tr}galleries inside{/tr}{/if}{/if}
		</span>
		{if !$gQueryUserId}<span class="fe-card-m">{displayname hash=$gal nolink=TRUE}</span>{/if}
	</a>
{/foreach}
{/strip}
