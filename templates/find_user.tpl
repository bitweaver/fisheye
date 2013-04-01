<!DOCTYPE html 	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Find User</title>
	<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/bitweaver.js"></script>

	<style type="text/css"><!--
		{if $gBitSystem->mStyles.styleSheet}@import url({$gBitSystem->mStyles.styleSheet});{/if}
	--></style>

	{literal}
		<script type="text/javascript">//<![CDATA[
		function returnUserInfo(userId, username) {
			self.opener.document.getElementById("found_user_id").value = userId;
			self.opener.document.getElementById("found_username").innerHTML = username;
			self.opener.document.getElementById("found_username_form_element").value = username;
			self.close();
		}
		//]]></script>
	{/literal}
{strip}
</head>
<body>
	<div class="finduser">
	<h2>User Search</h2>
		<form name="formUserSearch" action="{$smarty.server.SCRIPT_NAME}">
			<div class="control-group">
				Username: <input type="text" name="find" value="{$find}"/>
			</div>
			<div class="formsubmit">
				<input type="submit" name="submitUserSearch" value="Search"/>
			</div>
		</form>
	</div>

	{if $foundUsers}
	<div class="body">
		<h2>Search Results</h2>
		<table>
		<tr><th>Username</th><th>Real Name</th><th>User Id</th><th>Actions</th></tr>
		{section name=ix loop=$foundUsers}
			<tr class="control-group">
				<td>{$foundUsers[ix].login}</td>
				<td>{$foundUsers[ix].real_name}</td>
				<td>{$foundUsers[ix].user_id}</td>
				<td><a href="javascript:void(null);" style="cursor:hand;" onclick="returnUserInfo({$foundUsers[ix].user_id},'{$foundUsers[ix].real_name}');">Select User</a></td>
			</tr>
		{/section}
		</table>
	</div><!-- end .body -->
	{/if}
</body>
</html>
{/strip}
