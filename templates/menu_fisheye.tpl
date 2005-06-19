{strip}
<ul>
	<li><a class="item" href="{$gBitLoc.FISHEYE_PKG_URL}list_galleries.php">{biticon ipackage=liberty iname=list iexplain="list galleries" iforce="icon"} {tr}List Galleries{/tr}</a></li>
	{if $gBitUser->hasPermission('bit_p_create_fisheye')}
		<li><a class="item" href="{$gBitLoc.FISHEYE_PKG_URL}list_galleries.php?user_id={$gBitUser->mUserId}">{biticon ipackage=liberty iname=spacer iexplain="my galleries" iforce="icon"} {tr}My Galleries{/tr}</a></li>
		<li><a class="item" href="{$gBitLoc.FISHEYE_PKG_URL}edit.php">{biticon ipackage=liberty iname=new iexplain="create galleries" iforce="icon"} {tr}Create a Gallery{/tr}</a></li>
		<!--<li><a class="item" href="{$gBitLoc.FISHEYE_PKG_URL}edit_collection.php">{tr}Create a Collection{/tr}</a></li>-->
	{/if}
	{if $gBitUser->hasPermission('bit_p_upload_fisheye')}
		<li><a class="item" href="{$gBitLoc.FISHEYE_PKG_URL}upload.php">{biticon ipackage=liberty iname=upload iexplain="upload images" iforce="icon"} {tr}Upload Images{/tr}</a></li>
	{/if}
{* if $gBitUser->hasPermission('bit_p_view_fisheye')}
		<!--<li><a class="item" href="{$gBitLoc.FISHEYE_PKG_URL}browse.php">{tr}Browse Galleries{/tr}</a></li>-->
		<!--<li><a class="item" href="{$gBitLoc.FISHEYE_PKG_URL}collection.php">{tr}List Collections{/tr}</a></li>-->
		<li><a class="item" href="{$gBitLoc.FISHEYE_PKG_URL}gallery_tree.php">{biticon ipackage=liberty iname=tree iexplain="growser gallery tree" iforce="icon"} {tr}Browse Gallery Tree{/tr}</a></li>
	{/if *}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('quota')}
		<li><a class="item" href="{$gBitLoc.QUOTA_PKG_URL}">{biticon ipackage=liberty iname=spacer iexplain="usage" iforce="icon"} {tr}Usage{/tr}</a></li>
	{/if}
	{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('gatekeeper')}
		<li><a class="item" href="{$gBitLoc.GATEKEEPER_PKG_URL}">{biticon ipackage=gatekeeper iname=security iexplain="security" iforce="icon"} {tr}Security{/tr}</a></li>
	{/if}
	{if $gBitUser->hasPermission('bit_p_admin_fisheye')}
		<!--<li><a class="item" href="{$gBitLoc.FISHEYE_PKG_URL}admin/admin_imagegals.php">{tr}Admin Galleries{/tr}</a></li>-->
	{/if}
</ul>
{/strip}
