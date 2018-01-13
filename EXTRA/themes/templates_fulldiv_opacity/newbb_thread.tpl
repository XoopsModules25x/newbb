<!-- Assign forum_post_prefix smarty -->
<{if $forum_post_prefix === null }>
    <!-- change the value to what you prefer. even value="" (no prefix) is acceptable -->
    <{assign var=forum_post_prefix value="post-"}>
    <!-- it is the first time then add id=0 for recognizoing top of the topic to scroll when $post_id=0 (just $topic_id in the URL) -->
    <div id="<{$forum_post_prefix}>0"></div>
<{/if}>
<{if $topic_post.poster.uid gt -1}>
<div class="thread_title_bar <{if $topic_post.post_no == 1}>thread_title_bar_top<{/if}> outer" id="<{$forum_post_prefix}><{$topic_post.post_id}>">
    <div class="thread_title comTitle floatleft"><{$topic_post.post_title}></div>
    <div class="thread_date floatleft right comDate"><span class="thread_date_caption comDateCaption"><{$smarty.const._MD_NEWBB_POSTEDON}></span><{$topic_post.post_date}></div>
    <div class="thread_post_no _col_end right">
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$topic_post.post_id}>">#<{$topic_post.post_no}></a>
    </div>
    <div class="clear"></div>
</div>
<div class="thread_body forum_table">
    <div class="thread_userinfo odd forum_cell">
        <{if $topic_post.poster.uid gt 0}>
            <div class="thread_poster comUserName"><{$topic_post.poster.link}></div>
            <div class="thread_poster_rank comUserRankText"><{if $topic_post.poster.rank.title !=""}> <{$topic_post.poster.rank.title}><br><img src="<{$xoops_upload_url}>/<{$topic_post.poster.rank.image}>" alt="<{$topic_post.poster.rank.title}>" /><{/if}></div>
            <{if $topic_post.poster.avatar != "blank.gif"}>
                <br>
                <img class="thread_poster_img comUserImg" src="<{$xoops_upload_url}>/<{$topic_post.poster.avatar}>" alt="<{$topic_post.poster.name}>"/>
            <{else}>
                <br>
                <{$anonym_avatar}>
            <{/if}>
            <{if $infobox.show}>
                <br>
                <span class="pointer"
                      onclick="ToggleBlockCategory('toggle-<{$topic_post.post_id}>',(this.firstElementChild || this.children[0]) , '<{$infobox.icon.expand}>', '<{$infobox.icon.collapse}>','<{$smarty.const._MD_NEWBB_HIDEUSERDATA|escape:'quotes'}>','<{$smarty.const._MD_NEWBB_SEEUSERDATA|escape:'quotes'}>','toggle_block','toggle_none');">
                        <{$infobox.displayImage}>
                    </span>
                <div id="toggle-<{$topic_post.post_id}>" class="toggle_<{$infobox.style}>">
                    <div class="thread_poster_stat comUserStat"><span class="thread_poster_stat_caption comUserStatCaption"><{$smarty.const._MD_NEWBB_JOINED}>:</span><br><{$topic_post.poster.regdate}><br><span class="thread_poster_stat_caption comUserStatCaption"><{$smarty.const._US_LASTLOGIN}>
                            :</span><br><{$topic_post.poster.last_login}></div>
                    <{if $topic_post.poster.from}>
                        <div class="thread_poster_stat comUserStat"><span class="thread_poster_stat_caption comUserStatCaption"><{$smarty.const._MD_NEWBB_FROM}></span> <{$topic_post.poster.from}></div>
                    <{/if}>
                    <{if $topic_post.poster.groups}>
                        <div class="thread_poster_stat comUserStat"><span class="thread_poster_stat_caption comUserStatCaption"><{$smarty.const._MD_NEWBB_GROUP}></span>
                            <{foreach item=group from=$topic_post.poster.groups}><br><{$group}><{/foreach}>
                        </div>
                    <{/if}>
                    <div class="thread_poster_stat comUserStat">
                        <span class="thread_poster_stat_caption comUserStatCaption"><{$smarty.const._MD_NEWBB_POSTS}>:</span>
                        <{if $topic_post.poster.posts gt 0}>
                            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?uid=<{$topic_post.poster.uid}>" title="<{$smarty.const._ALL}>" target="_self"><{$topic_post.poster.posts}></a>
                        <{else}>
                            0
                        <{/if}>
                        <{if $topic_post.poster.digests gt 0}>
                            |
                            <span class="thread_poster_stat_caption comUserStatCaption"><{$smarty.const._MD_NEWBB_DIGESTS}>:</span>
                            <{$topic_post.poster.digests}>
                        <{/if}>
                    </div>
                    <{if $topic_post.poster.level}>
                        <div class="thread_poster_stat comUserStat"><{$topic_post.poster.level}></div>
                    <{/if}>
                    <{if $topic_post.poster.status}>
                        <div class="thread_poster_status comUserStatus"><{$topic_post.poster.status}></div>
                    <{/if}>
                </div>
            <{/if}>
        <{else}>
            <div class="thread_poster_rank comUserRankText"><{$anonymous_prefix}><{$topic_post.poster.name}></div>
        <{/if}>
    </div>
    <{else}>
    <div class="thread_title_bar outer" id="<{$forum_post_prefix}>advertise">
        <div class="thread_advertiser"><{$topic_post.poster.link}></div>
    </div>
    <div class="thread_body forum_table">
        <{/if}>
        <div class="thread_body_text even forum_cell">
            <div class="thread_text comText"><{$topic_post.post_text}></div>
            <div class="clear"></div>
            <{if $topic_post.post_attachment}>
                <div class="thread_attach comText"><{$topic_post.post_attachment}></div>
                <div class="clear"></div>
            <{/if}>
            <br>
            <div class="thread_poster_ip floatright">
                <{if $topic_post.poster_ip}>
                    IP:
                    <a href="http://www.whois.sc/<{$topic_post.poster_ip}>" target="_blank"><{$topic_post.poster_ip}></a>
                <{/if}>
            </div>
            <div class="clear"></div>
            <{if $topic_post.post_edit}>
                <br>
                <div class="thread_edit floatright">
                    <{$topic_post.post_edit}>
                </div>
                <div class="clear"></div>
            <{/if}>
            <{if $topic_post.post_signature}>
                <br>
                <div class="thread_signature odd">
                    <{$topic_post.post_signature}>
                </div>
            <{/if}>
        </div>
    </div>
    <div class="thread_buttons foot">
        <div class="icon_left">
          &nbsp;<a href="#threadtop" title="<{$smarty.const._MD_NEWBB_TOP}>"><{$p_up}></a>&nbsp;
            <{if $topic_post.thread_action}>
                <{foreach item=btn from=$topic_post.thread_action}>
                <a href="<{$btn.link}>&amp;post_id=<{$topic_post.post_id}>" alt="<{$btn.name}>" title="<{$btn.name}>" <{if $btn.target}>target="<{$btn.target}>"<{/if}>><{$btn.image}></a>&nbsp;
            <{/foreach}>
            <{/if}>
        </div>
        <div class="icon_right">
            <{if $mode gt 1 && $topic_post.poster.uid gt -1}>
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/action.post.php?post_id=<{$topic_post.post_id}>&amp;op=split&amp;mode=1" target="_self" title="<{$smarty.const._MD_NEWBB_SPLIT_ONE}>"><{$smarty.const._MD_NEWBB_SPLIT_ONE}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/action.post.php?post_id=<{$topic_post.post_id}>&amp;op=split&amp;mode=2" target="_self" title="<{$smarty.const._MD_NEWBB_SPLIT_TREE}>"><{$smarty.const._MD_NEWBB_SPLIT_TREE}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/action.post.php?post_id=<{$topic_post.post_id}>&amp;op=split&amp;mode=3" target="_self" title="<{$smarty.const._MD_NEWBB_SPLIT_ALL}>"><{$smarty.const._MD_NEWBB_SPLIT_ALL}></a>
                |
                <input type="checkbox" name="post_id[]" id="post_id[<{$topic_post.post_id}>]" value="<{$topic_post.post_id}>"/>
            <{else}>
                <{if $topic_post.thread_buttons}>
                    <{foreach item=btn from=$topic_post.thread_buttons}>
                    <a href="<{$btn.link}>&amp;post_id=<{$topic_post.post_id}>" alt="<{$btn.name}>" title="<{$btn.name}>"> <{$btn.image}></a>
                <{/foreach}>
                <{/if}>
            <{/if}>
        </div>
        <div class="clear"></div>
    </div>
