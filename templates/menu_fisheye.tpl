{strip}
<ul>
	{if $gBitUser->hasPermission('p_fisheye_view')}
	<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php">{biticon ipackage="icons" iname="format-justify-fill" iexplain="list galleries" iforce="icon"} {tr}List Galleries{/tr}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_fisheye_create')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gBitUser->mUserId}">{biticon ipackage=icons iname="user-home" iexplain="my galleries" iforce="icon"} {tr}My Galleries{/tr}</a></li>
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}edit.php">{biticon ipackage="icons" iname="document-new" iexplain="create galleries" iforce="icon"} {tr}Create a Gallery{/tr}</a></li>
		{*<!--<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}edit_collection.php">{tr}Create a Collection{/tr}</a></li>-->*}
	{/if}
	{if $gBitUser->hasPermission('p_fisheye_upload')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}upload.php">{biticon ipackage="icons" iname="go-up" iexplain="upload images" iforce="icon"} {tr}Upload Images{/tr}</a></li>
	{/if}
{* if $gBitUser->hasPermission('p_fisheye_view')}
		<!--<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}browse.php">{tr}Browse Galleries{/tr}</a></li>-->
		<!--<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}collection.php">{tr}List Collections{/tr}</a></li>-->
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}gallery_tree.php">{biticon ipackage="icons" iname="folder-remote" iexplain="growser gallery tree" iforce="icon"} {tr}Browse Gallery Tree{/tr}</a></li>
	{/if *}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('quota')}
		<li><a class="item" href="{$smarty.const.QUOTA_PKG_URL}">{biticon ipackage=icons iname="drive-harddisk" iexplain="usage" iforce="icon"} {tr}Usage{/tr}</a></li>
	{/if}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('gatekeeper')}
		<li><a class="item" href="{$smarty.const.GATEKEEPER_PKG_URL}">{biticon ipackage="icons" iname="emblem-readonly" iexplain="security" iforce="icon"} {tr}Security{/tr}</a></li>
	{/if}
	{*if $gBitUser->hasPermission('p_fisheye_admin')}
		<!--<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}admin/admin_imagegals.php">{tr}Admin Galleries{/tr}</a></li>-->
	{/if*}
</ul>
{/strip}
