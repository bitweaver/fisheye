{* $Header: /cvsroot/bitweaver/_bit_fisheye/templates/Attic/create_collection.tpl,v 1.1 2005/06/19 04:36:24 bitweaver Exp $ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin structure">
	<div class="header">
		<h1>{tr}Collections{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Create A New Collection"}
			<p>{tr}Once you have created your collection, you can add galleries or images, and organize them.{/tr}</p>

			<div class="row">
				{formfeedback error=`$errors.title`}
				{formlabel label="Collection Name" for="name"}
				{forminput}
					<input type="text" name="name" id="name" size="50" maxlength="240"/>
					{formhelp note="This is the name of your collection."}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" value="{tr}create collection{/tr}" name="createstructure" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .admin -->
{/strip}