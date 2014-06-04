{assign var=thumbsize value='avatar'}
<div align="center" height="500" class="gallery">
	<div  id="g1" class="galleryCont">
		{foreach from=$gContent->mItems item=galItem}
			<img class="imgThumb" src="{$galItem->mInfo.thumbnail_url.$thumbsize}">
			<img class="imgFull" src="{$galItem->mInfo.thumbnail_url.large}">
			<div class="imgDesc">{$galItem->mInfo.title|escape}</div>
		{/foreach}
	</div>
</div>
