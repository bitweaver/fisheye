{* $Header: /cvsroot/bitweaver/_bit_fisheye/modules/Attic/mod_random_images.tpl,v 1.1 2005/06/20 06:03:55 spiderr Exp $ *}
{strip}
{if $gBitSystem->isPackageActive( 'fisheye' )}
	{bitmodule title="$moduleTitle" name="randomimages"}
		<ul>
			{foreach from=$modImages item=img key=imageId}
				<li><a href="{$img.display_url}" title="{$img.title} - {$img.last_modified|bit_short_datetime}, by {displayname user=$img.modifier_user real_name=$img.modifier_real_name nolink=1}{if (strlen($img.title) > $maxlen) AND ($maxlen > 0)}, {$img.title}{/if}"><img src="{$img.thumbnail_url}" />
						<h4>{if !$img.has_machine_name}{if $maxlen gt 0}
							{$img.title|truncate:$maxlen:"...":true}
						{else}
							{$img.title}
						{/if}
						{/if}</h4>
					</a>
				</li>
			{foreachelse}
				<li></li>
			{/foreach}
		</ul>
	{/bitmodule}
{/if}
{/strip}
