<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd ">
<html>
<head>
<title>Piwik - Your Web Analytics Reports</title>
</head>
<body>

{loadJavascriptTranslations modules='Home'}

<script type="text/javascript">
var period = "{$period}";
var currentDateStr = "{$date}";
var minDateYear = {$minDateYear};
var minDateMonth = {$minDateMonth};
var minDateDay = {$minDateDay};
</script>

<script type="text/javascript" src="libs/jquery/jquery.js"></script>

<script type="text/javascript" src="themes/default/common.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.dimensions.js"></script>
<script type="text/javascript" src="libs/jquery/tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="libs/jquery/truncate/jquery.truncate.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.scrollTo.js"></script>
<script type="text/javascript" src="libs/jquery/jquery-calendar.js"></script>
<script type="text/javascript" src="libs/swfobject/swfobject.js"></script>

<script type="text/javascript" src="plugins/Home/templates/datatable.js"></script>
<script type="text/javascript" src="plugins/Home/templates/calendar.js"></script>

<script type="text/javascript" src="plugins/Home/templates/date.js"></script>

<script type="text/javascript" src="libs/jquery/jquery.blockUI.js"></script>
<script type="text/javascript" src="libs/jquery/ui.mouse.js"></script>
<script type="text/javascript" src="libs/jquery/ui.sortable_modif.js"></script>

<link rel="stylesheet" href="plugins/Home/templates/datatable.css">
<link rel="stylesheet" href="plugins/Dashboard/templates/dashboard.css">

<style type="text/css">@import url(libs/jquery/jquery-calendar.css);</style>

<script type="text/javascript" src="libs/jquery/superfish_modified.js"></script>
<script type="text/javascript" src="plugins/Home/templates/menu.js"></script>
<link rel="stylesheet" type="text/css" href="plugins/Home/templates/menu.css" media="screen">
<link rel="stylesheet" type="text/css" href="plugins/Home/templates/style.css" media="screen">

<span id="loggued">
<form action="{url idSite=null}" method="GET" id="siteSelection">
<small>
	<strong>{$userLogin}</strong>
	| 
<span id="sitesSelection">
{hiddenurl idSite=null}
Site <select name="idSite" onchange='javascript:this.form.submit()'>
	<optgroup label="Sites">
	   {foreach from=$sites item=info}
	   		<option label="{$info.name}" value="{$info.idsite}" {if $idSite==$info.idsite} selected="selected"{/if}>{$info.name}</option>
	   {/foreach}
	</optgroup>
</select>
</span> | {if $userLogin=='anonymous'}<a href='?module=Login'>{'Login_LogIn'|translate}</a>{else}<a href='?module=Login&action=logout'>{'Login_Logout'|translate}</a>{/if}</a>
</small>
</form>
</span>

<span id="h1"><a href='http://piwik.org'>Piwik</a> </span><span id="subh1"> # open source web analytics</span><br>
<br>
<div id="stuff">
	<div>
		<span id="messageToUsers"><a href='http://piwik.org'>Piwik</a> is a collaborative project and still Beta. If you want to help, please <u><a href="mailto:hello@piwik.org?subject=Piwik">contact us</a></u>.</span> 
		{include file="Home/templates/links_misc_modules.tpl"}
	</div>
</div>


<noscript>
<span id="javascriptDisable">
{'Home_JavascriptDisabled'|translate:'<a href="">':'</a>'}
</span>
</noscript>
{include file="Home/templates/period_select.tpl"}

<br><br>
{include file="Home/templates/menu.tpl"}

<div style='clear:both'></div>

<div id="loadingPiwik" {if $basicHtmlView}style="display:none"{/if}><img src="themes/default/images/loading-blue.gif"> {'General_LoadingData'|translate}</div>
<div id="loadingError">{'General_ErrorRequest'|translate}</div>
<div id='content'>
{if $content}{$content}{/if}
</div>

{include file="Home/templates/piwik_tag.tpl"}
