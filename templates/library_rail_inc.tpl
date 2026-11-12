{* Library rail beside a gallery: the owner's galleries. Needs $feRail and $feRailCurrent. *}
{strip}
<nav class="fe-rail" aria-label="{tr}Library{/tr}" data-fe-rail-url="{feurl page="view" current=$feRailCurrent}&amp;gallery_id=">
	<a class="fe-railbtn" href="#" data-fe-rail-toggle aria-expanded="true" title="{tr}Hide library{/tr}">{booticon iname="fa-columns"}</a>
	<h2>{tr}Library{/tr}</h2>
	<ul>
		<li><a href="{$gContent->getLibraryUrl()|escape}">{booticon iname="fa-images"}{tr}All galleries{/tr}</a></li>
	</ul>
	<h2>{tr}Galleries{/tr}
		<a class="fe-railall" href="#" data-fe-rail-all aria-pressed="false" title="{tr}Expand all{/tr}" data-fe-expand="{tr}Expand all{/tr}" data-fe-collapse="{tr}Collapse all{/tr}">{booticon iname="fa-angle-double-down"}</a>
	</h2>
	<ul class="fe-tree" data-fe-tree="{$gContent->mGalleryId}">
		{include file="bitpackage:fisheye/library_rail_items_inc.tpl" railItems=$feRail.galleries}
	</ul>
	<ul>
		{if $feRail.has_more}
			<li><a href="{$gContent->getLibraryUrl()|escape}">{booticon iname="fa-ellipsis-h"}{tr}More galleries{/tr}</a></li>
		{/if}
	</ul>
	{* timelines draw on every gallery of the owner, so only the owner and gallery administrators are offered them *}
	{if $gBitUser->isRegistered() && ( $gContent->mInfo.user_id eq $gBitUser->mUserId || $gBitUser->hasPermission( 'p_fisheye_admin' ) )}
		{if $gContent->mInfo.user_id ne $gBitUser->mUserId}{assign var=feRailOwner value="&amp;user_id=`$gContent->mInfo.user_id`"}{else}{assign var=feRailOwner value=""}{/if}
		<h2>{tr}Browse by{/tr}</h2>
		<ul>
			<li><a href="{feurl page="timeline"}?by=taken{$feRailOwner}" {if $feRailMeta eq 'taken'}aria-current="page"{/if}>{booticon iname="fa-calendar-alt"}{tr}Date taken{/tr}</a></li>
			<li><a href="{feurl page="timeline"}?by=uploaded{$feRailOwner}" {if $feRailMeta eq 'uploaded'}aria-current="page"{/if}>{booticon iname="fa-clock"}{tr}Date uploaded{/tr}</a></li>
			<li><a href="{feurl page="timeline"}?by=unfiled{$feRailOwner}" {if $feRailMeta eq 'unfiled'}aria-current="page"{/if}>{booticon iname="fa-folder-open"}{tr}Not in a gallery{/tr}</a></li>
		</ul>
	{/if}
</nav>
{/strip}
