{strip}
<div class="listing fisheye">
	<div class="header">
		<h1>{tr}Image Galleries{/tr}{if $gQueryUserId} {tr}by{/tr} {displayname user_id=$gQueryUserId}{/if}</h1>
	</div>

	<div class="body">
		{minifind}

		<ul class="inline navbar sortby">
			<li>{biticon ipackage="icons" iname="emblem-symbolic-link" iexplain="sort by" iforce="icon"}</li>
			{if $gBitSystem->isFeatureActive('fisheye_list_title')}
				<li>{smartlink ititle="Gallery Name" isort="title" user_id=$gQuerUserId offset=$iMaxRows home=$userInfo.login search=$iSearchString}</li>
			{/if}
			{if $gBitSystem->isFeatureActive('fisheye_list_user')}
				<li>{smartlink ititle="Owner" isort=$gBitSystem->getConfig('users_display_name') user_id=$gQuerUserId offset=$iMaxRows home=$userInfo.login search=$iSearchString}</li>
			{/if}
			{if $gBitSystem->isFeatureActive('fisheye_list_created')}
				<li>{smartlink ititle="Created" isort="created" user_id=$gQuerUserId offset=$iMaxRows home=$userInfo.login search=$iSearchString}</li>
			{/if}
			{if $gBitSystem->isFeatureActive('fisheye_list_lastmodif')}
				<li>{smartlink ititle="Last Modified" isort="last_modified" user_id=$gQuerUserId offset=$iMaxRows home=$userInfo.login search=$iSearchString}</li>
			{/if}
			{if $gBitSystem->isFeatureActive('fisheye_list_hits')}
				<li>{smartlink ititle="Hits" isort="hits" user_id=$gQuerUserId offset=$iMaxRows home=$userInfo.login search=$iSearchString}</li>
			{/if}
		</ul>

		<ul class="clear data">
			{foreach from=$galleryList key=galleryId item=gal}
				<li class="item {cycle values='odd,even'} {$gal.content_type_guid}">
					<div class="security">
						{if $gal.is_hidden=='y' || $gal.is_private=='y' || $gal.access_answer}
							{biticon ipackage="icons" iname="emblem-readonly" iexplain="Security" label=TRUE}
						{/if}
						{if $gal.is_hidden=='y'}
							{tr}Hidden{/tr}
						{/if}
						{if $gal.is_private=='y'}
							{tr}Private{/tr}
						{/if}
						{if $gal.access_answer}
							{tr}Password{/tr}
						{/if}
					</div>

					<h2><a href="{$gal.display_url}">{if $gBitSystem->isFeatureActive('fisheye_list_title')}{$gal.title|escape}{else}Gallery {$gal.gallery_id}{/if}</a></h2>

					{if $gBitSystem->isFeatureActive('fisheye_list_thumbnail') && $gal.display_url}
						<a href="{$gal.display_url}">
							<img class="thumb" src="{$gal.thumbnail_uri}" alt="{$gal.title|escape}" title="{$gal.title|escape}" />
						</a>
					{/if}

					{if $gBitSystem->isFeatureActive('fisheye_list_user')}
						{displayname hash=$gal nolink=TRUE} &raquo; <a href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gal.user_id}">{tr}Galleries{/tr}</a>
					{/if}
					{* if $galleryList[ix]->isProtected()}
						{biticon ipackage="icons" iname="emblem-readonly" iexplain="Protected"}
					{/if *}
					{if $gBitSystem->isFeatureActive('fisheye_list_description')}
						<p>{$gal.data|truncate:200}</p>
					{/if}
						
					{if $gBitSystem->isFeatureActive('fisheye_list_created' ) or $gBitSystem->isFeatureActive('fisheye_list_lastmodif' )}
						<div class="date">
							{if $gBitSystem->isFeatureActive('fisheye_list_created' ) }
								{tr}Created{/tr}: {$gal.created|bit_short_date}<br />
							{/if}
							{if $gBitSystem->isFeatureActive('fisheye_list_lastmodif' )}
								{tr}Modified{/tr}: {$gal.last_modified|bit_short_date}<br />
							{/if}
						</div>
					{/if}

					{if $gBitSystem->isFeatureActive('fisheye_list_hits')}
						{tr}Hits{/tr}: {$gal.hits}<br />
					{/if}

					{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gal}

					<div class="clear"></div>
				</li>
			{foreachelse}
				<li class="item norecords">
					{tr}No records found{/tr}
				</li>
			{/foreach}
		</ul>

		<div class="clear"></div>
		{pagination}
	</div>	<!-- end .body -->
</div>	<!-- end .fisheye -->
{/strip}
