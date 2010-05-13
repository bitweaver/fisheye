{literal}
<script type="text/javascript">//<![CDATA[
	function updateForm(){
		$("#width").val($("#drag").attr("offsetWidth"));
		$("#height").val($("#drag").attr("offsetHeight"));
		$("#top").val($("#drag").attr("offsetTop"));
		$("#left").val($("#drag").attr("offsetLeft"));
	}
	$(document).ready(function(){
		updateForm();
		$("#drag").resizable({
			 stop: function() {
			  	updateForm();
			  }
		});
		$("#drag").draggable({
			  containment: 'parent',
			  stop: function() {
			  	updateForm();
			  }
		});
		$(".tag").hover(
		function () {
		$(this).addClass("tagOn");
		},
		function () {
		$(this).removeClass("tagOn");
		}
		);
	});
//]]></script>
<style>
	.tagOn{
		border:1px solid black;
	}
	#taggingArea{
		position:relative;
		width:auto;
		float:left;
	}
	#formArea{
		width:50%;
		float:left;
	}
	#drag{
		position:absolute;
		top:0px;
		width:100px;
		height:100px;
		border:2px solid black;
		background:url('blank.gif');
	}
</style>
{/literal}
{strip}
<div class="display fisheye">
	{if !$liberty_preview}
		<div class="floaticon">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}
			{if $gContent->hasUpdatePermission()}
				<a title="{tr}Edit{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?image_id={$gContent->mImageId}">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit Image"}</a>
				<a title="{tr}Delete{/tr}" href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?image_id={$gContent->mImageId}&amp;delete=1">{biticon ipackage="icons" iname="edit-delete" iexplain="Delete Image"}</a>
			{/if}
		</div>
	{/if}

	{formfeedback hash=$feedback}

	<div class="header">
		<h1>{$gContent->getTitle()|default:$gContent->mInfo.filename|escape}</h1>
	</div>

{*    <div class="body">  *}
		<div id="taggingArea">
			<img src="{$gContent->mInfo.source_url}" />
			{if $mode == 'edit' }
				<div id="drag" class="ui-widget-content"></div>
			{else}
				{if $gContent->mInfo.tags }
					{foreach from=$gContent->mInfo.tags item=resTags key=itemContentId}
						<div class=tag style="position:absolute;width:{$resTags.tag_width}px;height:{$resTags.tag_height}px;top:{$resTags.tag_top};left:{$resTags.tag_left};">
							{$resTags.description}
						</div>
					{/foreach}
				{/if}
			{/if}
		</div>

		{if $mode == 'edit' }
			<div id="formArea">
				<form method=post>
					<input type=hidden name=save value=yes>
					<input type=hidden name=pic value="{$gContent->getTitle()|default}">
					<input type=hidden name=width id=width>
					<input type=hidden name=height id=height>
					<input type=hidden name=top id=top>
					<input type=hidden name=left id=left>
					Description : <input type=text name=description>
					<input type=submit value=save>
				</form>
				{foreach from=$gContent->mInfo.tags item=resTags key=itemContentId}
					Tag #{$resTags.id} {$resTags.description} <a href="view_image_tagged.php?image_id={$gContent->mImageId}&delete={$resTags.id}">Delete</a><br />
				{/foreach}
				<a href="view_image_tagged.php?image_id={$gContent->mImageId}">Go to view mode</a>
			</div>]
		{else}
			<div id="formArea">
				<a href="view_image_tagged.php?image_id={$gContent->mImageId}&mode=edit">Go to edit mode</a>
			</div>
		{/if}
{*	</div>  *}
	
	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}
	
	{if $gGallery && $gGallery->getPreference('allow_comments') eq 'y'}
		{include file="bitpackage:liberty/comments.tpl"}
	{/if}

</div>	<!-- end .fisheye -->
{/strip}
