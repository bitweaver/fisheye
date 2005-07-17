{* $Header: /cvsroot/bitweaver/_bit_fisheye/templates/header_inc.tpl,v 1.2 2005/07/17 17:36:03 squareing Exp $ *}
{strip}
{if $gGallery->mInfo.previous_image_id}
	<link rel="prev" title="{tr}Previous{/tr}" href="{$gContent->getDisplayUrl($gGallery->mInfo.previous_image_id)|escape}" />
{/if}
{if $gGallery->mInfo.next_image_id}
	<link rel="next" title="{tr}Next{/tr}" href="{$gContent->getDisplayUrl($gGallery->mInfo.next_image_id)|escape}" />
{/if}
{/strip}
