<{if $topic_pollform}>
    <{$topic_pollform}>
<{else}>
<form action="<{$xoops_url}>/modules/<{$xoops_dirname}>/votepolls.php" method="post">
  <input type="hidden" name="topic_id" value="<{$topic_id}>" />
  <input type="hidden" name="forum" value="<{$forum_id}>" />
  <div class="poll outer">
    <div>
        <input type="hidden" name="poll_id" value="<{$poll.pollId}>" />
        <{$poll.question}>
    </div>
    <div class="forum_table">
        <{foreach item=option from=$poll.options}>
            <div class="forum_row">
                <div class="poll_input even"><{$option.input}></div>
                <div class="poll_col2 odd"><{$option.text}></div>
            </div>
        <{/foreach}>
    </div>
    <div class="foot">
        <input type="submit" value="<{$lang_vote}>" />
    </div>
  </div>
</form>
<{/if}>
