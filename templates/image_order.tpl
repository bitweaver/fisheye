<div class="admin fisheye">
	<div class="header">
		<h1>{tr}Batch Edit Images{/tr}: <a href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gContent->mGalleryId}">{$gContent->mInfo.title}</a></h1>
	</div>

	<div class="body">
		{form id="batch_order" legend="Batch Edit Images"}
{strip}
			<input type="hidden" name="gallery_id" value="{$gContent->mGalleryId}"/>

			{formfeedback hash=$formfeedback}

			<table class="data">
				<caption>{tr}Image Order{/tr}</caption>
				<tr>
					<td style="width:1px;"></td>
					<th scope="col" style="width:1px;">{tr}Thumbnail{/tr}</th>
					<th scope="col">{tr}Title and Position{/tr}</th>
					<th scope="col">{tr}Miscellaneous{/tr}</th>
				</tr>

				{counter start=0 print=false assign=imageCount}
				{section name=ix loop=$galleryImages}
					<tr class="{cycle values='even,odd'}">
						{if $imageCount % $gContent->mInfo.images_per_page == 0}
							{if $imageCount ne 0}
									<td colspan="5">&nbsp;</td>
								</tr>
								<tr class="{cycle}">
							{/if}
							<th style="block-progression:rl;writing-mode:tb-rl;filter:flipv fliph;text-align:center;" rowspan="{$gContent->mInfo.images_per_page}">
								{tr}Page{/tr} {math equation="imgCount / imagesPerPage + 1"
									imgCount=$imageCount
									imagesPerPage=$gContent->mInfo.images_per_page}
							</th>
						{/if}

						{counter print=false}
						<td class="{$galleryImages[ix]->mType.content_type_guid}">
							<a href="{$galleryImages[ix]->getDisplayUrl()|escape}"><img class="thumb" src="{$galleryImages[ix]->getThumbnailUrl()|replace:"&":"&amp;"}?{math equation="1 + rand(1,9999)"}" alt="{$galleryImages[ix]->mInfo.title}" /></a>
						</td>

						<td>
							<div class="row">
								{formlabel label="Title" for="imageTitle-`$galleryImages[ix]->mContentId`"}
								{forminput}
									<input type="text" maxlength="160" size="20" name="imageTitle[{$galleryImages[ix]->mContentId}]" id="imageTitle-{$galleryImages[ix]->mContentId}" value="{$galleryImages[ix]->mInfo.title}"/>
									{if $galleryImages[ix]->mInfo.user_id == $gBitUser->mUserId || $gBitUser->isAdmin()}
										&nbsp;<a href="{$smarty.const.FISHEYE_PKG_URL}edit_image.php?content_id={$gContent->mItems[ix]->mInfo.content_id}">{biticon ipackage=liberty iname="edit" iexplain="Edit Image"}</a>
									{/if}
								{/forminput}
							</div>

							<div class="row">
								{formlabel label="Position" for="imagePosition-`$galleryImages[ix]->mContentId`"}
								{forminput}
									<input type="text" size="3" maxlength="3" name="imagePosition[{$galleryImages[ix]->mContentId}]" id="imagePosition-{$galleryImages[ix]->mContentId}" value="{$galleryImages[ix]->mInfo.position}"/>
								{/forminput}
							</div>

							<div class="row">
								{formlabel label="Uploaded" for="imagePosition-`$galleryImages[ix]->mContentId`"}
								{forminput}
									{$galleryImages[ix]->mInfo.created|bit_short_datetime}
								{/forminput}
							</div>

							<div class="row">
								{formlabel label="File name" for="imagePosition-`$galleryImages[ix]->mContentId`"}
								{forminput}
									{$galleryImages[ix]->mInfo.image_file.filename}
								{/forminput}
							</div>
						</td>

						<td style="text-align:right;">
							<label>{tr}Gallery Image{/tr}: <input type="radio" name="gallery_preview_content_id" value="{$galleryImages[ix]->mContentId}" {if $gContent->mInfo.preview_content_id == $galleryImages[ix]->mContentId}checked="checked"{/if}/></label><br />
							<label>{tr}Batch Select{/tr}: <input type="checkbox" name="batch[]" value="{$galleryImages[ix]->mContentId}" /></label>
						</td>
					</tr>
				{/section}
			</table>
{/strip}
			<div class="row" style="text-align:right;">
				<script type="text/javascript">//<![CDATA[
					document.write("<label for=\"switcher\">{tr}Select all images{/tr}</label> ");
					document.write("<input name=\"switcher\" id=\"switcher\" type=\"checkbox\" onclick=\"switchCheckboxes(this.form.id,'batch[]','switcher')\" />");
				//]]></script>
			</div>
{strip}

			{legend legend="With selected images do the following"}
				<div class="row">
					{formlabel label="Use Random Gallery Image" for="gallery_preview_content_id"}
					{forminput}
						<input type="radio" name="gallery_preview_content_id" id="gallery_preview_content_id" value="" {if $gContent->mInfo.preview_content_id == ""}checked="checked"{/if} />
						{formhelp note="Using the Gallery Image radio button you can specify what image is used to identify this particular gallery."}
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Batch commands" for=""}
					{forminput}
						<select name="batch_command">
							<option value=""></option>
							<option value="delete">{tr}Delete{/tr}</option>
							<option value="thumbnail">{tr}Regenerate Thumbnails{/tr}</option>
							<option value="rotate:-90">&lt;&lt; {tr}Rotate Counter Clockwise{/tr}</option>
							<option value="rotate:90">&gt;&gt; {tr}Rotate Clockwise{/tr}</option>
							{if $gBitSystem->isPackageActive( 'gatekeeper' ) }
								<option value="security:">{tr}Set Security to{/tr} ~~ {tr}Publically Visible{/tr} ~~</option>
								{foreach from=$securities key=secId item=sec}
									<option value="security:{$secId}">{tr}Set Security to{/tr} "{$sec.security_description}"</option>
								{/foreach}
							{/if}
							{foreach from=$galleryList item=gal key=galleryId}
								{if $gContent->mInfo.content_id ne $gal.content_id}
									<option value="gallerycopy:{$gal.content_id}">{tr}Copy to gallery{/tr} "{$gal.title|truncate:30}"</option>
								{/if}
							{/foreach}
							{foreach from=$galleryList item=gal key=galleryId}
								{if $gContent->mInfo.content_id ne $gal.content_id}
									<option value="gallerymove:{$gal.content_id}">{tr}Move to gallery{/tr} "{$gal.title|truncate:30}"</option>
								{/if}
							{/foreach}
						</select>
						{formhelp note=""}
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
						{formhelp note=""}
					{/forminput}
				</div>
			{/legend}

			<div class="row submit">
				<input type="submit" name="cancel" value="{tr}Back{/tr}"/> <input type="submit" name="updateImageOrder" value="{tr}Save Changes{/tr}"/>
			</div>
{/strip}
		{/form}
	</div><!-- end .body -->
</div><!-- end .fisheye -->
