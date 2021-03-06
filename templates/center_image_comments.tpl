{* $Header$ *}
{strip}
{if $modLastComments}
<div class="listing fisheye">
	<div class="header">
		<h2>{tr}{$moduleTitle|default:'Image Comments'}{/tr}</h2>
	</div>

	<div class="body">
		<ul class="comment">
			{section name=ix loop=$modLastComments}
				<li class="post">
						<a href="{$modLastComments[ix].object->getDisplayUrl()}"><img class="thumb" src="{$modLastComments[ix].object->getThumbnailUrl($moduleParams.module_params.thumb_size)}" alt="{$modLastComments[ix].object->getTitle()|escape}" title="{$modLastComments[ix].object->getTitle()|escape}" /></a>

					<div class="header">
					<h3>{$modLastComments[ix].object->getTitle()|escape}:&nbsp;{$modLastComments[ix].display_link}</h3>
					{if $moduleParams.module_params.show_date}
						<div class="date">{tr}by{/tr} {displayname hash=$modLastComments[ix]}, {$modLastComments[ix].last_modified|bit_short_datetime}</div>
					{/if}
					</div>
					{if $moduleParams.module_params.full}
						<p>{$modLastComments[ix].parsed_data}</p>
					{/if}
					<div class="clear"></div>
				</li>
			{sectionelse}
				<li></li>
			{/section}
		</ul>
		<div class="clear"></div>
		{pagination}
	</div>	<!-- end .body -->
</div>	<!-- end .fisheye -->
{/if}
{/strip}
