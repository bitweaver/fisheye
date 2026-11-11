{* Light or dark for the viewer, kept in a cookie. Needs $feDark and $feThemeUrls. The page script switches without a reload; the links work without it. *}
{strip}
<nav class="fe-seg fe-theme" data-fe-theme="{$smarty.const.BIT_ROOT_URL}" aria-label="{tr}Appearance{/tr}">
	<a href="{$feThemeUrls.light|escape}" rel="nofollow" data-fe-theme-set="light" {if !$feDark}aria-current="true"{/if} title="{tr}Light{/tr}">{booticon iname="fa-sun"}</a>
	<a href="{$feThemeUrls.dark|escape}" rel="nofollow" data-fe-theme-set="dark" {if $feDark}aria-current="true"{/if} title="{tr}Dark{/tr}">{booticon iname="fa-moon"}</a>
</nav>
{/strip}
