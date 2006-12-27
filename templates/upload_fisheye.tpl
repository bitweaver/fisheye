{if $gBitSystem->isPackageActive('gigaupload')}
	{include file="bitpackage:gigaupload/js_inc.tpl"}
{else}
	{assign var=onSubmit value="javascript:disableSubmit('submitbutton');"}
	{assign var=id value=fishid}
{/if}

{strip}
<div class="admin fisheye">
	<div class="header">
		<h1>{tr}Upload Files{/tr}</h1>
	</div>

	<div class="body">
		{form enctype="multipart/form-data" onsubmit=$onSubmit id=$id target=$target action=$action}
			<div id="uploadblock">
				{jstabs}
					{jstab title="Upload Files"}
						{legend legend="Upload Files"}
							{formfeedback note=$quotaMessage}

							<p class="warning">{biticon ipackage="icons" iname="dialog-warning" iexplain=Warning iforce=icon} The maximum file size you can upload is {$uploadMax} Megabytes</p>
							{formfeedback error=$errors}

							{formhelp note="Here you can upload files. You can upload single files, or you can upload archived files (.zip's, .tar's, etc. NOTE: .sitx on Mac OS X generally does not work) Archived uploads will automatically be decompressed, and a gallery will created for every gallery in it. If you have nested folders, the hierarchy will be maintained for you with nested galleries." force=true}
							{if $gBrowserInfo.platform=='mac'}
								{formhelp note="Mac Users: The newer .sitx format is not supported currently because the makers of the StuffIt application have not released new versions of their software for servers. Please use DropZip or similar for best results." force=true}
							{/if}

							<input type="hidden" name="gallery_id" value="{$galleryId|escape}"/>
							<input type="hidden" name="save_image" value="save" />
							<input type="hidden" name="image_id" value="{$imageId}"/>
							<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" />

								{if $gBitSystem->isPackageActive( 'gigaupload' )}
									{$gigaPopup}
									{include file="bitpackage:gigaupload/form_inc.tpl"}
								{elseif $gBitSystem->isFeatureActive( 'fisheye_extended_upload_slots' )}
									<br />
									<h2>{tr}Upload Images{/tr}</h2>

									<div id="slot0">
										<div class="row">
											{formlabel label="Title" for="title0"}
											{forminput}
												<input type="text" name="imagedata[0][title]" id="title0" size="50" />
												<br />
												<input type="file" name="upload0" id="upload0" size="35" />
											{/forminput}
										</div>

										<div class="row">
											{formlabel label="Description" for="edit0"}
											{forminput}
												<textarea rows="2" cols="50" name="imagedata[0][edit]" id="edit0"></textarea>
											{/forminput}
										</div>

										<script type="text/javascript">/* <![CDATA[ */
											document.write( "<input id=\"button1\" type=\"button\" onclick=\"javascript:showById('slot1');hideById('button1')\" value=\"{tr}Add slot{/tr}\" />" );
										/* ]]> */</script>
									</div>

									{foreach from=$uploadSlots item=dummy key=key}
										{assign var=slot value=$key+2}

										<script type="text/javascript">/* <![CDATA[ */
											document.write( "<div id=\"slot{$slot-1}\" style=\"display:none;\">" );
										/* ]]> */</script>

										<hr />

										<div class="row">
											{formlabel label="Title" for="title$slot"}
											{forminput}
											<input type="text" name="imagedata[{$slot}][title]" id="title{$slot}" size="50" />
												<br />
												<input type="file" name="upload{$slot}" id="upload{$slot}" size="35" />
											{/forminput}
										</div>

										<div class="row">
											{formlabel label="Description" for="edit$slot"}
											{forminput}
											<textarea rows="2" cols="50" name="imagedata[{$slot}][edit]" id="edit{$slot}"></textarea>
											{/forminput}
										</div>

										<script type="text/javascript">/* <![CDATA[ */
											{if count($uploadSlots) gt $slot-1}
												document.write( "<input id=\"button{$slot}\" type=\"button\" onclick=\"javascript:showById('slot{$slot}');hideById('button{$slot}')\" value=\"{tr}Add slot{/tr}\" />" );
											{else}
												document.write( "</div>" );
											{/if}
										/* ]]> */</script>
									{/foreach}
								{else}
									<div class="row">
										{formlabel label="Select File(s)"}
										{forminput}
											<input type="file" name="file0" id="fileupload" />
											{formhelp note="To upload more than one file, please click on choose repeatedly<br />(javascript has to be enabled for this to work)."}
										{/forminput}
									</div>

									<div class="row">
										{formlabel label="Selected File(s)" for=""}
										{forminput}
											<div id="fileslist"></div>
											<div class="clear"></div>
											{formhelp note="These files will be uploaded when you hit the upload button below."}
											<script type="text/javascript">/* <![CDATA[ Multi file upload */
												var multi_selector = new MultiSelector( document.getElementById( 'fileslist' ), 10 );
												multi_selector.addElement( document.getElementById( 'fileupload' ) );
											/* ]]> */</script>
										{/forminput}
									</div>
								{/if}
						{/legend}
					{/jstab}

					{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_tab_tpl}

					{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_upload_tab_tpl}
				{/jstabs}

					{if $gBitUser->hasPermission( 'p_fisheye_upload_nonimages' )}
						<div class="row">
							{formlabel label="Process Archive(s)" for="process_archive"}
							{forminput}
								<input type="checkbox" id="process_archive" name="process_archive" value="true" checked="checked" />
								{formhelp note="If you don't want to have archived files processed and extracted, please uncheck the above box."}
							{/forminput}
						</div>
					{else}
						<input type="hidden" name="process_archive" value="true" />
					{/if}

					{if function_exists('exif_read_data')}
						<div class="row">
							{formlabel label="Auto-Rotate Images" for="rotate_image"}
							{forminput}
								<input type="checkbox" id="rotate_image" name="rotate_image" value="auto" checked="checked" />
								{formhelp note="If your camera was turned sideways when the image was taken, this will attempt to orient the image correctly."}
							{/forminput}
						</div>
					{/if}

					<div class="row">
						{formlabel label="Add File(s) to these Galleries"}
						{forminput}
							{foreach from=$galleryList key=galId item=gal}
								<input type="checkbox" name="galleryAdditions[]" value="{$galId}"
									{if  $gContent->mGalleryId == $galId}
										checked="checked"
									{else}
										{section name=gx loop=$gContent->mInfo.parent_galleries}
											{if ($gContent->mInfo.parent_galleries[gx].gallery_id == $galId)}
												checked="checked"
											{/if}
										{/section}
									{/if}
								/>
								<a href="{$smarty.const.FISHEYE_PKG_URL}view.php?gallery_id={$gal.gallery_id}">{$gal.title|escape}</a>
								<br />
							{foreachelse}
								<p class="norecords">
									{tr}No Galleries Found{/tr}.<br />
									{tr}The following gallery will automatically be created for you{/tr}: <strong>{displayname hash=$gBitUser->mInfo nolink=1}'s Gallery</strong>
								</p>
							{/foreach}
						{/forminput}

					<div class="row">
						{include file="bitpackage:fisheye/resize_image_select.tpl"}
					</div>
				</div>

				{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_mini_tpl}
			</div>

			{if $gBitSystem->isPackageActive( 'gigaupload' )}
				{include file="bitpackage:gigaupload/progress_container_inc.tpl"}
			{/if}

			<div class="row submit">
				<noscript><p class="highlight">{tr}Please don't press the save button more than once!<br />Depending on what you are uploading and the system, this can take a few minutes.{/tr}</p></noscript>
				<input type="submit" id="submitbutton" value="{tr}Upload File(s){/tr}" {if $submitClick}onclick="{$submitClick}"{/if}/>
			</div>
		{/form}
	</div> <!-- end .body -->
</div> <!-- end .fisheye -->
{/strip}
