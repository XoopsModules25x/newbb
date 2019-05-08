<div class="resultMsg"> <{$search_info}> </div>
<br>
<{if $results}>
    <div class="outer">
        <div class="head align_center">
            <div class="topic_forumname floatleft"><{$smarty.const._MD_NEWBB_FORUMC}></div>
            <div class="topic_name floatleft"><{$smarty.const._MD_NEWBB_SUBJECT}></div>
            <div class="topic_poster floatleft"><{$smarty.const._MD_NEWBB_AUTHOR}></div>
            <div class="_col_end"><{$smarty.const._MD_NEWBB_POSTTIME}></div>
            <div class="clear"></div>
        </div>
        <!-- start search results -->
        <{section name=i loop=$results}>
            <!-- start each result -->
            <div>
                <div class="topic_forumname even floatleft"><a href="<{$results[i].forum_link}>"><{$results[i].forum_name}></a></div>
                <div class="topic_name odd floatleft"><a href="<{$results[i].link}>"><{$results[i].title}></a></div>
                <div class="topic_poster even floatleft align_center"><{$results[i].poster}></a></div>
                <div class="odd _col_end right"><{$results[i].post_time}></div>
                <div class="clear"></div>
            </div>
            <{if $results[i].post_text }>
                <div>
                    <{$results[i].post_text}>
                </div>
            <{/if}>
            <!-- end each result -->
        <{/section}>
        <!-- end search results -->
        <{if $search_next or $search_prev}>
            <div class="head">
                <div class="floatleft left"><{$search_prev}> </div>
                <div class="_col_end right"> <{$search_next}></div>
                <div class="clear"></div>
            </div>
        <{/if}>
    </div>
    <div class="clear"></div>
    <br>
<{elseif $lang_nomatch}>
    <div class="resultMsg"> <{$lang_nomatch}> </div>
    <br>
<{/if}>
