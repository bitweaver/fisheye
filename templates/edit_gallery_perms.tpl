<div class="admin fisheye">
{formfeedback error=$fisheyeErrors warning=$fisheyeWarnings success=$fisheyeSuccess}
	<div class="header">
		<h1>{tr}Edit Gallery Permissions{/tr}: {$gContent->mInfo.title}</h1>
	</div>
	
	<div class="body">
		<!-- Add New User Permissions -->
		{legend legend="Add New User Permissions"}
		<form name="newUserPermLevelForm" method="POST" action="{$PHP_SELF}">
		<div class="row">
			<input type="hidden" id="found_user_id" name="new_perm_user_id" value="" />
			<input type="hidden" name="gallery_id" value="{$gContent->mGalleryId}" />
			<input type="hidden" name="found_username" id="found_username_form_element" />
			{tr}User{/tr}:
			<span id="found_username"></span>
			<a href="{$smarty.const.FISHEYE_PKG_URL}find_user.php" title="{tr}Opens user search tool in a new window{/tr}" onkeypress="popUpWin(this.href,'standard',600,400);" onclick="popUpWin(this.href,'standard',600,400);return false;">{tr}User Browser{/tr}</a>
		</div>
		<div class="row">
			{tr}Permission Level:{/tr}
			<select name="new_perm_level">
				<option value="{$FISHEYE_PERM_VIEWER}">{tr}Viewer{/tr}</option>
				<option value="{$FISHEYE_PERM_CONTRIBUTOR}">{tr}Contributor{/tr}</option>
				<option value="{$FISHEYE_PERM_EDITOR}">{tr}Editor{/tr}</option>
				<option value="{$FISHEYE_PERM_ADMIN}">{tr}Admin{/tr}</option>
			</select>
		</div>
		<div class="row submit">
			<input type="submit" name="submitNewPermissions" value="Add New Permission"/>
		</div>
		</form>
		{/legend}
		
		<!-- Existing User Permissions -->
		{legend legend="Existing User Permissions"}
		<form name="updateUserPermsForm" method="POST" action="{$PHP_SELF}">
		<input type="hidden" name="gallery_id" value="{$gContent->mGalleryId}"/>
		<table>
		<tr><th>User</th><th>Access Level</th><th>Action</th></tr>
		{section name=ix loop=$userPerms}
		<tr>
			<td class="row">
				{displayname hash=$userPerms[ix]}
			</td>
			<td class="row">
				<select name="existingPerms[{$userPerms[ix].user_id}]">
					<option value="{$FISHEYE_PERM_VIEWER}" {if $userPerms[ix].perm_level == $FISHEYE_PERM_VIEWER}selected="selected"{/if}>{tr}Viewer{/tr}</option>
					<option value="{$FISHEYE_PERM_CONTRIBUTOR}" {if $userPerms[ix].perm_level == $FISHEYE_PERM_CONTRIBUTOR}selected="selected"{/if}>{tr}Contributor{/tr}</option>
					<option value="{$FISHEYE_PERM_EDITOR}" {if $userPerms[ix].perm_level == $FISHEYE_PERM_EDITOR}selected="selected"{/if}">{tr}Editor{/tr}</option>
					<option value="{$FISHEYE_PERM_ADMIN}" {if $userPerms[ix].perm_level == $FISHEYE_PERM_ADMIN}selected="selected"{/if}>{tr}Admin{/tr}</option>
				</select>
			</td>		
			<td>
				<a href="{$smarty.const.FISHEYE_PKG_URL}edit_gallery_perms.php?gallery_id={$gContent->mGalleryId}&remove_perm_user_id={$userPerms[ix].user_id}">{biticon ipackage=liberty iname="delete_small" iexplain="Remove Permission"}</a> 
			</td>	
		</tr>
		{sectionelse}
			<div class="row">
				<span class="norecords">{tr}No User Permssions Found{/tr}</span>
			</div>
		{/section}
		{if $userPerms}
			<tr class="row submit">
				<td colspan="3">
					<input type="submit" name="submitUpdatePerms" value="Update Permissions"/>
				</td>
			</tr>
		{/if}
		</table>		
		</form>
		{/legend}
	</div>
</div>
