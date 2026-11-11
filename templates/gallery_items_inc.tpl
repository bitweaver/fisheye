{* One page of gallery items for the current display mode. Also served alone as the "more" fragment. *}
{strip}
{foreach from=$feItems item=item}
	{if $item.title}{assign var=feLabel value=$item.title}{else}{assign var=feLabel value=$item.file_name}{/if}
	{if $feMode eq 'organize'}
		<a class="fe-tile{if $item.content_id eq $gContent->mInfo.preview_content_id} is-cover{/if}" href="{$item.display_url|escape}" title="{$feLabel|escape}" data-fe-id="{$item.content_id}" {if $feCanEdit && $feList.sort eq 'position'}draggable="true"{/if}>
			<span class="fe-frame">
				<img src="{$item.thumbnail_uri|escape}" alt="{$feLabel|escape}" loading="lazy" draggable="false" />
				{* corners: number and favorite top left, select top right, cover tag bottom left, cover and rotate buttons bottom right *}
				<span class="fe-tl">
					<span class="fe-num">{$item.number}</span>
					{if $feCanEdit}
						<span class="fe-fav{if $item.is_favorite} is-on{/if}" data-fe-fav role="button" aria-pressed="{if $item.is_favorite}true{else}false{/if}" title="{tr}Favorite{/tr}">{booticon iname="fa-heart"}</span>
					{/if}
				</span>
				{if $feCanEdit}
					<span class="fe-pick" data-fe-pick role="checkbox" aria-checked="false" title="{tr}Select{/tr}">{booticon iname="fa-check"}</span>
					{* cover and rotate act on this photo alone, whatever is selected *}
					<span class="fe-turn">
						<span data-fe-coverset role="button" title="{tr}Use as the gallery cover{/tr}">{booticon iname="fa-star"}</span>
						<span data-fe-turn="270" role="button" title="{tr}Rotate left{/tr}">{booticon iname="fa-undo"}</span>
						<span data-fe-turn="90" role="button" title="{tr}Rotate right{/tr}">{booticon iname="fa-redo"}</span>
					</span>
				{/if}
				<span class="fe-covertag">{tr}Cover{/tr}</span>
			</span>
			<span class="fe-cap"><span class="t">{$feLabel|escape}</span></span>
		</a>
	{elseif $feMode eq 'masonry'}
		<a href="{$item.display_url|escape}" title="{$feLabel|escape}">
			<img src="{$item.thumbnail_uri|escape}" alt="{$feLabel|escape}" loading="lazy" {if $item.width && $item.height && !$item.thumbnail_pending}width="{$item.width}" height="{$item.height}"{/if} />
		</a>
	{elseif $feMode eq 'details'}
		<tr>
			<td><a href="{$item.display_url|escape}"><img src="{$item.thumbnail_uri|escape}" alt="" loading="lazy" /></a></td>
			<td class="t"><a href="{$item.display_url|escape}">{$feLabel|escape}</a></td>
			<td class="m">{$item.file_name|escape}</td>
			<td class="m">{if $item.event_time}{$item.event_time|bit_short_date}{/if}</td>
			<td class="m">{$item.created|bit_short_date}</td>
			<td class="m">{if $item.width && $item.height}{$item.width} &times; {$item.height}{/if}</td>
			<td class="m">{if $item.file_size}{$item.file_size|display_bytes}{/if}</td>
		</tr>
	{else}
		<a href="{$item.display_url|escape}" title="{$feLabel|escape}" style="--r:{$item.aspect}">
			<img src="{$item.thumbnail_uri|escape}" alt="{$feLabel|escape}" loading="lazy" />
		</a>
	{/if}
{/foreach}
{/strip}
