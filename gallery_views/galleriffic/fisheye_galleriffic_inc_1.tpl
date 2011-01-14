{strip}<div class="galleriffic">

<div class="header">
	{include file="bitpackage:fisheye/gallery_icons_inc.tpl"}
	<h1>{$gContent->getTitle()|escape}</h1>
	<div class="gallerybar">
		<div class="path">
			{assign var=breadCrumbs value=$gContent->getBreadcrumbLinks()}
			{if $gGallery}
				{displayname user=$gGallery->mInfo.creator_user user_id=$gGallery->mInfo.creator_user_id real_name=$gGallery->mInfo.creator_real_name} :: <a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gGallery->mInfo.user_id}">{tr}Galleries{/tr}</a> &raquo;{if $breadCrumbs}{$breadCrumbs}{else}{$gGallery->getTitle()}{/if}
			{else}
				{displayname user=$gContent->mInfo.creator_user user_id=$gContent->mInfo.creator_user_id real_name=$gContent->mInfo.creator_real_name} :: <a href="{$smarty.const.FISHEYE_PKG_URL}?user_id={$gContent->mInfo.user_id}">{tr}Galleries{/tr}</a> &raquo; {if $breadCrumbs}{$breadCrumbs}{else}{$gContent->getTitle()}{/if}
			{/if}
		</div>
	</div>
</div>


<!-- Start Advanced Gallery Html Containers -->				
<div class="navigation-container">
	<div id="thumbs" class="navigation">
		<div>
		<ul class="thumbs noscript">
			{foreach from=$gContent->mItems item=galItem}
			<li>
				{if is_a($galItem, 'FisheyeImage')}
					<a class="thumb" name="{$galItem->mImageId}" href="{$galItem->mInfo.thumbnail_url.large}{*$smarty.const.FISHEYE_PKG_URL}view_image.php?image_id={$galItem->mImageId*}" title="{$galItem->mInfo.title|escape}">
						<img src="{$galItem->mInfo.thumbnail_url.avatar}" alt="{$galItem->mInfo.title|escape}" />
					</a>
					<h2 class="heading">
						<div class="image-heading">{biticon iname="image-x-generic" isize="small" iexplain=""}{$galItem->getContentTypeDescription()|escape}{$galItem->getDisplayLink()}</div>
					</h2>
					<div class="caption">
						<div class="meta floatright">
							{if $galItem->mInfo.event_time}
							<div class="photo-date date">
								{$galItem->mInfo.event_time|bit_short_date}
							</div>
							{/if}
							{if ($galItem->hasUpdatePermission() || $gContent->getPreference('link_original_images')) && $galItem->mInfo.image_file.source_url}
							<div class="download">
								<a href="{$galItem->mInfo.source_url}">{tr}Download Original{/tr}</a>
								{if $galItem->mInfo.width && $galItem->mInfo.height}
								<div class="photo-date">{$galItem->mInfo.width}x{$galItem->mInfo.height} {tr}pixels{/tr}</div>
								{/if}
							</div>
							{/if}
						</div>
						<div class="image-desc"><p>{$galItem->mInfo.description|escape}</p></div>
					</div>
				{elseif is_a($galItem, 'FisheyeGallery')}
					<a class="thumb" name="{$galItem->mContentId}" href="{$galItem->mPreviewImage->mInfo.thumbnail_url.large}" title="{$galItem->mInfo.title|escape}">
						<img src="{$galItem->mPreviewImage->mInfo.thumbnail_url.avatar}" alt="{$galItem->mInfo.title|escape}"/>
					</a>
					<div class="heading">
						<h2>{biticon iname="emblem-photos" isize="small" iexplain="{$galItem->getContentTypeDescription()|escape}{$galItem->getDisplayLink()}</h2><span class="image-count">({$galItem->getImageCount()} {tr}Items{/tr})</span>
					</div>
					<div class="caption">
						<div class="image-desc">{$galItem->mInfo.description|escape}</div>
						<div class="download">
							
						</div>
					</div>
				{/if}
			</li>
			{/foreach}	
		</ul>			
		</div>
	</div>

	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}

	{if $gContent->getPreference('allow_comments') eq 'y'}
		{include file="bitpackage:liberty/comments.tpl"}
	{/if}

</div>

<div id="gallery" class="content">
	<div class="slideshow-container">
		<div id="heading" class="heading-container"></div>
		<div id="controls" class="controls"></div>
		<div id="loading" class="loader"></div>
		<div id="slideshow" class="slideshow"></div>
		<div id="imagedetails" class="image-details-container"></div>
	</div>
	<div id="caption" class="caption-container"></div>
</div>

<script type="text/javascript">/*<![CDATA[*/
{literal}
jQuery(document).ready(function($) {
	// We only want these styles applied when javascript is enabled
	$('div.content').css('display', 'block');

	// Initially set opacity on thumbs and add
	// additional styling for hover effect on thumbs
	var onMouseOutOpacity = 0.67;
	$('#thumbs ul.thumbs li').opacityrollover({
		mouseOutOpacity:   onMouseOutOpacity,
		mouseOverOpacity:  1.0,
		fadeSpeed:         'fast',
		exemptionSelector: '.selected'
	});
	
	// Initialize Advanced Galleriffic Gallery
	var gallery = $('#thumbs').galleriffic({
		delay:                     2500,
		numThumbs:                 20,
		preloadAhead:              10,
		enableTopPager:            true,
		enableBottomPager:         true,
		maxPagesToShow:            6,
		imageContainerSel:         '#slideshow',
		controlsContainerSel:      '#controls',
		headingContainerSel:       '#heading',
		captionContainerSel:       '#caption',
		loadingContainerSel:       '#loading',
		renderSSControls:          true,
		renderNavControls:         true,
		playLinkText:              '',
		playLinkImage:             '{/literal}{biticon iname="media-playback-start" isize="small" iexplain="Play Slideshow"}{literal}',
		pauseLinkText:             '',
		pauseLinkImage:            '{/literal}{biticon iname="media-playback-pause" isize="small" iexplain="Pause Slideshow"}{literal}',
		prevLinkText:              '&laquo;',
		nextLinkText:              '&raquo;',
		nextPageLinkText:          'Next &rsaquo;',
		prevPageLinkText:          '&lsaquo; Prev',
		enableHistory:             true,
		autoStart:                 false,
		syncTransitions:           false,
		defaultTransitionDuration: 250,
		onSlideChange:             function(prevIndex, currentIndex) {
			// 'this' refers to the gallery, which is an extension of $('#thumbs')
			this.find('ul.thumbs').children()
				.eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
				.eq(currentIndex).fadeTo('fast', 1.0);

			// Update the photo index display
			$('.photo-index').html( (currentIndex+1) +' of '+ this.data.length);
		},
//      onTransitionOut:           function(slide, caption, isSync, callback) { },
//      onTransitionIn:            function(slide, caption, isSync) { },
		onImageLoadFinish:			function(pImageData) {
			jQuery.ajax({
				url: '{/literal}{$smarty.const.FISHEYE_PKG_URL}view_image_details.php?image_id={literal}'+pImageData.hash,
				success: function(data) {
					$('#imagedetails').html(data);
				}
			});
		},
		onPageTransitionOut:       function(callback) {
			this.fadeTo('fast', 0.0, callback);
		},
		onPageTransitionIn:        function() {
			var prevPageLink = this.find('a.prev').css('visibility', 'hidden');
			var nextPageLink = this.find('a.next').css('visibility', 'hidden');
			
			// Show appropriate next / prev page links
			if (this.displayedPage > 0)
				prevPageLink.css('visibility', 'visible');

			var lastPage = this.getNumPages() - 1;
			if (this.displayedPage < lastPage)
				nextPageLink.css('visibility', 'visible');

			this.fadeTo('fast', 1.0);
		}
	});

	/**************** Event handlers for custom next / prev page links **********************/

	gallery.find('a.prev').click(function(e) {
		gallery.previousPage();
		e.preventDefault();
	});

	gallery.find('a.next').click(function(e) {
		gallery.nextPage();
		e.preventDefault();
	});

	/**** Functions to support integration of galleriffic with the jquery.history plugin ****/

	// PageLoad function
	// This function is called when:
	// 1. after calling $.historyInit();
	// 2. after calling $.historyLoad();
	// 3. after pushing "Go Back" button of a browser
	function pageload(hash) {
		// alert("pageload: " + hash);
		// hash doesn't contain the first # character.
		if(hash) {
			$.galleriffic.gotoImage(hash);
		} else {
			gallery.gotoIndex(0);
		}
	}

	// Initialize history plugin.
	// The callback is called at once by present location.hash. 
	$.historyInit(pageload, "advanced.html");

	// set onlick event for buttons using the jQuery 1.3 live method
	$("a[rel='history']").live('click', function(e) {
		if (e.button != 0) return true;

		var hash = this.href;
		hash = hash.replace(/^.*#/, '');

		// moves to a new page. 
		// pageload is called at once. 
		// hash don't contain "#", "?"
		$.historyLoad(hash);

		return false;
	});

	/****************************************************************************************/
});
{/literal}
/*]]>*/</script>

</div>{/strip}
