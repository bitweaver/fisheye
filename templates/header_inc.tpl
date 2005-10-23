{* $Header: /cvsroot/bitweaver/_bit_fisheye/templates/header_inc.tpl,v 1.3 2005/10/23 14:39:44 squareing Exp $ *}
{strip}
{if $gGallery->mInfo.previous_image_id}
	<link rel="prev" title="{tr}Previous{/tr}" href="{$gContent->getDisplayUrl($gGallery->mInfo.previous_image_id)|escape}" />
{/if}
{if $gGallery->mInfo.next_image_id}
	<link rel="next" title="{tr}Next{/tr}" href="{$gContent->getDisplayUrl($gGallery->mInfo.next_image_id)|escape}" />
{/if}
{if $gBitSystem->isFeatureActive( 'fisheye_gallery_div_layout' )}
	<link rel="stylesheet" title="{$style}" type="text/css" href="{$smarty.const.FISHEYE_PKG_URL}div_layout.css" media="all" />
{/if}
{/strip}
