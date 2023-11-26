<link rel="stylesheet" href="/photos/css/fisheye.css">
{strip}
<div class="listing fisheye">
	<header>
		<div class="floaticon">
			{minifind prompt="Galleries"}
		</div>
		<h1>{tr}Image Galleries{/tr}{if $gQueryUserId} {tr}by{/tr} {displayname user_id=$gQueryUserId}{/if}</h1>
	</header>

	<section class="body">
		<ul class="list-inline sortby">
			<li>{booticon iname="fa-circle-arrow-right" iexplain="sort by"}</li>
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

		<div class="form-group">
		<div class="row galleries">
			{math assign=quarterValue equation="round(c/4)" c=$galleryList|count}
			{foreach from=$galleryList key=galleryId item=gal}
			<div class="col-xs-6 col-sm-4 col-md-3 ">
				<div class="{$gal.content_type_guid} thumbnail">
					{if $gBitSystem->isFeatureActive('fisheye_list_thumbnail') && $gal.display_url}
					{assign var=thumbnailUri value=$gBitSystem->getParameter( $gal, 'thumbnail_uri', "`$smarty.const.FISHEYE_PKG_URL`image/no_image.png")}
					<a href="{$gal.display_url}"><div class="square" style="background-image:url('{$thumbnailUri}');"alt="{$gal.title|escape}" title="{$gal.title|truncate:50|escape}" {if !empty($gal.data)} data-toggle="popover" data-trigger="click hover focus" data-placement="top" data-content="{$gal.data|truncate:100}"{else}{/if}><img src="{$thumbnailUri}" alt="{$gal.title|escape}"><h3 class="gallery-title"><a href="{$gal.display_url}">{if $gBitSystem->isFeatureActive('fisheye_list_title')}{$gal.title|truncate:25|escape}{else}Gallery {$gal.gallery_id}{/if}</a></h3><div class="security" style="position:absolute; top:5%;right:5%;color:#fff;">
							{if $gal.is_hidden=='y' || $gal.is_private=='y' || $gal.access_answer}
								{booticon iname="fa-lock" iexplain="Security" label=TRUE}
							{/if}
							{if $gal.is_hidden=='y'}
								<span style="padding:5px;">{tr}Hidden{/tr}</span>
							{/if}
							{if $gal.is_private=='y'}
								<span style="padding:5px;">{tr}Private{/tr}</span>
							{/if}
							{if $gal.access_answer}
								<span style="padding:5px;">{tr}Password{/tr}</span>
							{/if}
						</div></div></a>
					{/if}
					<div class="caption">

					{if $gBitSystem->isFeatureActive('fisheye_list_user')}
						<strong>{displayname hash=$gal nolink=TRUE}</strong> <small><a href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gal.user_id}" style="display:block;">{tr}Galleries{/tr}</a></small>
					{/if}
					{* if $galleryList[ix]->isProtected()}
						{booticon iname="fa-lock" iexplain="Protected"}
					{/if *}

					{if $gBitSystem->isFeatureActive('fisheye_list_created' ) or $gBitSystem->isFeatureActive('fisheye_list_lastmodif' )}
						<div class="date">
							{if $gBitSystem->isFeatureActive('fisheye_list_created' ) }
								<strong>{tr}Created{/tr}:</strong> {$gal.created|bit_short_date}<br />
							{/if}
							{if $gBitSystem->isFeatureActive('fisheye_list_lastmodif' )}
								<strong>{tr}Modified{/tr}:</strong> {$gal.last_modified|bit_short_date}<br />
							{/if}
						</div>
					{/if}

					<!--{if $gBitSystem->isFeatureActive('fisheye_list_hits')}
						<small><strong>{tr}Hits{/tr}:</strong> {$gal.hits}</small>
					{/if}-->

					{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gal}

					</div>
				</div>
			</div>

			{/foreach}	
			</div>
		</div>

		<nav>
			{pagination}
		</nav>

		</section>	<!-- end .body -->
	</div>	<!-- end .fisheye -->
	{/strip}
	{literal}
	<script src="/storage/static/js/popover.js"></script>
	<script>
		$('background-image').error(function(){
        $(this).attr('src', '/storage/static/images/no-image.png');
});
	
	</script>
{/literal}
