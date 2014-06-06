{strip}
<div class="listing fisheye">
	<header>
		<div class="floaticon">
			{minifind prompt="Galleries"}
		</div>
		<h1>{tr}Image Galleries{/tr}{if $gQueryUserId} {tr}by{/tr} {displayname user_id=$gQueryUserId}{/if}</h1>
	</header>

	<section class="body">
		<ul class="list-inline navbar sortby">
			<li>{booticon iname="icon-circle-arrow-right"  ipackage="icons"  iexplain="sort by" iforce="icon"}</li>
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
			<li>
		{pagination}
			</li>
		</ul>

		<div class="form-group">
		<ul class="thumbnails">
			{foreach from=$galleryList key=galleryId item=gal}
				<li class="col-md-3 {$gal.content_type_guid}">
					<div class="thumbnail">
						{if $gBitSystem->isFeatureActive('fisheye_list_thumbnail') && $gal.display_url}
						<a href="{$gal.display_url}"><img class="thumb" src="{$gal.thumbnail_uri}" alt="{$gal.title|escape}" title="{$gal.title|escape}" /></a>
						{/if}
						<div class="caption">
							<div class="security">
								{if $gal.is_hidden=='y' || $gal.is_private=='y' || $gal.access_answer}
									{booticon iname="icon-lock" ipackage="icons" iexplain="Security" label=TRUE}
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
							<h3><a href="{$gal.display_url}">{if $gBitSystem->isFeatureActive('fisheye_list_title')}{$gal.title|escape}{else}Gallery {$gal.gallery_id}{/if}</a></h3>
						{if $gBitSystem->isFeatureActive('fisheye_list_description')}
							<p>{$gal.data|truncate:200}</p>
						{/if}



						{if $gBitSystem->isFeatureActive('fisheye_list_user')}
							{displayname hash=$gal nolink=TRUE} &raquo; <a href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gal.user_id}">{tr}Galleries{/tr}</a>
						{/if}
						{* if $galleryList[ix]->isProtected()}
							{booticon iname="icon-lock" ipackage="icons" iexplain="Protected"}
						{/if *}
							
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

						</div>
					</div>
				</li>
			{foreachelse}
				<li class="item norecords">
					{tr}No records found{/tr}
				</li>
			{/foreach}
		</ul>
		</div>

	</section>	<!-- end .body -->
</div>	<!-- end .fisheye -->
{/strip}
