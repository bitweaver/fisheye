{* $Header: /cvsroot/bitweaver/_bit_fisheye/modules/mod_images.tpl,v 1.7 2008/09/02 16:08:24 laetzer Exp $ *}
{strip}
{if $gBitSystem->isPackageActive( 'fisheye' ) && $modImages}
	{bitmodule title="$moduleTitle" name="fisheye_images"}
		<ul>
			{foreach from=$modImages item=modImg}
				<li class="{cycle values='odd,even'} item">
					<a href="{$modImg.display_url}" title="{$modImg.title|escape} - {$modImg.last_modified|bit_short_datetime}, by {displayname user=$modImg.modifier_user real_name=$modImg.modifier_real_name nolink=1}{if (strlen($modImg.title) > $maxlen) AND ($maxlen > 0)}, {$modImg.title|escape}{/if}">
						<img src="{$modImg.thumbnail_url}" title="{$modImg.title|escape}" alt="{$modImg.title|escape}" />

						{if !$modImg.has_machine_name}
							<br />
							{if $maxlen gt 0}
								{$modImg.title|escape|truncate:$maxlen:"...":true}
							{else}
								{$modImg.title|escape}
							{/if}
						{/if}
					</a>

					{if $module_params.description}
						<br />
						{if $maxlendesc gt 0}
							{$modImg.data|escape|truncate:$maxlendesc:"...":true}
						{else}
							{$modImg.data|escape}
						{/if}
					{/if}
					{if !$userGallery}
						<br/>{tr}By{/tr} {displayname hash=$modImg}
					{/if}
				</li>
			{foreachelse}
				<li></li>
			{/foreach}
		</ul>
		{if $userGallery}
		<a href="{$smarty.const.FISHEYE_PKG_URL}index.php?user_id={$userGallery}">{tr}See more...{/tr}</a>
		{/if}
	{/bitmodule}
{/if}
{/strip}
