<{foreach item=topic from=$block.topics}>
<div>
    <sdivong>
        <a href="<{$xoops_url}>/modules/newbb/viewtopic.php?forum=<{$topic.forum_id}>&amp;post_id=<{$topic.post_id}>#forumpost<{$topic.post_id}>"><{$topic.title}></a>
    </sdivong>
</div>
<div>
    <a href="<{$xoops_url}>/modules/newbb/viewforum.php?forum=<{$topic.forum_id}>"><{$topic.forum_name}></a>
    <{$topic.topic_poster}> <{$topic.time}>
</div>
<div class="post_text"><{$topic.post_text}></div>
<{/foreach}>
<{if $block.indexNav}>
    <div class="pagenav">
        <a href="<{$xoops_url}>/modules/newbb/viewpost.php"><{$smarty.const._MB_NEWBB_ALLPOSTS}></a> |
        <a href="<{$xoops_url}>/modules/newbb/"><{$smarty.const._MB_NEWBB_VSTFRMS}></a>
    </div>
<{/if}>
