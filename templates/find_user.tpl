<!DOCTYPE html 	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Find User</title>
	<!--[if gte IE 5.5000]>
		<script type="text/javascript" src="{$gBitLoc.THEMES_PKG_URL}js/pngfix.js"></script>
	<![endif]-->

	<script type="text/javascript" src="{$gBitLoc.KERNEL_PKG_URL}bitweaver.js"></script>
	<style type="text/css">
	<!--
	  @import url({$gBitLoc.THEMES_PKG_URL}base.css);
	  {if $gBitLoc.styleSheet}@import url({$gBitLoc.styleSheet});{/if}
	  {if $gBitLoc.browserStyleSheet}@import url({$gBitLoc.browserStyleSheet});{/if}
	  {if $gBitLoc.customStyleSheet}@import url({$gBitLoc.customStyleSheet});{/if}
	-->
	</style>

	{literal}
		<script type="text/javascript"><!--
		function returnUserInfo(userId, username) {
			self.opener.document.getElementById("found_user_id").value = userId;
			self.opener.document.getElementById("found_username").innerHTML = username;
			self.opener.document.getElementById("found_username_form_element").value = username;
			self.close();
		}
		--></script>
	{/literal}
{strip}
</head>
<body>
	<div class="finduser">
	<h2>User Search</h2>
		<form name="formUserSearch" action="{$PHP_SELF}">
			<div class="row">
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
			<tr class="row">
				<td>{$foundUsers[ix].login}</td>
				<td>{$foundUsers[ix].real_name}</td>
				<td>{$foundUsers[ix].user_id}</td>
				<td><a href="javascript:void(null);" style="cursor:hand;" onclick="returnUserInfo({$foundUsers[ix].user_id},'{$foundUsers[ix].real_name}');">Select User</a></td>
			</tr>			
		{/section}
		</table>
	</div>
	{/if}
</body>
</html>
{/strip}