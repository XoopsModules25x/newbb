<!-- a new block template for newbb -->
<!-- all classes can be found in xoops.css -->
<!-- define your desired width here -->
<{assign var=minwidth value=200}> <!-- minimum block minwidth property -->
<{assign var=topicwidth value=100}> <!-- maximum topic width property -->

<{if $block.headers.forum}>
    <{assign var=minwidth value=$minwidth+50}>
    <{assign var=topicwidth value=$topicwidth-20}>
    <{assign var=block_forum value="width20"}>
<{/if}>
<{if $block.headers.views}>
    <{assign var=minwidth value=$minwidth+25}>
    <{assign var=topicwidth value=$topicwidth-10}>
    <{assign var=block_view value="width10"}>
<{/if}>
<{if $block.headers.replies}>
    <{assign var=minwidth value=$minwidth+25}>
    <{assign var=topicwidth value=$topicwidth-10}>
    <{assign var=block_reply value="width10"}>
<{/if}>
<{if $block.headers.lastpost}>
    <{assign var=minwidth value=$minwidth+100}>
    <{assign var=topicwidth value=$topicwidth-20}>
<{/if}>
<{assign var=block_topic value=$topicwidth}> <!-- block topic width after reduction above -->
<div class="outer" style="min-width: <{$minwidth}>px;">
    <div class="head border x-small">
        <div class="<{$block_topic}> floatleft center"><{$block.headers.topic}></div>
        <{if $block.headers.forum}>
            <div class="<{$block_forum}> floatleft center"><{$block.headers.forum}></div>
        <{/if}>
        <{if $block.headers.replies}>
            <div class="<{$block_reply}> floatleft center"><{$block.headers.replies}></div>
        <{/if}>
        <{if $block.headers.views}>
            <div class="<{$block_view}> floatleft center"><{$block.headers.views}></div>
        <{/if}>
        <div style="overflow: hidden;" class="center"><{$block.headers.lastpost}></div>
        <div class="clear"></div>
    </div>
    <!-- start forum topic -->
    <{foreach name=loop item=topic from=$block.topics}>
    <div class="<{cycle values="even,odd"}> border">
        <div class="<{$block_topic}> floatleft left">
            <{if $block.headers.approve}>
                <{if $topic.approve eq 1}><a href="<{$xoops_url}>/modules/newbb/<{$topic.topic_link}>&status=active#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_ADMIN}>"><{$smarty.const._MD_NEWBB_TYPE_ADMIN}></a><{/if}>
                <{if $topic.approve eq 0}><a href="<{$xoops_url}>/modules/newbb/<{$topic.topic_link}>&status=pending#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_PENDING}>"><{$smarty.const._MD_NEWBB_TYPE_PENDING}></a><{/if}>
                <{if $topic.approve eq -1}><a href="<{$xoops_url}>/modules/newbb/<{$topic.topic_link}>&status=deleted#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_DELETED}>"><{$smarty.const._MD_NEWBB_TYPE_DELETED}></a><{/if}>
                <br>
            <{/if}>
            <{if $block.headers.read}><{$topic.topic_folder}><{/if}>
            <{if $block.headers.topic}>
                <a href="<{$xoops_url}>/modules/newbb/<{$topic.topic_link}>" title="<{$topic.topic_excerpt}>">
                    <{if $block.headers.type}><{$topic.topic_title}><{else}><{$topic.topic_title_excerpt}><{/if}>
                </a>
                <{if $block.headers.pagenav}><{$topic.topic_page_jump}><{/if}>
                <br>
            <{/if}>
            <{if $block.headers.attachment}><{$topic.attachment}><{/if}>
            <{if $block.headers.lock}><{$topic.lock}><{/if}>
            <{if $block.headers.sticky}><{$topic.sticky}><{/if}>
            <{if $block.headers.digest}><{$topic.digest}><{/if}>
            <{if $block.headers.poll}><{$topic.poll}><{/if}>
            <{if $block.headers.publish }>
                <br>
                <span class="xx-small">
<{$block.headers.publish}>: <{$topic.topic_time}>
</span>
            <{/if}>
            <{if $topic.votes}>
                <br>
                <span class="xx-small">
<{if $block.headers.votes}><{$block.headers.votes}>: <{$topic.votes}><{/if}>
                    <{if $block.headers.ratings}>&nbsp;<{$topic.rating_img}><{/if}>
</span>
            <{/if}>
            <{if $block.headers.poster}>
                <br>
                <span class="xx-small">
<{$block.headers.poster}>: <{$topic.topic_poster}>
</span>
            <{/if}>
        </div>
        <{if $block.headers.forum}>
            <div class="<{$block_forum}> floatleft left"><{$topic.topic_forum_link}></div>
        <{/if}>
        <{if $block.headers.replies}>
            <div class="<{$block_reply}> floatleft center"><{$topic.topic_replies}></div>
        <{/if}>
        <{if $block.headers.views}>
            <div class="<{$block_view}> floatleft center"><{$topic.topic_views}></div>
        <{/if}>
        <div style="overflow: hidden;" class="right">
            <{if $block.headers.lastpostmsgicon}><{$topic.topic_icon}><{/if}>
            <{if $block.headers.lastposttime}><{$topic.topic_last_posttime}><{/if}>
            <br>
            <{if $block.headers.lastposter}><{$topic.topic_last_poster}><{/if}>
            &nbsp;
            <{if $block.headers.lastpost}><{$topic.topic_page_jump_icon}><{/if}>
        </div>
        <div class="clear"></div>
    </div>
    <{/foreach}>
    <!-- end forum topic -->
</div>
<div class="clear"></div>
<{if $block.indexNav}>
    <!-- a sample of pagenav. you can create your own! -->
    <div class="floatright right">
        <a href="<{$xoops_url}>/modules/newbb/viewpost.php"><{$smarty.const._MB_NEWBB_ALLPOSTS}></a> |
        <a href="<{$xoops_url}>/modules/newbb/list.topic.php"><{$smarty.const._MB_NEWBB_ALLTOPICS}></a> |
        <a href="<{$xoops_url}>/modules/newbb/list.topic.php?status=unread"><{$smarty.const._MD_NEWBB_UNREAD}></a> |
        <{if $block.headers.replies}>
            <a href="<{$xoops_url}>/modules/newbb/list.topic.php?status=unreplied"><{$smarty.const._MD_NEWBB_UNREPLIED}></a>
            |
        <{/if}>
        <{if $block.headers.votes}>
            <a href="<{$xoops_url}>/modules/newbb/list.topic.php?status=voted"><{$smarty.const._MD_NEWBB_VOTED}></a>
            |
        <{/if}>
        <{if $block.headers.poll}>
            <a href="<{$xoops_url}>/modules/newbb/list.topic.php?status=poll"><{$smarty.const._MD_NEWBB_POLL_POLL}></a>
            |
        <{/if}>
        <a href="<{$xoops_url}>/modules/newbb"><{$smarty.const._MB_NEWBB_VSTFRMS}></a>
    </div>
<{/if}>
