<p class="note">
	{tr}Display images from the fisheye gallery. If <kbd>maxlen</kbd> is set, the image's title is shown, and truncated to the specified number of letters. If <kbd class="param">description</kbd> is set, the image's description is shown and can be truncated by setting <kbd>maxlendesc</kbd>. <kbd>recent_users</kbd> will display the most recent image from the most recent users.{/tr}
	<br />
	<span class="example">{tr}Example:{/tr} <kbd>sort_mode=hits&amp;maxlen=111&amp;description=yes&amp;maxlendesc=222</kbd></span>
</p>

<dl>
	<dt class="param"><kbd>sort_mode</kbd></dt>
	<dd><em>{tr}String{/tr}</em></dd>
	<dd><abbr title="{tr}default{/tr}" class="default">random</abbr>, created, hits</dd>

	<dt class="param"><kbd>maxlen</kbd></dt>
	<dd><em>{tr}Boolean{/tr}</em></dd>

	<dt class="param"><kbd>description</kbd></dt>
	<dd><em>{tr}Boolean{/tr}</em></dd>

	<dt class="param"><kbd>maxlendesc</kbd></dt>
	<dd><em>{tr}Numeric{/tr}</em></dd>

	<dt class="param"><kbd>recent_users</kbd></dt>
	<dd><em>{tr}Boolean{/tr}</em></dd>
</dl>
