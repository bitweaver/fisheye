<script type="text/javascript">/*<![CDATA[*/
{literal}
$(function(){
		$("#g1").mbGallery( {
			galleryMaxWidth:0,
			galleryWidth:$('#matteo').width(),
			galleryHeight:500,
//			galleryColor:"#333",
			galleryFrameBorder: 12,
			galleryFrameColor:"#fff",	

			thumbStripPos: "left",
			thumbStripWidth: 480,
			thumbSelectColor: "#fff",
			thumbOverColor : "#cccccc",
//			thumbStripColor: "#333333",
			thumbsBorder: 4,
			thumbHeight:50,
			headerOpacity: .8,

			labelColor: "#333333",
			labelColorDisactive:"#333333",
			labelTextColor: "#ffffff",
			labelTextSize:"11px",
			labelHeight:20,

			startFrom:0,
			fadeTime: 500,
			autoSlide:true,
			autoSize : true,
			slideTimer: 6000,
			iconFolder: "{/literal}{$smarty.const.FISHEYE_PKG_URL}{literal}gallery_views/matteo/icons/white",
			startTimer:0
		}
	)}
);
{/literal}
/*]]>*/</script>


<div class="header">
	{include file="bitpackage:fisheye/gallery_icons_inc.tpl"}
	<h1>{$gContent->getTitle()|escape}</h1>
</div>

{if $gContent->mInfo.data}
	<p>{$gContent->mInfo.data|escape}</p>
{/if}

{assign var=thumbsize value='avatar'}
<div class="gallery" id="matteo">
	<div id="g1" class="galleryCont">
		{foreach from=$gContent->mItems item=galItem}
			<img class="imgThumb" src="{$galItem->mInfo.thumbnail_url.$thumbsize}">
			<img class="imgFull" src="{$galItem->mInfo.thumbnail_url.large}">
			<div class="imgDesc">{$galItem->mInfo.title|escape}</div>
		{/foreach}	
	</div>
</div>

{if $gContent->getPreference('allow_comments') eq 'y'}
	{include file="bitpackage:liberty/comments.tpl"}
{/if}
