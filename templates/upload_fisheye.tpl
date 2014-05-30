{if !$gLibertySystem->hasService('upload')}
	{assign var=onSubmit value="javascript:BitBase.disableSubmit('submitbutton');"}
	{assign var=id value=fishid}
{/if}

{strip}
<div class="admin fisheye">
	<div class="header">
		<h1>{booticon iname="icon-cloud-upload" ipackage="icons" iexplain="^"} {tr}Upload Photos{/tr}</h1>
	</div>

	<div class="body">
		{form enctype="multipart/form-data" onsubmit=$onSubmit id=$id|default:photoupload target=$target action=$action}
			<div id="uploadblock">
				{jstabs}
					{jstab title="Upload Files"}
						{formfeedback note=$quotaMessage}

						<p class="warning">{booticon iname="icon-warning-sign"  ipackage="icons"  iexplain=Warning iforce=icon} {tr}The maximum file size you can upload is {$uploadMax} Megabytes{/tr}</p>
						{formfeedback error=$errors}

						{formhelp note="Here you can upload files. You can upload single files, or you can upload archived files (.zip's, .tar's, etc. NOTE: .sitx on Mac OS X generally does not work) Archived uploads will automatically be decompressed, and a gallery will created for every gallery in it. If you have nested folders, the hierarchy will be maintained for you with nested galleries." force=true}
						{if $gBrowserInfo.platform=='mac'}
							{formhelp note="Mac Users: The newer .sitx format is not supported currently because the makers of the StuffIt application have not released new versions of their software for servers. Please use DropZip or similar for best results." force=true}
						{/if}

						<input type="hidden" name="gallery_id" value="{$galleryId|escape}"/>
						<input type="hidden" name="save_image" value="save" />
						<input type="hidden" name="image_id" value="{$imageId}"/>
						<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" />

						<br/>
						{if $gLibertySystem->hasService('upload')}
							{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_upload_form_tpl"}
						{elseif $gBitSystem->isFeatureActive( 'fisheye_extended_upload_slots' )}
							<h2>{tr}Upload Images{/tr}</h2>
							{include file="bitpackage:kernel/upload_slot_inc.tpl" hash_key=imagedata}
						{else}
							
							<div class="form-group">
								{formlabel label="Select File(s)"}
								{forminput}
									<input type="file" name="file0" id="fileupload" />
									{formhelp note="To upload more than one file, click on choose repeatedly."}
								{/forminput}
							</div>

							<div class="form-group">
								{formlabel label="Selected File(s)" for=""}
								{forminput}
									<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/multifile.js"></script>
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
					{/jstab}

					{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_tab_tpl"}

					{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_upload_tab_tpl"}
				{/jstabs}

			<div class="width48p floatright box">
				<div class="form-group">
					{if !$gBitUser->hasPermission( 'p_fisheye_create' )}
						{formfeedback warning="Please make sure you select a gallery to load your images into, otherwise your images will be discarded"}
					{/if}
					{formlabel label="Add File(s) to these Galleries"}
					{forminput}
						{if $galleryTree}
							<div class="gallerytree">
								{$galleryTree}
							</div>						
						{else}
							<p class="norecords">
								{tr}No Galleries Found{/tr}.<br />
								{tr}The following gallery will automatically be created for you{/tr}: <strong>{displayname hash=$gBitUser->mInfo nolink=1}'s Gallery</strong>
							</p>
						{/if}
					{/forminput}
				</div>
			</div>
			<div class="width50p floatleft">
				{if $gBitUser->hasPermission( 'p_fisheye_upload_nonimages' )}
					<div class="form-group">
						<label class="checkbox">
							<input type="checkbox" id="process_archive" name="process_archive" value="true" checked="checked" />Process Archive(s)
							{formhelp note="If you don't want to have archived files processed and extracted, please uncheck the above box."}
						</label>
					</div>
				{else}
					<input type="hidden" name="process_archive" value="true" />
				{/if}

				{if function_exists('exif_read_data')}
					<div class="form-group">
						<label class="checkbox">
							<input type="checkbox" id="rotate_image" name="rotate_image" value="auto" checked="checked" />Auto-Rotate Images
							{formhelp note="If your camera was turned sideways when the image was taken, this will attempt to orient the image correctly."}
						</label>
					</div>
				{/if}

				<div class="form-group">
					<label class="checkbox">
						<input type="checkbox" id="use_filenames" name="use_filenames" value="true"/>Use Filenames
						{formhelp note="If you would like to name your images based upon their filenames rather than their EXIF data."}
					</label>
				</div>

				<div class="form-group">
					{include file="bitpackage:fisheye/resize_image_select.tpl"}
				</div>
			</div>

			{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"}

			{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_upload_mini_tpl"}

			<div class="form-group submit">
				<noscript><p class="highlight">{tr}Please don't press the save button more than once!<br />Depending on what you are uploading and the system, this can take a few minutes.{/tr}</p></noscript>
				<input type="submit" class="btn btn-default" id="submitbutton" value="{tr}Upload File(s){/tr}" {if $submitClick}onclick="{$submitClick}"{/if}/>
			</div>
			</div>
		{/form}
	</div> <!-- end .body -->
</div> <!-- end .fisheye -->
{/strip}

{if $gLibertySystem->hasService('upload')}
	{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_upload_js_tpl"}
{/if}

