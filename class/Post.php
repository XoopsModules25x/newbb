<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: https://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

use Xmf\Highlighter;
use Xmf\IPAddress;
use Xmf\Request;
use XoopsModules\Newbb;

\defined('NEWBB_FUNCTIONS_INI') || require $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class Post
 */
class Post extends \XoopsObject
{
    public $post_id;
    public $topic_id;
    public $forum_id;
    public $post_time;
    //        public $poster_ip ;
    public $poster_ip;
    public $poster_name;
    public $subject;
    public $pid;
    public $dohtml;
    public $dosmiley;
    public $doxcode;
    public $doimage;
    public $dobr;
    public $uid;
    public $icon;
    public $attachsig;
    public $approved;
    public $post_karma;
    public $require_reply;
    public $attachment;
    public $post_text;
    public $post_edit;
    //class Post extends \XoopsObject {
    private $attachmentArray = [];

    public function __construct()
    {
        parent::__construct();

        $this->initVar('post_id', \XOBJ_DTYPE_INT);
        $this->initVar('topic_id', \XOBJ_DTYPE_INT, 0, true);
        $this->initVar('forum_id', \XOBJ_DTYPE_INT, 0, true);
        $this->initVar('post_time', \XOBJ_DTYPE_INT, 0, true);
        //        $this->initVar('poster_ip', XOBJ_DTYPE_INT, 0);
        $this->initVar('poster_ip', \XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('poster_name', \XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('subject', \XOBJ_DTYPE_TXTBOX, '', true);
        $this->initVar('pid', \XOBJ_DTYPE_INT, 0);
        $this->initVar('dohtml', \XOBJ_DTYPE_INT, 0);
        $this->initVar('dosmiley', \XOBJ_DTYPE_INT, 1);
        $this->initVar('doxcode', \XOBJ_DTYPE_INT, 1);
        $this->initVar('doimage', \XOBJ_DTYPE_INT, 1);
        $this->initVar('dobr', \XOBJ_DTYPE_INT, 1);
        $this->initVar('uid', \XOBJ_DTYPE_INT, 1);
        $this->initVar('icon', \XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('attachsig', \XOBJ_DTYPE_INT, 0);
        $this->initVar('approved', \XOBJ_DTYPE_INT, 1);
        $this->initVar('post_karma', \XOBJ_DTYPE_INT, 0);
        $this->initVar('require_reply', \XOBJ_DTYPE_INT, 0);
        $this->initVar('attachment', \XOBJ_DTYPE_TXTAREA, '');
        $this->initVar('post_text', \XOBJ_DTYPE_TXTAREA, '');
        $this->initVar('post_edit', \XOBJ_DTYPE_TXTAREA, '');
    }

    // ////////////////////////////////////////////////////////////////////////////////////
    // attachment functions    TODO: there should be a file/attachment management class

    /**
     * @return array|mixed|null
     */
    public function getAttachment()
    {
        if (\count($this->attachmentArray)) {
            return $this->attachmentArray;
        }
        $attachment = $this->getVar('attachment');
        if (empty($attachment)) {
            $this->attachmentArray = [];
        } else {
            $this->attachmentArray = @\unserialize(\base64_decode($attachment, true));
        }

        return $this->attachmentArray;
    }

    /**
     * @param $attachKey
     * @return bool
     */
    public function incrementDownload($attachKey)
    {
        if (!$attachKey) {
            return false;
        }
        $this->attachmentArray[(string)$attachKey]['numDownload']++;

        return $this->attachmentArray[(string)$attachKey]['numDownload'];
    }

    /**
     * @return bool
     */
    public function saveAttachment()
    {
        $attachmentSave = '';
        if ($this->attachmentArray && \is_array($this->attachmentArray)) {
            $attachmentSave = \base64_encode(\serialize($this->attachmentArray));
        }
        $this->setVar('attachment', $attachmentSave);
        $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . ' SET attachment=' . $GLOBALS['xoopsDB']->quoteString($attachmentSave) . ' WHERE post_id = ' . $this->getVar('post_id');
        if (!$result = $GLOBALS['xoopsDB']->queryF($sql)) {
            //xoops_error($GLOBALS['xoopsDB']->error());
            return false;
        }

        return true;
    }

    /**
     * @param array|null $attachArray
     * @return bool
     */
    public function deleteAttachment($attachArray = null)
    {
        $attachOld = $this->getAttachment();
        if (!\is_array($attachOld) || \count($attachOld) < 1) {
            return true;
        }
        $this->attachmentArray = [];

        if (null === $attachArray) {
            $attachArray = \array_keys($attachOld);
        } // to delete all!
        if (!\is_array($attachArray)) {
            $attachArray = [$attachArray];
        }

        foreach ($attachOld as $key => $attach) {
            if (\in_array($key, $attachArray, true)) {
                $file = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attach['name_saved']);
                @\unlink($file);
                $file = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/thumbs/' . $attach['name_saved']); // delete thumbnails
                @\unlink($file);
                continue;
            }
            $this->attachmentArray[$key] = $attach;
        }
        $attachmentSave = '';
        if ($this->attachmentArray && \is_array($this->attachmentArray)) {
            $attachmentSave = \base64_encode(\serialize($this->attachmentArray));
        }
        $this->setVar('attachment', $attachmentSave);

        return true;
    }

    /**
     * @param string $name_saved
     * @param string $nameDisplay
     * @param string $mimetype
     * @param int    $numDownload
     * @return bool
     */
    public function setAttachment($name_saved = '', $nameDisplay = '', $mimetype = '', $numDownload = 0)
    {
        static $counter = 0;
        $this->attachmentArray = $this->getAttachment();
        if ($name_saved) {
            $key                         = (string)(\time() + $counter++);
            $this->attachmentArray[$key] = [
                'name_saved'  => $name_saved,
                // BigKev73 >  without this change the nameDisplay will always get set to the $name_Saved, so in the forum it will show the on-disk filename instead of the name of the orginal file
                //'nameDisplay' => empty($nameDisplay) ? $nameDisplay : $name_saved,
                'nameDisplay' => !empty($nameDisplay) ? $nameDisplay : $name_saved,
                'mimetype'    => $mimetype,
                // BigKev73 >  without this change the numDownload will always be set to 0
                //'numDownload' => empty($numDownload) ? (int)$numDownload : 0,
                'numDownload' => !empty($numDownload) ? (int)$numDownload : 0,
            ];
        }
        $attachmentSave = null;
        if (\is_array($this->attachmentArray)) {
            $attachmentSave = \base64_encode(\serialize($this->attachmentArray));
        }
        $this->setVar('attachment', $attachmentSave);

        return true;
    }

    /**
     * TODO: refactor
     * @param bool $asSource
     * @return string
     */
    public function displayAttachment($asSource = false)
    {
        global $xoopsModule;

        $post_attachment = '';
        $attachments     = $this->getAttachment();
        if ($attachments && \is_array($attachments)) {
            $iconHandler = \newbbGetIconHandler();
            $mime_path   = $iconHandler->getPath('mime');
            require_once \dirname(__DIR__) . '/include/functions.image.php';
            $image_extensions = ['jpg', 'jpeg', 'gif', 'png', 'bmp']; // need improve !!!
            $post_attachment  .= '<br><strong>' . \_MD_NEWBB_ATTACHMENT . '</strong>:';
            $post_attachment  .= '<br><hr size="1" noshade="noshade" ><br>';
            foreach ($attachments as $key => $att) {
                $file_extension = \ltrim(mb_strrchr($att['name_saved'], '.'), '.');
                $filetype       = $file_extension;
                if (\file_exists($GLOBALS['xoops']->path($mime_path . '/' . $filetype . '.gif'))) {
                    $icon_filetype = XOOPS_URL . '/' . $mime_path . '/' . $filetype . '.gif';
                } else {
                    $icon_filetype = XOOPS_URL . '/' . $mime_path . '/unknown.gif';
                }
                $file_size = @\filesize($GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $att['name_saved']));
                $file_size = \number_format($file_size / 1024, 2) . ' KB';
                $att['nameDisplay'] = $att['nameDisplay']??'';
                if ($GLOBALS['xoopsModuleConfig']['media_allowed']
                    && \in_array(mb_strtolower($file_extension), $image_extensions, true)) {
                    $post_attachment .= '<br><img src="' . $icon_filetype . '" alt="' . $filetype . '" ><strong>&nbsp; ' . $att['nameDisplay'] . '</strong> <small>(' . $file_size . ')</small>';
                    $post_attachment .= '<br>' . \newbbAttachmentImage($att['name_saved']);
                    $isDisplayed     = true;
                } elseif (empty($GLOBALS['xoopsModuleConfig']['show_userattach'])) {
                    $post_attachment .= '<a href="'
                                        . XOOPS_URL
                                        . '/modules/'
                                        . $xoopsModule->getVar('dirname', 'n')
                                        . '/dl_attachment.php?attachid='
                                        . $key
                                        . '&amp;post_id='
                                        . $this->getVar('post_id')
                                        . '"> <img src="'
                                        . $icon_filetype
                                        . '" alt="'
                                        . $filetype
                                        . '" > '
                                        . $att['nameDisplay']
                                        . '</a> '
                                        . \_MD_NEWBB_FILESIZE
                                        . ': '
                                        . $file_size
                                        . '; '
                                        . \_MD_NEWBB_HITS
                                        . ': '
                                        . $att['numDownload'];
                } elseif ($GLOBALS['xoopsUser'] && $GLOBALS['xoopsUser']->uid() > 0
                          && $GLOBALS['xoopsUser']->isactive()) {
                    $post_attachment .= '<a href="'
                                        . XOOPS_URL
                                        . '/modules/'
                                        . $xoopsModule->getVar('dirname', 'n')
                                        . '/dl_attachment.php?attachid='
                                        . $key
                                        . '&amp;post_id='
                                        . $this->getVar('post_id')
                                        . '"> <img src="'
                                        . $icon_filetype
                                        . '" alt="'
                                        . $filetype
                                        . '" > '
                                        . $att['nameDisplay']
                                        . '</a> '
                                        . \_MD_NEWBB_FILESIZE
                                        . ': '
                                        . $file_size
                                        . '; '
                                        . \_MD_NEWBB_HITS
                                        . ': '
                                        . $att['numDownload'];
                } else {
                    $post_attachment .= \_MD_NEWBB_SEENOTGUEST;
                }
                $post_attachment .= '<br>';
            }
        }

        return $post_attachment;
    }

    // attachment functions
    // ////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $poster_name
     * @param string $post_editmsg
     * @return bool
     */
    public function setPostEdit($poster_name = '', $post_editmsg = '')
    {
        $edit_user = '';
        if (empty($GLOBALS['xoopsModuleConfig']['recordedit_timelimit'])
            || $this->getVar('approved') < 1
            || (\time() - $this->getVar('post_time')) < $GLOBALS['xoopsModuleConfig']['recordedit_timelimit'] * 60
        ) {
            return true;
        }
        if (\is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->isActive()) {
            if ($GLOBALS['xoopsModuleConfig']['show_realname'] && $GLOBALS['xoopsUser']->getVar('name')) {
                $edit_user = $GLOBALS['xoopsUser']->getVar('name');
            } else {
                $edit_user = $GLOBALS['xoopsUser']->getVar('uname');
            }
        }
        $post_edit              = [];
        $post_edit['edit_user'] = $edit_user; // (?) The proper way is to store uid instead of name.
        // However, to save queries when displaying, the current way is ok.
        $post_edit['edit_time'] = \time();
        $post_edit['edit_msg']  = $post_editmsg;

        $post_edits = $this->getVar('post_edit');
        if (!empty($post_edits)) {
            $post_edits = \unserialize(\base64_decode($post_edits, true));
        }
        if (!\is_array($post_edits)) {
            $post_edits = [];
        }
        $post_edits[] = $post_edit;
        $post_edit    = \base64_encode(\serialize($post_edits));
        unset($post_edits);
        $this->setVar('post_edit', $post_edit);

        return true;
    }

    /**
     * @return bool|string
     */
    public function displayPostEdit()
    {
        global $myts;

        if (empty($GLOBALS['xoopsModuleConfig']['recordedit_timelimit'])) {
            return false;
        }

        $post_edit  = '';
        $post_edits = $this->getVar('post_edit');
        if (!empty($post_edits)) {
            $post_edits = \unserialize(\base64_decode($post_edits, true));
        }
        if (!isset($post_edits) || !\is_array($post_edits)) {
            $post_edits = [];
        }
        if ($post_edits && \is_array($post_edits)) {
            foreach ($post_edits as $postedit) {
                $edit_time = (int)$postedit['edit_time'];
                $edit_user = $postedit['edit_user'];
                $edit_msg  = !empty($postedit['edit_msg']) ? $postedit['edit_msg'] : '';
                // Start irmtfan add option to do only the latest edit when do_latestedit=0 (Alfred)
                if (empty($GLOBALS['xoopsModuleConfig']['do_latestedit'])) {
                    $post_edit = '';
                }
                // End irmtfan add option to do only the latest edit when do_latestedit=0 (Alfred)
                // START hacked by irmtfan
                // display/save all edit records.
                $post_edit .= \_MD_NEWBB_EDITEDBY . ' ' . $edit_user . ' ' . \_MD_NEWBB_ON . ' ' . \formatTimestamp($edit_time) . '<br>';
                // if reason is not empty
                if ('' !== $edit_msg) {
                    $post_edit .= \_MD_NEWBB_EDITEDMSG . ' ' . $edit_msg . '<br>';
                }
                // START hacked by irmtfan
            }
        }

        return $post_edit;
    }

    /**
     * @return array
     */
    public function &getPostBody()
    {
        global $viewtopic_users;
        $newbbConfig = \newbbLoadConfig();
        require_once \dirname(__DIR__) . '/include/functions.user.php';
        require_once \dirname(__DIR__) . '/include/functions.render.php';

        $uid = \is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
        /** @var KarmaHandler $karmaHandler */
        $karmaHandler = Helper::getInstance()->getHandler('Karma');
        $user_karma   = $karmaHandler->getUserKarma();

        $post               = [];
        $post['attachment'] = false;
        $post_text          = \newbbDisplayTarea($this->vars['post_text']['value'], $this->getVar('dohtml'), $this->getVar('dosmiley'), $this->getVar('doxcode'), $this->getVar('doimage'), $this->getVar('dobr'));
        if ($this->checkIdentity() || \newbbIsAdmin($this->getVar('forum_id'))) {
            $post['text'] = $post_text . '<br>' . $this->displayAttachment();
        } elseif ($newbbConfig['enable_karma'] && $this->getVar('post_karma') > $user_karma) {
            $post['text'] = \sprintf(\_MD_NEWBB_KARMA_REQUIREMENT, $user_karma, $this->getVar('post_karma'));
        } elseif ($newbbConfig['allow_require_reply'] && $this->getVar('require_reply')
                  && (!$uid || !isset($viewtopic_users[$uid]))) {
            $post['text'] = \_MD_NEWBB_REPLY_REQUIREMENT;
        } else {
            $post['text'] = $post_text . '<br>' . $this->displayAttachment();
        }
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');
        $eachposter    = $memberHandler->getUser($this->getVar('uid'));
        if (\is_object($eachposter) && $eachposter->isActive()) {
            if ($newbbConfig['show_realname'] && $eachposter->getVar('name')) {
                $post['author'] = $eachposter->getVar('name');
            } else {
                $post['author'] = $eachposter->getVar('uname');
            }
            unset($eachposter);
        } else {
            $post['author'] = $this->getVar('poster_name') ?: $GLOBALS['xoopsConfig']['anonymous'];
        }

        $post['subject'] = \newbbhtmlspecialchars($this->vars['subject']['value']);

        $post['date'] = $this->getVar('post_time');

        return $post;
    }

    /**
     * @return bool
     */
    public function isTopic()
    {
        return !$this->getVar('pid');
    }

    /**
     * @param string $action_tag
     * @return bool
     */
    public function checkTimelimit($action_tag = 'edit_timelimit')
    {
        $newbbConfig = \newbbLoadConfig();
        if (empty($newbbConfig['edit_timelimit'])) {
            return true;
        }

        return ($this->getVar('post_time') > \time() - $newbbConfig[$action_tag] * 60);
    }

    /**
     * @param int $uid
     * @return bool
     */
    public function checkIdentity($uid = -1)
    {
        $uid = ($uid > -1) ? $uid : (\is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0);
        if ($this->getVar('uid') > 0) {
            $user_ok = ($uid == $this->getVar('uid'));
        } else {
            static $user_ip;
            if (!isset($user_ip)) {
                $user_ip = IPAddress::fromRequest()->asReadable();
            }
            $user_ok = ($user_ip == $this->getVar('poster_ip'));
        }

        return $user_ok;
    }

    // TODO: cleaning up and merge with post hanldings in viewpost.php

    /**
     * @param $isAdmin
     * @return array
     */
    public function showPost($isAdmin)
    {
        global $xoopsModule, $myts;
        global $forumUrl, $forumImage, $forumObject, $online, $viewmode;
        global $viewtopic_users, $viewtopic_posters, $topicObject, $user_karma;
        global $order, $start, $total_posts, $topic_status;
        static $post_NO = 0;
        static $name_anonymous;
        /** @var TopicHandler $topicHandler */
        $topicHandler = Helper::getInstance()->getHandler('Topic');
        if (null === $name_anonymous) {
            $name_anonymous = \htmlspecialchars((string)$GLOBALS['xoopsConfig']['anonymous'], \ENT_QUOTES | \ENT_HTML5);
        }

        require_once \dirname(__DIR__) . '/include/functions.time.php';
        require_once \dirname(__DIR__) . '/include/functions.render.php';

        $post_id  = $this->getVar('post_id');
        $topic_id = $this->getVar('topic_id');
        $forum_id = $this->getVar('forum_id');

        $query_vars              = ['status', 'order', 'start', 'mode', 'viewmode'];
        $query_array             = [];
        $query_array['topic_id'] = "topic_id={$topic_id}";
        foreach ($query_vars as $var) {
            if (Request::getString($var, '', 'GET')) {
                $query_array[$var] = "{$var}=" . Request::getString($var, '', 'GET');
            }
        }
        $page_query = \htmlspecialchars(\implode('&', \array_values($query_array)), \ENT_QUOTES | \ENT_HTML5);

        $uid = \is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;

        ++$post_NO;
        if ('desc' === \mb_strtolower($order)) {
            $post_no = $total_posts - ($start + $post_NO) + 1;
        } else {
            $post_no = $start + $post_NO;
        }

        if ($isAdmin || $this->checkIdentity()) {
            $post_text       = $this->getVar('post_text');
            $post_attachment = $this->displayAttachment();
        } elseif ($GLOBALS['xoopsModuleConfig']['enable_karma'] && $this->getVar('post_karma') > $user_karma) {
            $post_text       = "<div class='karma'>" . \sprintf(\_MD_NEWBB_KARMA_REQUIREMENT, $user_karma, $this->getVar('post_karma')) . '</div>';
            $post_attachment = '';
        } elseif ($GLOBALS['xoopsModuleConfig']['allow_require_reply'] && $this->getVar('require_reply')
                  && (!$uid || !\in_array($uid, $viewtopic_posters, true))) {
            $post_text       = "<div class='karma'>" . \_MD_NEWBB_REPLY_REQUIREMENT . '</div>';
            $post_attachment = '';
        } else {
            $post_text       = $this->getVar('post_text');
            $post_attachment = $this->displayAttachment();
        }

        // Hightlight search words
        $post_title = $this->getVar('subject');
        $keywords   = Request::getString('keywords', '', 'GET');
        if ($keywords) {
            //$keywords   = htmlspecialchars(trim(urldecode(Request::getString('keywords', '', 'GET'))));
            $post_text  = Highlighter::apply($keywords, $post_text, '<mark>', '</mark>');
            $post_title = Highlighter::apply($keywords, $post_title, '<mark>', '</mark>');
        }

        if (isset($viewtopic_users[$this->getVar('uid')])) {
            $poster = $viewtopic_users[$this->getVar('uid')];
        } else {
            $name   = ($post_name = $this->getVar('poster_name')) ? $post_name : $name_anonymous;
            $poster = [
                'poster_uid' => 0,
                'name'       => $name,
                'link'       => $name,
            ];
        }

        $posticon = $this->getVar('icon');
        if ($posticon) {
            $post_image = '<a name="' . $post_id . '"><img src="' . XOOPS_URL . '/images/subject/' . $posticon . '" alt="" ></a>';
        } else {
            $post_image = '<a name="' . $post_id . '"><img src="' . XOOPS_URL . '/images/icons/posticon.gif" alt="" ></a>';
        }

        $thread_buttons = [];
        $mod_buttons    = [];

        if ($isAdmin && $this->getVar('uid') > 0 && ($GLOBALS['xoopsUser'] && $GLOBALS['xoopsUser']->getVar('uid') !== $this->getVar('uid'))) {
            $mod_buttons['bann']['image']    = \newbbDisplayImage('p_bann', \_MD_NEWBB_SUSPEND_MANAGEMENT);
            $mod_buttons['bann']['link']     = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/moderate.php?forum=' . $forum_id . '&amp;uid=' . $this->getVar('uid');
            $mod_buttons['bann']['name']     = \_MD_NEWBB_SUSPEND_MANAGEMENT;
            $thread_buttons['bann']['image'] = \newbbDisplayImage('p_bann', \_MD_NEWBB_SUSPEND_MANAGEMENT);
            $thread_buttons['bann']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/moderate.php?forum=' . $forum_id . '&amp;uid=' . $this->getVar('uid');
            $thread_buttons['bann']['name']  = \_MD_NEWBB_SUSPEND_MANAGEMENT;
        }

        if ($GLOBALS['xoopsModuleConfig']['enable_permcheck']) {
            // /** @var TopicHandler $topicHandler */
            //            $topicHandler =  Newbb\Helper::getInstance()->getHandler('Topic');
            $topic_status = $topicObject->getVar('topic_status');
            if ($topicHandler->getPermission($forum_id, $topic_status, 'edit')) {
                $edit_ok = ($isAdmin || ($this->checkIdentity() && $this->checkTimelimit('edit_timelimit')));

                if ($edit_ok) {
                    $thread_buttons['edit']['image'] = \newbbDisplayImage('p_edit', _EDIT);
                    $thread_buttons['edit']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/edit.php?{$page_query}";
                    $thread_buttons['edit']['name']  = _EDIT;
                    $mod_buttons['edit']['image']    = \newbbDisplayImage('p_edit', _EDIT);
                    $mod_buttons['edit']['link']     = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/edit.php?{$page_query}";
                    $mod_buttons['edit']['name']     = _EDIT;
                }
            }

            if ($topicHandler->getPermission($forum_id, $topic_status, 'delete')) {
                $delete_ok = ($isAdmin || ($this->checkIdentity() && $this->checkTimelimit('delete_timelimit')));

                if ($delete_ok) {
                    $thread_buttons['delete']['image'] = \newbbDisplayImage('p_delete', _DELETE);
                    $thread_buttons['delete']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/delete.php?{$page_query}";
                    $thread_buttons['delete']['name']  = _DELETE;
                    $mod_buttons['delete']['image']    = \newbbDisplayImage('p_delete', _DELETE);
                    $mod_buttons['delete']['link']     = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/delete.php?{$page_query}";
                    $mod_buttons['delete']['name']     = _DELETE;
                }
            }
            if ($topicHandler->getPermission($forum_id, $topic_status, 'reply')) {
                $thread_buttons['reply']['image'] = \newbbDisplayImage('p_reply', \_MD_NEWBB_REPLY);
                $thread_buttons['reply']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/reply.php?{$page_query}";
                $thread_buttons['reply']['name']  = \_MD_NEWBB_REPLY;

                $thread_buttons['quote']['image'] = \newbbDisplayImage('p_quote', \_MD_NEWBB_QUOTE);
                $thread_buttons['quote']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/reply.php?{$page_query}&amp;quotedac=1";
                $thread_buttons['quote']['name']  = \_MD_NEWBB_QUOTE;
            }
        } else {
            $mod_buttons['edit']['image'] = \newbbDisplayImage('p_edit', _EDIT);
            $mod_buttons['edit']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/edit.php?{$page_query}";
            $mod_buttons['edit']['name']  = _EDIT;

            $mod_buttons['delete']['image'] = \newbbDisplayImage('p_delete', _DELETE);
            $mod_buttons['delete']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/delete.php?{$page_query}";
            $mod_buttons['delete']['name']  = _DELETE;

            $thread_buttons['reply']['image'] = \newbbDisplayImage('p_reply', \_MD_NEWBB_REPLY);
            $thread_buttons['reply']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/reply.php?{$page_query}";
            $thread_buttons['reply']['name']  = \_MD_NEWBB_REPLY;
        }

        if (!$isAdmin && $GLOBALS['xoopsModuleConfig']['reportmod_enabled']) {
            $thread_buttons['report']['image'] = \newbbDisplayImage('p_report', \_MD_NEWBB_REPORT);
            $thread_buttons['report']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/report.php?{$page_query}";
            $thread_buttons['report']['name']  = \_MD_NEWBB_REPORT;
        }

        $thread_action = [];
        // irmtfan add pdf permission
        if (\file_exists(XOOPS_ROOT_PATH . '/class/libraries/vendor/tecnickcom/tcpdf/tcpdf.php')
            && $topicHandler->getPermission($forum_id, $topic_status, 'pdf')) {
            $thread_action['pdf']['image']  = \newbbDisplayImage('pdf', \_MD_NEWBB_PDF);
            $thread_action['pdf']['link']   = XOOPS_URL . '/modules/newbb/makepdf.php?type=post&amp;pageid=0';
            $thread_action['pdf']['name']   = \_MD_NEWBB_PDF;
            $thread_action['pdf']['target'] = '_blank';
        }
        // irmtfan add print permission
        if ($topicHandler->getPermission($forum_id, $topic_status, 'print')) {
            $thread_action['print']['image']  = \newbbDisplayImage('printer', \_MD_NEWBB_PRINT);
            $thread_action['print']['link']   = XOOPS_URL . '/modules/newbb/print.php?form=2&amp;forum=' . $forum_id . '&amp;topic_id=' . $topic_id;
            $thread_action['print']['name']   = \_MD_NEWBB_PRINT;
            $thread_action['print']['target'] = '_blank';
        }

        if ($GLOBALS['xoopsModuleConfig']['show_sociallinks']) {
            $full_title  = $this->getVar('subject');
            $clean_title = \preg_replace('/[^A-Za-z0-9-]+/', '+', $this->getVar('subject'));
            // BigKev73 - added to make page scroll to the last post
            $full_link = XOOPS_URL . '/modules/newbb/viewtopic.php?post_id=' . $post_id . '#forumpost=' . $post_id;
            //$full_link   = XOOPS_URL . '/modules/newbb/viewtopic.php?post_id=' . $post_id;

            $thread_action['social_twitter']['image']  = \newbbDisplayImage('twitter', \_MD_NEWBB_SHARE_TWITTER);
            $thread_action['social_twitter']['link']   = 'https://twitter.com/share?text=' . $clean_title . '&amp;url=' . $full_link;
            $thread_action['social_twitter']['name']   = \_MD_NEWBB_SHARE_TWITTER;
            $thread_action['social_twitter']['target'] = '_blank';

            $thread_action['social_facebook']['image']  = \newbbDisplayImage('facebook', \_MD_NEWBB_SHARE_FACEBOOK);
            $thread_action['social_facebook']['link']   = 'https://www.facebook.com/sharer.php?u=' . $full_link;
            $thread_action['social_facebook']['name']   = \_MD_NEWBB_SHARE_FACEBOOK;
            $thread_action['social_facebook']['target'] = '_blank';

            $thread_action['social_gplus']['link']   = 'https://plusone.google.com/_/+1/confirm?hl=en&url=' . $full_link;
            $thread_action['social_gplus']['name']   = \_MD_NEWBB_SHARE_GOOGLEPLUS;
            $thread_action['social_gplus']['target'] = '_blank';

            $thread_action['social_linkedin']['image']  = \newbbDisplayImage('linkedin', \_MD_NEWBB_SHARE_LINKEDIN);
            $thread_action['social_linkedin']['link']   = 'https://www.linkedin.com/shareArticle?mini=true&amp;title=' . $full_title . '&amp;url=' . $full_link;
            $thread_action['social_linkedin']['name']   = \_MD_NEWBB_SHARE_LINKEDIN;
            $thread_action['social_linkedin']['target'] = '_blank';

            $thread_action['social_delicious']['image']  = \newbbDisplayImage('delicious', \_MD_NEWBB_SHARE_DELICIOUS);
            $thread_action['social_delicious']['link']   = 'https://del.icio.us/post?title=' . $full_title . '&amp;url=' . $full_link;
            $thread_action['social_delicious']['name']   = \_MD_NEWBB_SHARE_DELICIOUS;
            $thread_action['social_delicious']['target'] = '_blank';

            $thread_action['social_reddit']['image']  = \newbbDisplayImage('reddit', \_MD_NEWBB_SHARE_REDDIT);
            $thread_action['social_reddit']['link']   = 'https://reddit.com/submit?title=' . $full_title . '&amp;url=' . $full_link;
            $thread_action['social_reddit']['name']   = \_MD_NEWBB_SHARE_REDDIT;
            $thread_action['social_reddit']['target'] = '_blank';
        }

        $post = [
            'post_id'         => $post_id,
            'post_parent_id'  => $this->getVar('pid'),
            'post_date'       => \newbbFormatTimestamp($this->getVar('post_time')),
            'post_image'      => $post_image,
            'post_title'      => $post_title,
            // irmtfan $post_title to add highlight keywords
            'post_text'       => $post_text,
            'post_attachment' => $post_attachment,
            'post_edit'       => $this->displayPostEdit(),
            'post_no'         => $post_no,
            'post_signature'  => $this->getVar('attachsig') ? @$poster['signature'] : '',
            //            'poster_ip'       => ($isAdmin && $GLOBALS['xoopsModuleConfig']['show_ip']) ? long2ip($this->getVar('poster_ip')) : '',
            'poster_ip'       => ($isAdmin
                                  && $GLOBALS['xoopsModuleConfig']['show_ip']) ? $this->getVar('poster_ip') : '',
            'thread_action'   => $thread_action,
            'thread_buttons'  => $thread_buttons,
            'mod_buttons'     => $mod_buttons,
            'poster'          => $poster,
            //Modified by BigKev73
            'post_permalink'  => '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewtopic.php?topic_id=' . $topic_id . '&amp;post_id=' . $post_id . '#forumpost=' . $post_id . '"></a>',

            //'post_permalink'  => '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewtopic.php?post_id=' . $post_id . '"></a>',
        ];

        unset($thread_buttons, $mod_buttons);

        return $post;
    }
}
