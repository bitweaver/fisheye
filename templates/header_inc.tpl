{* $Header: /cvsroot/bitweaver/_bit_fisheye/templates/header_inc.tpl,v 1.5 2006/01/10 21:11:46 squareing Exp $ *}
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
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/multifile.js"></script>
{/strip}
