{strip}
<div class="listing fisheye">
	<div class="header">
		<h1>{tr}Image Galleries{/tr}{if $gQueryUserId} {tr}by{/tr} {displayname user_id=$gQueryUserId}{/if}</h1>
	</div>

	<div class="body">
		{minifind}

		<div class="navbar">
			<ul class="sortby">
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
		</div>

		<ul class="clear data">
			{foreach from=$galleryList key=galleryId item=gal}
				<li class="item {cycle values='odd,even'} {$gal.content_type_guid}">
					<div class="floaticon">
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
						{* if $galleryList[ix]->hasEditPermission()}
							<a title="{tr}Edit{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit.php?gallery_id={$galleryId}">{biticon ipackage="icons" iname="document-properties" iexplain="Edit"}</a>
						{/if}
						{if $galleryList[ix]->hasEditPermission()}
						 	<a title="{tr}Image Order{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}image_order.php?gallery_id={$galleryId}">{biticon ipackage="icons" iname="emblem-default" iexplain="Item Order"}</a>
						{/if}
						{if $galleryList[ix]->hasUserPermission('p_fisheye_upload', TRUE, FALSE)}
							<a title="{tr}Add Image{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}upload.php?gallery_id={$galleryId}">{biticon ipackage="icons" iname="go-up" iexplain="Add Image"}</a>
						{/if}
						{if $galleryList[ix]->hasAdminPermission()}
							<a title="{tr}User Permissions{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit_gallery_perms.php?gallery_id={$galleryId}">{biticon ipackage="icons" iname="emblem-shared" iexplain="User Permissions"}</a>
						{/if *}
					</div>

					<h2><a href="{$gal.display_url}">{if $gBitSystem->isFeatureActive('fisheye_list_title')}{$gal.title|escape}{else}Gallery {$gal.gallery_id}{/if}</a></h2>

					{if $gBitSystem->isFeatureActive('fisheye_list_thumbnail') && $gal.display_url}
						<a href="{$gal.display_url}">
							<img class="thumb" src="{$gal.thumbnail_url}" alt="{$gal.title|escape}" title="{$gal.title|escape}" />
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

					<div class="date">
						{if $gBitSystem->isFeatureActive('fisheye_list_created' ) }
							Created: {$gal.created|bit_short_date}<br />
						{/if}
						{if $gBitSystem->isFeatureActive('fisheye_list_lastmodif' )}
							Modified: {$gal.last_modified|bit_short_date}<br />
						{/if}
					</div>

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
		{libertypagination page=$curPage numPages=$numPages find=$find}
	</div>	<!-- end .body -->
</div>	<!-- end .fisheye -->
{/strip}
