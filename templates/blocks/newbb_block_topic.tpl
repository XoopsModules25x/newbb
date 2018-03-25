<table class="outer" cellspacing="1">

    <{if $block.disp_mode == 0}>
        <tr>
            <th class="head" nowrap="nowrap"><{$smarty.const._MB_NEWBB_FORUM}></th>
            <th class="head" nowrap="nowrap"><{$smarty.const._MB_NEWBB_TITLE}></th>
            <th class="head" align="center" nowrap="nowrap"><{$smarty.const._MB_NEWBB_RPLS}></th>
            <th class="head" align="center" nowrap="nowrap"><{$smarty.const._MB_NEWBB_VIEWS}></th>
            <th class="head" align="center" nowrap="nowrap"><{$smarty.const._MB_NEWBB_AUTHOR}></th>
        </tr>
        <{foreach item=topic from=$block.topics}>
        <tr class="<{cycle values="even,odd"}>">
            <!-- irmtfan remove hardcoded html in URLs  -->
            <td><a href="<{$topic.seo_forum_url}>"><{$topic.forum_name}></a></td>
            <td><a href="<{$topic.seo_topic_url}>"><{$topic.title}></a></td>
            <td align="center"><{$topic.replies}></td>
            <td align="center"><{$topic.views}></td>
            <!-- irmtfan hardcode removed align="right" -->
            <td class="align_right"><{$topic.time}><br><{$topic.topic_poster}></td>
        </tr>
    <{/foreach}>

    <{elseif $block.disp_mode == 1}>
        <tr>
            <th class="head" nowrap="nowrap"><{$smarty.const._MB_NEWBB_TOPIC}></th>
            <th class="head" align="center" nowrap="nowrap"><{$smarty.const._MB_NEWBB_AUTHOR}></th>
        </tr>
        <{foreach item=topic from=$block.topics}>
        <tr class="<{cycle values="even,odd"}>">
            <!-- irmtfan remove hardcoded html in URLs  -->
            <td><a href="<{$topic.seo_topic_url}>"><{$topic.title}></a></td>
            <!-- irmtfan hardcode removed align="right" -->
            <td class="align_right"><{$topic.time}><br><{$topic.topic_poster}></td>
        </tr>
    <{/foreach}>

    <{elseif $block.disp_mode == 2}>

        <{foreach item=topic from=$block.topics}>
        <tr class="<{cycle values="even,odd"}>">
            <!-- irmtfan remove hardcoded html in URLs  -->
            <td><a href="<{$topic.seo_topic_url}>"><{$topic.title}></a></td>
        </tr>
    <{/foreach}>

    <{/if}>

</table>

<{if $block.indexNav}>
    <!-- irmtfan hardcode removed style="text-align:right; padding: 5px;" -->
    <div class="pagenav">
        <!-- irmtfan remove hardcoded html in URLs  -->
        <a href="<{$block.seo_top_alltopics}>"><{$smarty.const._MB_NEWBB_ALLTOPICS}></a> |
        <a href="<{$block.seo_top_allforums}>"><{$smarty.const._MB_NEWBB_VSTFRMS}></a>
    </div>
<{/if}>
