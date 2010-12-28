{strip}
<ul>
	{if $gBitUser->hasPermission('p_fisheye_view')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php">{biticon iname="format-justify-fill" iexplain="List Galleries" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_fisheye_create')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gBitUser->mUserId}">{biticon iname="user-home" iexplain="My Galleries" ilocation=menu}</a></li>
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}edit.php">{biticon iname="document-new" iexplain="Create a Gallery" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_fisheye_upload')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}upload.php">{biticon iname="go-up" iexplain="Upload Images" ilocation=menu}</a></li>
	{/if}
	{* if $gBitUser->hasPermission('p_fisheye_view')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}gallery_tree.php">{biticon iname="folder-remote" iexplain="growser gallery tree" ilocation=menu} {tr}Browse Gallery Tree{/tr}</a></li>
	{/if *}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('quota')}
		<li><a class="item" href="{$smarty.const.QUOTA_PKG_URL}">{biticon iname="drive-harddisk" iexplain="Usage" ilocation=menu}</a></li>
	{/if}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('gatekeeper')}
		<li><a class="item" href="{$smarty.const.GATEKEEPER_PKG_URL}">{biticon iname="emblem-readonly" iexplain="Security" ilocation=menu}</a></li>
	{/if}
	{*if $gBitUser->hasPermission('p_fisheye_admin')}
		<li><a class="item" href="{$smarty.const.FISHEYE_PKG_URL}admin/admin_imagegals.php">{tr}Admin Galleries{/tr}</a></li>
	{/if*}
</ul>
{/strip}
