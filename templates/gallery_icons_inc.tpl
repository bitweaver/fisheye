<div class="floaticon">
	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}
	{if $gContent->hasUserPermission( 'p_fisheye_download_gallery_arc' ) }	
		<a title="{tr}Download Gallery{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gContent->mGalleryId}&amp;download=1">{biticon ipackage="icons" iname="system-file-manager" iexplain="Download Gallery"}</a>
	{/if}
	{if $gContent->hasUpdatePermission()}
		<a title="{tr}Edit{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit.php?gallery_id={$gContent->mGalleryId}">{biticon ipackage="icons" iname="document-properties" iexplain="Edit"}</a>
		<a title="{tr}Image Order{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}image_order.php?gallery_id={$gContent->mGalleryId}">{biticon ipackage=fisheye iname="order" iexplain="Image Order"}</a>
	{/if}
	{if $gContent->hasUpdatePermission() || $gContent->getPreference('is_public')}
		<a title="{tr}Add Image{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}upload.php?gallery_id={$gContent->mGalleryId}">{booticon iname="icon-cloud-upload" ipackage="icons" iexplain="Add Image"}</a>
	{/if}
	{if $gContent->getPreference('is_public')}
		{biticon ipackage="icons" iname="weather-clear" iexplain="Public"}
	{/if}
	{if $gContent->hasAdminPermission()}
		<a title="{tr}User Permissions{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit.php?gallery_id={$gContent->mGalleryId}&amp;delete=1">{booticon iname="icon-trash" ipackage="icons" iexplain="Delete Gallery"}</a>
	{* appears broken at the moment	<a title="{tr}User Permissions{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit_gallery_perms.php?gallery_id={$gContent->mGalleryId}">{booticon iname="icon-key" ipackage="icons" iexplain="User Permissions"}</a> *}
	{/if}
</div>
