{strip}
{if $loadAjax}
	<script type="text/javascript">/*<![CDATA[*/ show_spinner('spinner'); /*]]>*/</script>
{/if}
<div class="admin fisheye">
	<div class="header">
		<h1>{tr}Gallery Images{/tr}: <a href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gContent->mGalleryId}">{$gContent->mInfo.title|escape}</a></h1>
	</div>

	<div class="body">
		<p class="formhelp">{tr}Here you can re-arrange the order of the images in this gallery and quickly change their titles. The image position does not have to be in an exact sequence. In fact, we recommend you count by tens so you can easily insert or re-order images at a later date. If you need to add a detailed description to an image, click the <strong>Edit Details</strong> link next to the desired image.<br />Using the <strong>Gallery Image</strong> radio button you can specify what image is used to identify this particular gallery.{/tr}</p>

		<div id="imgedit"></div>

		{form id="batch_order" legend="Gallery Images"}
			<input type="hidden" name="gallery_id" value="{$gContent->mGalleryId}"/>

			{formfeedback hash=$formfeedback}

			<table class="data">
				<tr>
					<th scope="col" style="width:200px;">{tr}Thumbnail{/tr}</th>
					<th scope="col">{tr}Title and Position{/tr}</th>
					<th scope="col">{tr}Miscellaneous{/tr}</th>
				</tr>

				{counter start=0 print=false assign=imageCount}
				{foreach from=$gContent->mItems item=galItem key=itemContentId}
					{if $imageCount % $gContent->mInfo.images_per_page == 0}
					<tr class="{cycle values='even,odd' assign='pageClass'}">
						<th colspan="3" class="pagebreak">
							{tr}Gallery Page{/tr} {math equation="imgCount / imagesPerPage + 1"
								imgCount=$imageCount
								imagesPerPage=$gContent->mInfo.images_per_page}
						</th>
					</tr>
					{/if}
					<tr class="{$pageClass}">
						{counter print=false}
						<td class="{$galItem->mType.content_type_guid}">
							<a href="{$galItem->getDisplayUrl()|escape}"><img class="thumb" src="{$gContent->mItems.$itemContentId->getThumbnailUrl()|replace:"&":"&amp;"}{if $batchEdit.$contentId ne ''}?{math equation="1 + rand(1,9999)"}{/if}" alt="{$galItem->mInfo.title|escape}" /></a>
						</td>

						<td>
							<input type="text" name="image_title[{$galItem->mContentId}]" value="{$galItem->getTitle()|escape:html}" /><br />
							{if $galItem->isContentType( FISHEYEIMAGE_CONTENT_TYPE_GUID )}
							<strong>{tr}Uploaded{/tr}</strong>: {$galItem->mInfo.created|bit_short_datetime}<br />
							<strong>{tr}File name{/tr}</strong>: {$galItem->mInfo.image_file.filename} <br />
								{if $galItem->mInfo.user_id == $gBitUser->mUserId || $gBitUser->isAdmin()}
								<strong>{tr}Edit Image{/tr}</strong>: <a href="javascript:ajax_updater( 'imgedit', '{$smarty.const.FISHEYE_PKG_URL}edit_image.php', 'ajax=true&amp;content_id={$galItem->mInfo.content_id}&amp;gallery_id={$gContent->mGalleryId}' );">{biticon iname="edit" ipackage="liberty" iexplain="Edit Details"}</a>
								<noscript><div><a href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$galItem->mInfo.content_id}">{biticon ipackage=liberty iname="edit" iexplain="Edit Image"}</a></div></noscript>
{*								jspopup href="`$smarty.const.FISHEYE_PKG_URL`edit_image.php?content_id=`$galItem->mInfo.content_id`" title="edit image" *}
{*								<a target="_new" href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$galItem->mInfo.content_id}">{biticon ipackage=liberty iname="edit" iexplain="Edit Image"}</a> *}
								<br />
								{/if}
							{/if}
							<strong>{tr}Description{/tr}</strong>: {$galItem->mInfo.data|default:"[ {tr}none{/tr} ]"} <br />
						</td>

						<td style="text-align:right;width:12em;">
							<label>{tr}Gallery Image{/tr}: <input type="radio" name="gallery_preview_content_id" value="{$galItem->mContentId}" {if $gContent->getField('preview_content_id') == $galItem->mContentId}checked="checked"{/if}/></label><br />
							<label>{if $galItem->getField('is_favorite')}{biticon iname="favorite" ipackage="users" iexplain=""}{/if}{tr}Favorite Image{/tr}: <input type="checkbox" name="is_favorite[]" value="{$galItem->mContentId}" {if $galItem->getField('is_favorite')}checked="checked"{/if}/></label><br />
							<label>{tr}Batch Select{/tr}: <input type="checkbox" name="batch[]" value="{$galItem->mContentId}" /></label><br />
							<label>{tr}Position{/tr}</label>: <input type="text" size="5" style="text-align:right;" maxlength="15" name="imagePosition[{$galItem->mContentId}]" id="imagePosition-{$galItem->mContentId}" value="{$galItem->mInfo.item_position}"/>
						</td>
					</tr>
				{/foreach}
				<tr>
					<td colspan="4" align="right">
						{tr}Use Random Gallery Image{/tr} <input type="radio" name="gallery_preview_content_id" id="gallery_preview_content_id" value="" {if $gContent->mInfo.preview_content_id == ""}checked="checked"{/if} /><br/>
						<script type="text/javascript">/* <![CDATA[ */
							document.write("<label>{tr}Batch select all images{/tr} <input name=\"switcher\" id=\"switcher\" type=\"checkbox\" onclick=\"switchCheckboxes(this.form.id,'batch[]','switcher')\" /></label>");
						/* ]]> */</script>
					</td>
				</tr>
			</table>

			<br />
			<br />

			<div class="row">
				{formlabel label="Batch commands" for=""}
				{forminput}
					<select name="batch_command">
							<option value=""></option>
							<option value="delete">{tr}Delete{/tr}</option>
							<option value="remove">{tr}Remove{/tr} ({tr}Don't delete if in other galleries{/tr})</option>
							<option value="thumbnail">{tr}Regenerate Thumbnails{/tr}</option>
							<optgroup label="{tr}Rotate{/tr}">
								<option value="rotate:90">&gt;&gt; {tr}Rotate Clockwise{/tr}</option>
								<option value="rotate:-90">&lt;&lt; {tr}Rotate Counter Clockwise{/tr}</option>
							</optgroup>
							{if $gBitSystem->isPackageActive( 'gatekeeper' ) }
								<optgroup label="{tr}Set Security to{/tr}">
									<option value="security:">~~ {tr}Publically Visible{/tr} ~~</option>
									{foreach from=$securities key=secId item=sec}
										<option value="security:{$secId}">{tr}Set Security to{/tr} "{$sec.security_description}"</option>
									{/foreach}
								</optgroup>
							{/if}
							{if count($galleryList) > 1}
								<optgroup label="{tr}Copy to Gallery{/tr}">
									{foreach from=$galleryList item=gal key=galleryId}
										{if $gContent->mInfo.content_id ne $gal.content_id}
											<option value="gallerycopy:{$gal.content_id}">{$gal.title|escape|truncate:50}</option>
										{/if}
									{/foreach}
								</optgroup>
								<optgroup label="{tr}Move to Gallery{/tr}">
									{foreach from=$galleryList item=gal key=galleryId}
										{if $gContent->mInfo.content_id ne $gal.content_id}
											<option value="gallerymove:{$gal.content_id}">{$gal.title|escape|truncate:50}</option>
										{/if}
									{/foreach}
								</optgroup>
							{/if}
					</select>

					{formhelp note="With selected images do the following"}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Re-order Gallery by" for="reorder_gallery"}
				{forminput}
					<select name="reorder_gallery" id="reorder_gallery">
						<option value=""></option>
						<option value="upload_date">{tr}Date Uploaded{/tr}</option>
						<option value="caption">{tr}Image Title{/tr}</option>
						<option value="file_name">{tr}File Name{/tr}</option>
						<option value="random">{tr}Random{/tr}</option>
					</select>
					{formhelp note="This will reset the position for every image in this gallery."}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="cancel" value="{tr}Back{/tr}"/> <input type="submit" name="updateImageOrder" value="{tr}Save Changes{/tr}"/>
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .fisheye -->
{/strip}
