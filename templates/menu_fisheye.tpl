{strip}
<a class="dropdown-toggle" data-toggle="dropdown" href="#"> {tr}{$packageMenuTitle}{/tr} <b class="caret"></b></a>
<ul class="{$packageMenuClass}">
	{if $gBitUser->hasPermission('p_fisheye_view')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php">{booticon iname="icon-list" iexplain="List Galleries" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_fisheye_create')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gBitUser->mUserId}">{booticon iname="icon-picture" iexplain="My Galleries" ilocation=menu}</a></li>
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}edit.php">{booticon iname="icon-camera" iexplain="Create a Gallery" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_fisheye_upload')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}upload.php">{booticon iname="icon-cloud-upload" iexplain="Upload Images" ilocation=menu}</a></li>
	{/if}
	{* if $gBitUser->hasPermission('p_fisheye_view')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}gallery_tree.php">{booticon iname="icon-sitemap" iexplain="growser gallery tree" ilocation=menu} {tr}Browse Gallery Tree{/tr}</a></li>
	{/if *}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('quota')}
		<li><a class="item" href="{$smarty.const.QUOTA_PKG_URL}">{booticon iname="icon-hdd" iexplain="Usage" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('gatekeeper')}
		<li><a class="item" href="{$smarty.const.GATEKEEPER_PKG_URL}">{booticon iname="icon-lock" iexplain="Security" ilocation=menu}</a></li>
	{/if}
	{*if $gBitUser->hasPermission('p_fisheye_admin')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}admin/admin_imagegals.php">{tr}Admin Galleries{/tr}</a></li>
	{/if*}
</ul>
{/strip}
