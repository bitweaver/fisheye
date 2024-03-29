{strip}
{if $packageMenuTitle}<a class="dropdown-toggle" data-toggle="dropdown" href="#"> {tr}{$packageMenuTitle}{/tr} <b class="caret"></b></a>{/if}
<ul class="{$packageMenuClass}">
	{if $gBitUser->hasPermission('p_fisheye_view')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php">{booticon iname="fa-list-ul" iexplain="List Galleries" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_fisheye_create')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gBitUser->mUserId}">{booticon iname="fa-image-landscape" iexplain="My Galleries" ilocation=menu}</a></li>
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}edit.php">{booticon iname="fa-camera" iexplain="Create a Gallery" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_fisheye_upload')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}upload.php">{booticon iname="fa-cloud-arrow-up" iexplain="Upload Images" ilocation=menu}</a></li>
	{/if}
	{* if $gBitUser->hasPermission('p_fisheye_view')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}gallery_tree.php">{booticon iname="fa-sitemap" iexplain="growser gallery tree" ilocation=menu} {tr}Browse Gallery Tree{/tr}</a></li>
	{/if *}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('quota')}
		<li><a class="item" href="{$smarty.const.QUOTA_PKG_URL}">{booticon iname="fa-hard-drive" iexplain="Usage" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('gatekeeper')}
		<li><a class="item" href="{$smarty.const.GATEKEEPER_PKG_URL}">{booticon iname="fa-lock" iexplain="Security" ilocation=menu}</a></li>
	{/if}
	{*if $gBitUser->hasPermission('p_fisheye_admin')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}admin/admin_imagegals.php">{tr}Admin Galleries{/tr}</a></li>
	{/if*}
</ul>
{/strip}
