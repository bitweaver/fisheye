{strip}
<div class="body">
	{formfeedback error=$failedLogin}
	<h2>{$gContent->mInfo.title}</h2>
	
	This gallery has been password protected by the owner.
	
	{form ifile="$PHP_SELF" legend="Authenticate"}
	<input type="hidden" name="content_id" value="{$gContent->mContentId}" />
	
	<h3>{$gContent->mInfo.access_question}:</h3>
	<div class="row">
		{formlabel label="Answer" for="try-access-answer"}
		{forminput}
			<input type="text" name="try_access_answer" id="try-access-answer" value="" maxlength="128" size="50"/>
			<input type="submit" name="submit_answer" value="Submit Answer"/>
		{/forminput}
	</div>
	{/form}
</div>
{/strip}
