<div class="forum_header">
    <div class="forum_title">
        <h2><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$index_title}></a></h2>
        <hr class="align_left"/>
        <{$index_desc}>
    </div>
</div>
<div class="clear"></div>
<{if $viewer_level gt 1}>
    <br>
  <fieldset style="border:1px solid #778;margin:1em 0;text-align:left;background-color:transparent;">
    <legend>&nbsp;<{$smarty.const._MD_NEWBB_ADMINCP}>&nbsp;</legend>    
    <div class="forum_stats">
        <div class="forum_stats_col left floatleft">
            <{$smarty.const._MD_NEWBB_TOPIC}>:
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=active#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_ADMIN}>"><{$smarty.const._MD_NEWBB_TYPE_ADMIN}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=pending#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_PENDING}>"><{if $wait_new_topic}>(
                    <span style="color:#ff0000;"><b><{$wait_new_topic}></b></span>
                    ) <{/if}><{$smarty.const._MD_NEWBB_TYPE_PENDING}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=deleted#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_DELETED}>"><{if $delete_topic}>(
                    <span style="color:#ff0000;"><b><{$delete_topic}></b></span>
                    ) <{/if}><{$smarty.const._MD_NEWBB_TYPE_DELETED}></a><br>
            <{$smarty.const._MD_NEWBB_POST2}>:
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?status=active#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_ADMIN}>"><{$smarty.const._MD_NEWBB_TYPE_ADMIN}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?status=pending#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_PENDING}>"><{if $wait_new_post}>(
                    <span style="color:#ff0000;"><b><{$wait_new_post}></b></span>
                    ) <{/if}><{$smarty.const._MD_NEWBB_TYPE_PENDING}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?status=deleted#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_DELETED}>"><{if $delete_post}>(
                    <span style="color:#ff0000;"><b><{$delete_post}></b></span>
                    ) <{/if}><{$smarty.const._MD_NEWBB_TYPE_DELETED}></a>
        </div>
        <div class="forum_stats_col right floatright">
            <{if $report_post}><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/admin/admin_report.php"><{$report_post}></a><{/if}>
            <br><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/moderate.php" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_SUSPEND}>"><{$smarty.const._MD_NEWBB_TYPE_SUSPEND}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/admin/index.php" target="_self" title="<{$smarty.const._MD_NEWBB_ADMINCP}>"><{$smarty.const._MD_NEWBB_ADMINCP}></a>
        </div>
        <div class="clear"></div>
    </div>
  </fieldset>
<{/if}>
<div class="dropdown floatleft">
    <{includeq file="db:newbb_index_menu.tpl"}>
</div>
<div class="clear"></div>
<br>

<!-- start index categories -->
<div class="index_category">
    <!-- start forum categories -->
    <{foreach item=category from=$categories}>
    <div class="head align_center">
        <div class="pointer floatleft"
             onclick="ToggleBlockCategory('<{$category.cat_element_id}>',(this.firstElementChild || this.children[0]) , '<{$category_icon.expand}>', '<{$category_icon.collapse}>','<{$smarty.const._MD_NEWBB_HIDE|escape:'quotes'}>','<{$smarty.const._MD_NEWBB_SEE|escape:'quotes'}>','toggle_block','toggle_none');">
            <{$category.cat_displayImage}>
        </div>
        <{if $category.cat_image}>
            <div class="floatleft"><img src="<{$category.cat_image}>" alt="<{$category.cat_title}>"/></div>
        <{/if}>
        <div class="floatleft">
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php?cat=<{$category.cat_id}>"><{$category.cat_title}></a>
            <{if $category.cat_description}><span class="desc"><{$category.cat_description}></span><{/if}>
        </div>
        <{if $category.cat_sponsor}>
            <div class="floatright">
                <span class="desc"><a href="<{$category.cat_sponsor.link}>" title="<{$category.cat_sponsor.title}>" target="_blank"><{$category.cat_sponsor.title}></a></span>
            </div>
        <{/if}>
        <div class="clear"></div>
    </div>
    <div id="<{$category.cat_element_id}>" class="toggle_<{$category.cat_display}>">
        <{if $category.forums}>
            <div class="forum_table">
                <div class="forum_row head align_center">
                    <div class="forum_folder">&nbsp;</div>
                    <div class="forum_name"><{$smarty.const._MD_NEWBB_FORUM}></div>
                    <div class="forum_topics"><{$smarty.const._MD_NEWBB_TOPICS}></div>
                    <div class="forum_posts"><{$smarty.const._MD_NEWBB_POSTS}></div>
                    <div class="forum_lastpost"><{$smarty.const._MD_NEWBB_LASTPOST}></div>
                </div>
                <!-- start forums -->
                <{foreach item=forum from=$category.forums}>
                <div class="forum_row head align_center">
                    <div class="forum_folder even <{if $forum.forum_read eq 1 }>forum-read<{else}>forum-new<{/if}> align_center"><{$forum.forum_folder}></div>
                    <div class="forum_name left odd">
                        <div class="index_forum">
    <span class="item"><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum.forum_id}>"><{$forum.forum_name}></a>
        <{if $rss_enable}>
            (
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?f=<{$forum.forum_id}>" target="_blank" title="RSS feed">RSS</a>
            )
        <{/if}>
        <br><{$forum.forum_desc}>
    </span>
                            <{if $forum.forum_moderators}>
                                <span class="extra left floatright">
    <{$smarty.const._MD_NEWBB_MODERATOR}>: <{$forum.forum_moderators}>
    </span>
                            <{/if}>
                        </div>
                        <{if $forum.subforum && $subforum_display == "collapse"}>
                            <div class="left"><{$smarty.const._MD_NEWBB_SUBFORUMS}>&nbsp;<{$img_subforum}>&nbsp;
                                <{foreach item=subforum from=$forum.subforum}>
                                &nbsp;[<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$subforum.forum_id}>"><{$subforum.forum_name}></a>]
                                <{/foreach}>
                            </div>
                        <{/if}>
                        <div class="clear"></div>
                    </div>
                    <div class="forum_topics even align_center">
                        <{if $stats[$forum.forum_id].topic.day}><strong><{$stats[$forum.forum_id].topic.day}></strong>/<{/if}>
                        <{$forum.forum_topics}>
                    </div>
                    <div class="forum_posts odd align_center">
                        <{if $stats[$forum.forum_id].post.day}><strong><{$stats[$forum.forum_id].post.day}></strong>/<{/if}>
                        <{$forum.forum_posts}>
                    </div>
                    <div class="forum_lastpost even right">
                        <{if $forum.forum_lastpost_subject}>
                            <{$forum.forum_lastpost_time}> <{$smarty.const._MD_NEWBB_BY}> <{$forum.forum_lastpost_user}>
                            <br>
                            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$forum.forum_lastpost_id}>">
                                <{$forum.forum_lastpost_subject}>&nbsp;&nbsp;<{$forum.forum_lastpost_icon}>
                            </a>
                        <{else}>
                            <{$smarty.const._MD_NEWBB_NOTOPIC}>
                        <{/if}>
                    </div>
                </div>
                <!-- start sub forums -->
                <{if $forum.subforum}>
                    <{if $subforum_display == "expand"}>
                        <div class="forum_row head align_center">
                            <div class="forum_folder">&nbsp;</div>
                            <div class="subforum_name">
                                <div class="forum_folder floatleft">
                                    <{$img_subforum}>&nbsp;
                                </div>
                                <div class="_col_end"><{$smarty.const._MD_NEWBB_SUBFORUMS}>&nbsp;</div>
                                <div class="clear"></div>
                            </div>
                            <div class="forum_topics">&nbsp;</div>
                            <div class="forum_posts">&nbsp;</div>
                            <div class="forum_lastpost">&nbsp;</div>
                        </div>
                        <{foreach item=subforum from=$forum.subforum}>
                        <div class="forum_row head align_center">
                            <div class="forum_folder odd">&nbsp;</div>
                            <div class="subforum_name left">
                                <div class="forum_folder even <{if $subforum.forum_read eq 1 }>forum-read<{else}>forum-new<{/if}> floatleft"><{$subforum.forum_folder}></div>
                                <div class="index_forum odd _col_end">
<span class="item"><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$subforum.forum_id}>"><strong><{$subforum.forum_name}></strong></a>
    <{if $rss_enable}>
        (
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?f=<{$subforum.forum_id}>" target="_blank" title="RSS feed">RSS</a>
        )
    <{/if}>
    <br><{$subforum.forum_desc}>
</span>
                                    <{if $subforum.forum_moderators}>
                                        <span class="extra left floatright">
<{$smarty.const._MD_NEWBB_MODERATOR}>: <{$subforum.forum_moderators}>
</span>
                                    <{/if}>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="forum_topics even">
                                <{if $stats[$subforum.forum_id].topic.day}><strong><{$stats[$subforum.forum_id].topic.day}></strong>/<{/if}>
                                <{$subforum.forum_topics}>
                            </div>
                            <div class="forum_posts odd">
                                <{if $stats[$subforum.forum_id].post.day}><strong><{$stats[$subforum.forum_id].post.day}></strong>/<{/if}>
                                <{$subforum.forum_posts}>
                            </div>
                            <div class="forum_lastpost even right">
                                <{if $subforum.forum_lastpost_subject}>
                                    <{$subforum.forum_lastpost_time}> <{$smarty.const._MD_NEWBB_BY}> <{$subforum.forum_lastpost_user}>
                                    <br>
                                    <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$subforum.forum_lastpost_id}>">
                                        <{$subforum.forum_lastpost_subject}>&nbsp;&nbsp;<{$subforum.forum_lastpost_icon}>
                                    </a>
                                <{else}>
                                    <{$smarty.const._MD_NEWBB_NOTOPIC}>
                                <{/if}>
                            </div>
                        </div>
                    <{/foreach}>
                    <{/if}>
                <{/if}>
                <!-- end sub forums -->
                <{/foreach}>
            </div>
        <{/if}>
        <!-- end forums -->
    </div>
    <!-- end cat display toggle-->
    <br>
    <{/foreach}>
    <!-- end forum categories -->
</div>
</div>
<!-- end index categories -->

<div class="icon_left">
    <{$img_forum_new}> = <{$smarty.const._MD_NEWBB_NEWPOSTS}><br>
    <{$img_forum}> = <{$smarty.const._MD_NEWBB_NONEWPOSTS}><br>
</div>
<div class="icon_right">
    <form action="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php" method="post" name="search" id="search">
        <input name="term" id="term" type="text" size="20"/>
        <input type="hidden" name="forum" id="forum" value="all"/>
        <input type="hidden" name="sortby" id="sortby" value="p.post_time desc"/>
        <input type="hidden" name="searchin" id="searchin" value="both"/>
        <input type="submit" id="submit" value="<{$smarty.const._MD_NEWBB_SEARCH}>"/>
        <br>
        [ <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php"><{$smarty.const._MD_NEWBB_ADVSEARCH}></a> ]
    </form>
    <{if $rss_button}>
        <br>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?c=<{$viewcat}>" target="_blank" title="RSS FEED">
            <{$rss_button}>
        </a>
        <br>
        <span style="font-size:0.7em;"><a href="https://xoops.org">NewBB Version  <{$version/100}></a></span>
        <br>
    <{/if}>
</div>
<div class="clear"></div>
<{if $currenttime}>
    <div>
        <div class="index_stat_foot even">
            <span><{$online.statistik}></span>
            <strong><{$smarty.const._MD_NEWBB_STATS}></strong>
        </div>
        <div class="index_stat_foot forum_stats odd">
            <div class="forum_stats_col odd left floatleft">
                <{$currenttime}><br>
                <{$lastvisit}><br>
                <{$smarty.const._MD_NEWBB_TOTALTOPICSC}>
                <strong><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php" title="<{$smarty.const._MD_NEWBB_ALL}>"><{$stats[0].topic.total}></a></strong>
                | <{$smarty.const._MD_NEWBB_TOTALPOSTSC}><strong><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php" title="<{$smarty.const._MD_NEWBB_ALLPOSTS}>"><{$stats[0].post.total}></a></strong>
                <{if $stats[0].digest.total}>
                    | <{$smarty.const._MD_NEWBB_TOTALDIGESTSC}><strong><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=digest" title="<{$smarty.const._MD_TOTALDIGESTSC}>"><{$stats[0].digest.total}></a></strong>
                <{/if}>
                <{if $userstats}>
                    <br>
                    <br>
                    <{*$userstats.lastvisit*}>
                    <br>
                    <{$userstats.lastpost}>
                <{/if}>
            </div>
            <div class="forum_stats_col odd right floatright">
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?status=new" title="<{$smarty.const._MD_NEWBB_VIEW_NEWPOSTS}>"><{$smarty.const._MD_NEWBB_VIEW_NEWPOSTS}></a><br>
                <{$smarty.const._MD_NEWBB_TODAYTOPICSC}><strong><{$stats[0].topic.day|default:0}></strong>
                | <{$smarty.const._MD_NEWBB_TODAYPOSTSC}><strong><{$stats[0].post.day|default:0}></strong>
                <{if $userstats}>
                    <br>
                    <br>
                    <{$userstats.topics}> | <{$userstats.posts}><{if $userstats.digests}><br><{$userstats.digests}><{/if}>
                <{/if}>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="clear"></div>
<{/if}>
<{if $online}>
    <{includeq file="db:newbb_online.tpl"}>
<{/if}>
<{includeq file='db:newbb_notification_select.tpl'}>
<!-- end module contents -->
