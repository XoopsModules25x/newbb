<{foreach item=topic from=$block.topics}>
<div><strong><a href="<{$xoops_url}>/modules/newbb/viewtopic.php?forum=<{$topic.forum_id}>&amp;post_id=<{$topic.post_id}>#forumpost<{$topic.post_id}>"><{$topic.title}></a></strong></div>
<div>
<a href="<{$xoops_url}>/modules/newbb/viewforum.php?forum=<{$topic.forum_id}>"><{$topic.forum_name}></a>
<{$topic.topic_poster}> <{$topic.time}>
</div>
<div style="padding: 5px 0 10px 0;"><{$topic.post_text}></div>
<{/foreach}>

<{if $block.indexNav}>
<!-- irmtfan hardcode removed style="text-align:right; padding: 5px;" -->
<div class="pagenav">
<a href="<{$xoops_url}>/modules/newbb/viewpost.php"><{$smarty.const._MB_NEWBB_ALLPOSTS}></a> |
<a href="<{$xoops_url}>/modules/newbb/"><{$smarty.const._MB_NEWBB_VSTFRMS}></a>
</div>
<{/if}>
