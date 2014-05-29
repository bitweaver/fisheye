{strip}
{if $gBitSystem->isPackageActive( 'fisheye' ) && $modImages}
	<ul id="specials">
			{foreach from=$modImages item=modImg}
				<li>
					<h1>{$modImg.title|escape}</h1>
						<a href="{$modImg.display_url}" alt="{$modImg.title|escape}" title="{$modImg.title|escape} - {$modImg.last_modified|bit_short_datetime}, by {displayname user=$modImg.modifier_user real_name=$modImg.modifier_real_name nolink=1}{if (strlen($modImg.title) > $maxlen) AND ($maxlen > 0)}, {$modImg.title|escape}{/if}">
							<img src="{$modImg.thumbnail_url}" title="{$modImg.title|escape}" alt="{$modImg.title|escape}" />
						</a>
				</li>
			{/foreach}
	</ul>
{/if}
{/strip}
