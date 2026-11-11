{* Library rail beside a gallery: the owner's galleries. Needs $feRail and $feRailCurrent. *}
{strip}
<nav class="fe-rail" aria-label="{tr}Library{/tr}" data-fe-rail-url="{$smarty.const.FISHEYE_PKG_URL}view.php?current={$feRailCurrent}&amp;gallery_id=">
	<a class="fe-railbtn" href="#" data-fe-rail-toggle aria-expanded="true" title="{tr}Hide library{/tr}">{booticon iname="fa-columns"}</a>
	<h2>{tr}Library{/tr}</h2>
	<ul>
		<li><a href="{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gContent->mInfo.user_id}">{booticon iname="fa-images"}{tr}All galleries{/tr}</a></li>
	</ul>
	<h2>{tr}Galleries{/tr}
		<a class="fe-railall" href="#" data-fe-rail-all aria-pressed="false" title="{tr}Expand all{/tr}" data-fe-expand="{tr}Expand all{/tr}" data-fe-collapse="{tr}Collapse all{/tr}">{booticon iname="fa-angle-double-down"}</a>
	</h2>
	<ul class="fe-tree" data-fe-tree="{$gContent->mGalleryId}">
		{include file="bitpackage:fisheye/library_rail_items_inc.tpl" railItems=$feRail.galleries}
	</ul>
	<ul>
		{if $feRail.has_more}
			<li><a href=""{$smarty.const.FISHEYE_PKG_URL}list_galleries.php?user_id={$gContent->mInfo.user_id}">{booticon iname="fa-ellipsis-h"}{tr}More galleries{/tr}</a></li>
		{/if}
	</ul>
</nav>
{/strip}
