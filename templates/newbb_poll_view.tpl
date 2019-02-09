<{if $topic_pollform}>
    <{$topic_pollform}>
<{else}>
    <form action="<{$xoops_url}>/modules/<{$xoops_dirname}>/votepolls.php" method="post">
        <input type="hidden" name="topic_id" value="<{$topic_id}>"/>
        <input type="hidden" name="forum" value="<{$forum_id}>"/>
        <table class="outer width100" cellspacing="1">
            <tr>
                <th class="center" colspan="2"><input type="hidden" name="poll_id" value="<{$poll.pollId}>"/>
                    <{$poll.question}></th>
            </tr>
            <{foreach item=option from=$poll.options}>
            <tr>
                <{*-- irmtfan hardcode removed align="left" --*}>
                <td class="even align_left" width="2%"><{$option.input}></td>
                <td class="odd align_left" width="98%"><{$option.text}></td>
            </tr>
            <{/foreach}>
            <tr>
                <td class="foot center" colspan="2"><input type="submit" value="<{$lang_vote}>"/></td>
            </tr>
        </table>
    </form>
<{/if}>
