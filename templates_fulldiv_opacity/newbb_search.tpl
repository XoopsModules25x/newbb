<div class="forum_header">
	<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$forumindex}></a>
    <span class="delimiter">&raquo;</span>
	<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php"><{$smarty.const._SR_SEARCH}></a>
</div>
<{if $search_info}>
	<{includeq file="db:newbb_searchresults.tpl" results=$results}>
<{/if}>
<form name="Search" action="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php" method="get">
<div class="outer">
    <div class="head search_col floatleft right"><strong><{$smarty.const._SR_KEYWORDS}></strong>&nbsp;</div>
	<div class="even _col_end"><input type="text" name="term" value="<{$search_term}>" /></div>
	<div class="clear"></div>
    <div class="head search_col floatleft right"><strong><{$smarty.const._SR_TYPE}></strong>&nbsp;</div>
    <div class="even _col_end"><{$andor_selection_box}></div>
	<div class="clear"></div>
    <div class="head search_col floatleft right"><strong><{$smarty.const._MD_NEWBB_FORUMC}></strong>&nbsp;</div>
    <div class="even _col_end"><{$forum_selection_box}></div>
	<div class="clear"></div>
	<div class="head search_col floatleft right"><strong><{$smarty.const._SR_SEARCHIN}></strong>&nbsp;</div>
    <div class="even _col_end"><{$searchin_radio}></div>
	<div class="clear"></div>
    <div class="head search_col floatleft right"><strong><{$smarty.const._MD_NEWBB_AUTHOR}></strong>&nbsp;</div>
    <div class="even _col_end"><input type="text" name="uname" value="<{$author_select}>" /></div>
	<div class="clear"></div>
	<div class="head search_col floatleft right"><strong><{$smarty.const._MD_NEWBB_SORTBY}></strong>&nbsp;</div>
    <div class="even _col_end"><{$sortby_selection_box}></div>
	<div class="clear"></div>
	<div class="head search_col floatleft right"><strong><{$smarty.const._MD_NEWBB_SINCE}></strong>&nbsp;</div>
    <div class="even _col_end"><{$since_selection_box}></div>
	<div class="clear"></div>
    <div class="head search_col floatleft right" id="align_right" title="<{$smarty.const._MD_NEWBB_SELECT_STARTLAG_DESC}>"><strong><{$smarty.const._MD_SELECT_STARTLAG}></strong>&nbsp;</div>
    <div class="even _col_end" title="<{$smarty.const._MD_NEWBB_SELECT_STARTLAG_DESC}>"><input type="text" name="selectstartlag" value="<{$selectstartlag_select}>" /></div>
	<div class="clear"></div>
    <div class="head search_col floatleft right" id="align_right"><strong><{$smarty.const._MD_NEWBB_SELECT_LENGTH}></strong>&nbsp;</div>
    <div class="even _col_end"><input type="text" name="selectlength" value="<{$selectlength_select}>" /></div>
	<div class="clear"></div>
    <div class="head search_col floatleft right" id="align_right"><strong><{$smarty.const._MD_NEWBB_SELECT_HTML}></strong>&nbsp;</div>
    <div class="even _col_end"><{$selecthtml_radio}></div>
	<div class="clear"></div>
    <div class="head search_col floatleft right" id="align_right"><strong><{$smarty.const._MD_NEWBB_SELECT_EXCLUDE}></strong>&nbsp;</div>
    <div class="even _col_end"><{$selectexclude_check_box}></div>
	<div class="clear"></div>
	<div class="head search_col floatleft right"><strong><{$smarty.const._MD_NEWBB_SHOWSEARCH}></strong>&nbsp;</div>
    <div class="even _col_end"><{$show_search_radio}></div>
	<div class="clear"></div>
    <{if $search_rule}>
		<div class="head search_col floatleft right"><strong><{$smarty.const._SR_SEARCHRULE}></strong>&nbsp;</div>
        <div class="even _col_end"><{$search_rule}></div>
		<div class="clear"></div>
	<{/if}>
    <div class="head search_col floatleft right">&nbsp;</div>
    <div class="even _col_end"><input type="submit" value="<{$smarty.const._MD_NEWBB_SEARCH}>" /></div>
	<div class="clear"></div>
</div>
</form>
