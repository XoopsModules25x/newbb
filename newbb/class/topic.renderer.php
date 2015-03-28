<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright    The XOOPS Project http://xoops.sf.net
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

/**
 * Topic Renderer
 *
 * @author D.J. (phppp)
 * @copyright copyright &copy; Xoops Project
 * @package module::newbb
 *
 */
class NewbbTopicRenderer
{
    /**
     * reference to an object handler
     */
    public $handler;

    /**
     * reference to moduleConfig
     */
    public $config;

    /**
     * Requested page
     */
    public $page = "list.topic.php";

    /**
     * query variables
     */
    var $args = array("forum", "uid", "lastposter", "type", "status", "mode", "sort", "order", "start", "since");// irmtfan add multi lastposter
    public $vars = array();

    /**
     * For multiple forums
     */
    public $is_multiple = false;

    /**
     * force to parse vars (run against static vars) irmtfan
     */
    public $force = false;

    /**
     * Vistitor's level: 0 - anonymous; 1 - user; 2 - moderator or admin
     */
    public $userlevel = 0;

    /**
     * Current user has no access to current page
     */
    public $noperm = false;

    /**
     *
     */
    public $query = array();

    /**
     * Constructor
     */
    public function NewbbTopicRenderer()
    {
        $this->handler = xoops_getModuleHandler("topic", "newbb");
    }

    /**
     * Access the only instance of this class
     * @return NewbbTopicRenderer
     */
    public function &instance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new NewbbTopicRenderer();
        }

        return $instance;
    }

    public function init()
    {
        $this->noperm = false;
        $this->query  = array();
    }

    /**
     * @param $var
     * @param $val
     * @return array|int|string
     */
    public function setVar($var, $val)
    {
        switch ($var) {
            case "forum":
                if (is_numeric($val)) {
                    $val = intval($val);
                    // START irmtfan - if the forum is array
                } elseif (is_array($val)) {
                    $val = implode("|", $val);
                    //} elseif (!empty($val)) {
                    //	$val = implode("|", array_map("intval", explode(", ", $val)));
                }
                // END irmtfan - if the forum is array
                break;

            case "type":
            case "mode":
            case "order":
            case "start":
            case "since":
                $val = intval($val);
                break;

            case "uid": // irmtfan add multi topic poster
            case "lastposter": // irmtfan add multi lastposter
                break;

            case "status":
                // START irmtfan to accept multiple status
                $val = is_array($val) ? $val : array($val);
                $val = implode(",", $val);
                //$val = (in_array($val, array_keys($this->getStatus( $this->userlevel ))) ) ? $val : "all"; //irmtfan no need to check if status is empty or not
                //if ($val == "all" && !$this->is_multiple) $val = ""; irmtfan commented because it is done in sort
                // END irmtfan to accept multiple status
                break;

            default:
                break;
        }

        return $val;
    }

    /**
     * @param array $vars
     */
    public function setVars($vars = array())
    {
        $this->init();

        foreach ($vars as $var => $val) {
            if (!in_array($var, $this->args)) {
                continue;
            }
            $this->vars[$var] = $this->setVar($var, $val);
        }
        $this->parseVars();
    }

    /**
     * @param null $status
     */
    public function _parseStatus($status = null)
    {
        switch ($status) {
            // START irmtfan to accept multiple status and add more status
            case 'digest':
                $this->query["where"][] = 't.topic_digest = 1';
                break;

            case 'undigest':
                $this->query["where"][] = 't.topic_digest = 0';
                break;

            case 'sticky':
                $this->query["where"][] = 't.topic_sticky = 1';
                break;

            case 'unsticky':
                $this->query["where"][] = 't.topic_sticky = 0';
                break;

            case 'lock':
                $this->query["where"][] = 't.topic_status = 1';
                break;

            case 'unlock':
                $this->query["where"][] = 't.topic_status = 0';
                break;

            case 'poll':
                $this->query["where"][] = 't.topic_haspoll = 1';
                break;

            case 'unpoll':
                $this->query["where"][] = 't.topic_haspoll = 0';
                break;

            case 'voted':
                $this->query["where"][] = 't.votes > 0';
                break;

            case 'unvoted':
                $this->query["where"][] = 't.votes < 1';
                break;

            case 'replied':
                $this->query["where"][] = 't.topic_replies > 0';
                break;

            case 'unreplied':
                $this->query["where"][] = 't.topic_replies < 1';
                break;

            case 'viewed':
                $this->query["where"][] = 't.topic_views > 0';
                break;

            case 'unviewed':
                $this->query["where"][] = 't.topic_views < 1';
                break;

            case 'read':
                // Skip
                if (empty($this->config["read_mode"])) {
                    // Use database
                } elseif ($this->config["read_mode"] == 2) {
                    // START irmtfan use read_uid to find the unread posts when the user is logged in
                    global $xoopsUser;
                    $read_uid = is_object($xoopsUser) ? $xoopsUser->getVar("uid") : 0;
                    if (!empty($read_uid)) {
                        $this->query["join"][]  = 'LEFT JOIN ' . $this->handler->db->prefix('bb_reads_topic') . ' AS r ON r.read_item = t.topic_id AND r.uid = ' . $read_uid . ' ';
                        $this->query["where"][] = 'r.post_id = t.topic_last_post_id';
                    } else {
                    }
                    // END irmtfan change criteria to get from uid p.uid = last post submit user id
                    // User cookie
                } elseif ($this->config["read_mode"] == 1) {
                    // START irmtfan fix read_mode = 1 bugs - for all users (member and anon)
                    $startdate = !empty($this->vars["since"]) ? (time() - newbb_getSinceTime($this->vars["since"])) : 0;
                    if ($lastvisit = max($GLOBALS['last_visit'], $startdate)) {
                        $readmode1query = '';
                        if ($lastvisit > $startdate) {
                            $readmode1query = 'p.post_time < ' . $lastvisit;
                        }
                        $topics         = array();
                        $topic_lastread = newbb_getcookie('LT', true);
                        if (count($topic_lastread) > 0) {
                            foreach ($topic_lastread as $id => $time) {
                                if ($time > $lastvisit) {
                                    $topics[] = $id;
                                }
                            }
                        }
                        if (count($topics) > 0) {
                            $topicquery = ' t.topic_id IN (' . implode(",", $topics) . ')';
                            // because it should be OR
                            $readmode1query = !empty($readmode1query) ? '(' . $readmode1query . ' OR ' . $topicquery . ')' : $topicquery;
                        }
                        $this->query["where"][] = $readmode1query;
                    }
                    // END irmtfan fix read_mode = 1 bugs - for all users (member and anon)
                }
                break;

            case 'unread':
                // Skip
                if (empty($this->config["read_mode"])) {
                    // Use database
                } elseif ($this->config["read_mode"] == 2) {
                    // START irmtfan use read_uid to find the unread posts when the user is logged in
                    global $xoopsUser;
                    $read_uid = is_object($xoopsUser) ? $xoopsUser->getVar("uid") : 0;
                    if (!empty($read_uid)) {
                        $this->query["join"][]  = 'LEFT JOIN ' . $this->handler->db->prefix('bb_reads_topic') . ' AS r ON r.read_item = t.topic_id AND r.uid = ' . $read_uid . ' ';
                        $this->query["where"][] = '(r.read_id IS NULL OR r.post_id < t.topic_last_post_id)';
                    } else {
                    }
                    // END irmtfan change criteria to get from uid p.uid = last post submit user id
                    // User cookie
                } elseif ($this->config["read_mode"] == 1) {
                    // START irmtfan fix read_mode = 1 bugs - for all users (member and anon)
                    $startdate = !empty($this->vars["since"]) ? (time() - newbb_getSinceTime($this->vars["since"])) : 0;
                    if ($lastvisit = max($GLOBALS['last_visit'], $startdate)) {
                        if ($lastvisit > $startdate) {
                            $this->query["where"][] = 'p.post_time > ' . $lastvisit;
                        }
                        $topics         = array();
                        $topic_lastread = newbb_getcookie('LT', true);
                        if (count($topic_lastread) > 0) {
                            foreach ($topic_lastread as $id => $time) {
                                if ($time > $lastvisit) {
                                    $topics[] = $id;
                                }
                            }
                        }
                        if (count($topics) > 0) {
                            $this->query["where"][] = ' t.topic_id NOT IN (' . implode(",", $topics) . ')';
                        }
                    }
                    // END irmtfan fix read_mode = 1 bugs - for all users (member and anon)
                }
                break;

            case 'pending':
                if ($this->userlevel < 2) {
                    $this->noperm = true;
                } else {
                    $this->query["where"][] = 't.approved = 0';
                }
                break;

            case 'deleted':
                if ($this->userlevel < 2) {
                    $this->noperm = true;
                } else {
                    $this->query["where"][] = 't.approved = -1';
                }
                break;

            case 'all': // For viewall.php; do not display sticky topics at first
            case 'active': // same as "all"
                $this->query["where"][] = 't.approved = 1';
                break;

            default: // irmtfan do nothing
                break;
            // END irmtfan to accept multiple status and add more status
        }
    }

    /**
     * @param $var
     * @param $val
     */
    public function parseVar($var, $val)
    {
        switch ($var) {
            case "forum":
                $forum_handler = xoops_getmodulehandler('forum', 'newbb');
                // START irmtfan - get forum Ids by values. parse positive values to forum IDs and negative values to category IDs. value=0 => all valid forums
                // Get accessible forums
                $access_forums = $forum_handler->getIdsByValues(array_map("intval", @explode("|", $val)));
                // Filter specified forums if any
                //if (!empty($val) && $_forums = @explode("|", $val)) {
                //$access_forums = array_intersect($access_forums, array_map("intval", $_forums));
                //}
                $this->vars["forum"] = $this->setVar("forum", $access_forums);
                // END irmtfan - get forum Ids by values. parse positive values to forum IDs and negative values to category IDs. value=0 => all valid forums

                if (empty($access_forums)) {
                    $this->noperm = true;
                    // irmtfan - it just return return the forum_id only when the forum_id is the first allowed forum - no need for this code implode is enough removed.
                    //} elseif (count($access_forums) == 1) {
                    //$this->query["where"][] = "t.forum_id = " . $access_forums[0];
                } else {
                    $this->query["where"][] = "t.forum_id IN ( " . implode(", ", $access_forums) . " )";
                }
                break;

            case "uid": // irmtfan add multi topic poster
                if ($val != -1) {
                    $val                    = implode(",", array_map("intval", explode(",", $val)));
                    $this->query["where"][] = "t.topic_poster IN ( " . $val . " )";
                }
                break;
            case "lastposter": // irmtfan add multi lastposter
                if ($val != -1) {
                    $val                    = implode(",", array_map("intval", explode(",", $val)));
                    $this->query["where"][] = "p.uid IN ( " . $val . " )";
                }
                break;

            case "since":
                if (!empty($val)) {
                    // START irmtfan if unread && read_mode = 1 and last_visit > startdate do not add where query | to accept multiple status
                    $startdate = time() - newbb_getSinceTime($val);
                    if (in_array("unread", explode(",", $this->vars["status"])) && $this->config["read_mode"] == 1 && $GLOBALS['last_visit'] > $startdate) {
                        break;
                    }
                    // irmtfan digest_time | to accept multiple status
                    if (in_array("digest", explode(",", $this->vars["status"]))) {
                        $this->query["where"][] = "t.digest_time > " . $startdate;
                    }
                    // irmtfan - should be >= instead of =
                    $this->query["where"][] = "p.post_time >= " . $startdate;
                    // END irmtfan if unread && read_mode = 1 and last_visit > startdate do not add where query
                }
                break;

            case "type":
                if (!empty($val)) {
                    $this->query["where"][] = "t.type_id = " . $val;
                }
                break;

            case "status":
                // START irmtfan to accept multiple status
                $val = explode(",", $val);
                // irmtfan - add "all" to always parse t.approved = 1
                if (count(array_intersect($val, array("all", "active", "pending", "deleted"))) == 0) {
                    $val[] = "all";
                }
                foreach ($val as $key => $status) {
                    $this->_parseStatus($status);
                }
                // END irmtfan to accept multiple status
                break;

            case "sort":
                if ($sort = $this->getSort($val, "sort")) {
                    $this->query["sort"][] = $sort . (empty($this->vars["order"]) ? " DESC" : " ASC");
                } else { // irmtfan if sort is not in the list
                    $this->query["sort"][] = "t.topic_last_post_id" . (empty($this->vars["order"]) ? " DESC" : " ASC");
                }
                break;

            default:
                break;
        }
    }

    /**
     * @return bool
     */
    public function parseVars()
    {
        static $parsed;
        // irmtfan - force to parse vars (run against static vars)
        if (isset($parsed) && !$this->force) {
            return true;
        }

        if (!isset($this->vars["forum"])) {
            $this->vars["forum"] = null;
        }
        //irmtfan parse status for rendering topic correctly - if empty($_GET(status)) it will show all topics include deleted and pendings. "all" instead of all
        if (!isset($this->vars["status"])) {
            $this->vars["status"] = "all";
        }
        // irmtfan if sort is not set or is empty get a default sort- if empty($_GET(sort)) | if sort=null eg: /list.topic.php?sort=
        if (empty($this->vars["sort"])) {
            $this->vars["sort"] = "lastpost";
        } // use lastpost instead of sticky

        foreach ($this->vars as $var => $val) {
            $this->parseVar($var, $val);
            if (empty($val)) {
                unset($this->vars[$var]);
            }
        }
        $parsed = true;

        return true;
    }

    /**
     * @param null $header
     * @param null $var
     * @return array|null
     */
    public function getSort($header = null, $var = null)
    {
        $headers = array(
            "topic"           => array(
                "title" => _MD_TOPICS,
                "sort"  => "t.topic_title",
            ),
            "forum"           => array(
                "title" => _MD_FORUM,
                "sort"  => "t.forum_id",
            ),
            "poster"          => array(
                "title" => _MD_TOPICPOSTER, /*irmtfan _MD_POSTER to _MD_TOPICPOSTER*/
                "sort"  => "t.topic_poster",
            ),
            "replies"         => array(
                "title" => _MD_REPLIES,
                "sort"  => "t.topic_replies",
            ),
            "views"           => array(
                "title" => _MD_VIEWS,
                "sort"  => "t.topic_views",
            ),
            "lastpost"        => array( // irmtfan show topic_page_jump_icon smarty
                                        "title" => _MD_LASTPOST, /*irmtfan _MD_DATE to _MD_LASTPOSTTIME again change to _MD_LASTPOST*/
                                        "sort"  => "t.topic_last_post_id",
            ),
            // START irmtfan add more sorts
            "lastposttime"    => array( // irmtfan same as lastpost
                                        "title" => _MD_LASTPOSTTIME,
                                        "sort"  => "t.topic_last_post_id",
            ),
            "lastposter"      => array( // irmtfan
                                        "title" => _MD_POSTER,
                                        "sort"  => "p.uid", // poster uid
            ),
            "lastpostmsgicon" => array( // irmtfan
                                        "title" => _MD_MESSAGEICON,
                                        "sort"  => "p.icon", // post message icon
            ),
            "ratings"         => array(
                "title" => _MD_RATINGS,
                "sort"  => "t.rating", // irmtfan t.topic_rating to t.rating
            ),
            "votes"           => array(
                "title" => _MD_VOTES,
                "sort"  => "t.votes",
            ),
            "publish"         => array(
                "title" => _MD_TOPICTIME,
                "sort"  => "t.topic_id",
            ),
            "digest"          => array(
                "title" => _MD_DIGEST,
                "sort"  => "t.digest_time",
            ),
            "sticky"          => array(
                "title" => _MD_STICKY,
                "sort"  => "t.topic_sticky",
            ),
            "lock"            => array(
                "title" => _MD_LOCK,
                "sort"  => "t.topic_status",
            ),
            "poll"            => array(
                "title" => _MD_POLL_POLL,
                "sort"  => "t.poll_id",
            ),
        );
        $types   = $this->getTypes();
        if (!empty($types)) {
            $headers["type"] = array(
                "title" => _MD_NEWBB_TYPE,
                "sort"  => "t.type_id",
            );
        }
        if ($this->userlevel == 2) {
            $headers["approve"] = array(
                "title" => _MD_APPROVE,
                "sort"  => "t.approved",
            );
        }
        // END irmtfan add more sorts
        if (empty($header) && empty($var)) {
            return $headers;
        }
        if (!empty($var) && !empty($header)) {
            return @$headers[$header][$var];
        }
        if (empty($var)) {
            return @$headers[$header];
        }
        $ret = null;
        foreach (array_keys($headers) as $key) {
            $ret[$key] = @$headers[$key][$var];
        }

        return $ret;
    }

    // START irmtfan add Display topic headers function
    /**
     * @param null $header
     * @return array
     */
    public function getHeader($header = null)
    {
        $headersSort = $this->getSort("", "title");
        // additional headers - important: those cannot be in sort anyway
        $headers = array_merge($headersSort, array(
            "attachment" => _MD_TOPICSHASATT, // show attachment smarty
            "read"       => _MD_MARK_UNREAD . '|' . _MD_MARK_READ, // read/unread show topic_folder smarty
            "pagenav"    => _MD_PAGENAV_DISPLAY, // show topic_page_jump smarty - sort by topic_replies?
        ));

        return $this->getFromKeys($headers, $header);
    }

    // END irmtfan add Display topic headers function
    /**
     * @param null $type
     * @param null $status
     * @return array
     */
    public function getStatus($type = null, $status = null)
    {
        $links       = array(
            //""			=> "", /* irmtfan remove empty array */
            "all"       => _ALL,
            "digest"    => _MD_DIGEST,
            "undigest"  => _MD_UNDIGEST, // irmtfan add
            "sticky"    => _MD_STICKY, // irmtfan add
            "unsticky"  => _MD_UNSTICKY, // irmtfan add
            "lock"      => _MD_LOCK, // irmtfan add
            "unlock"    => _MD_UNLOCK, // irmtfan add
            "poll"      => _MD_TOPICHASPOLL, // irmtfan add
            "unpoll"    => _MD_TOPICHASNOTPOLL, // irmtfan add
            "voted"     => _MD_VOTED, // irmtfan add
            "unvoted"   => _MD_UNVOTED, // irmtfan add
            "viewed"    => _MD_VIEWED, // irmtfan add
            "unviewed"  => _MD_UNVIEWED, // irmtfan add
            "replied"   => _MD_REPLIED, // irmtfan add
            "unreplied" => _MD_UNREPLIED,
            "read"      => _MD_READ, // irmtfan add
            "unread"    => _MD_UNREAD,
        );
        $links_admin = array(
            //" "			=> "", /* irmtfan remove empty array */
            "active"  => _MD_TYPE_ADMIN,
            "pending" => _MD_TYPE_PENDING,
            "deleted" => _MD_TYPE_DELETED,
        );

        // all status, for admin
        if ($type > 1) {
            $links = array_merge($links, $links_admin);// irmtfan to accept multiple status
        }

        return $this->getFromKeys($links, $status); // irmtfan to accept multiple status
    }

    /**
     * @param Smarty $xoopsTpl
     */
    public function buildSelection(Smarty &$xoopsTpl)
    {
        $selection         = array("action" => $this->page);
        $selection["vars"] = $this->vars;
        // irmtfan need vars for other selections
        //$selection["vars"]["order"] = $selection["vars"]["since"] = null;
        // START irmtfan add forum selection box
        mod_loadFunctions("forum", "newbb");
        $forum_selected     = empty($this->vars["forum"]) ? null : explode("|", @$this->vars["forum"]);
        $selection["forum"] = '<select name="forum[]" multiple="multiple">';
        $selection["forum"] .= '<option value="0">' . _MD_ALL . '</option>';
        $selection["forum"] .= newbb_forumSelectBox($forum_selected);
        $selection["forum"] .= '</select>';
        // END irmtfan add forum selection box

        $sort_selected     = $this->vars["sort"]; // irmtfan no need to check
        $sorts             = $this->getSort("", "title");
        $selection["sort"] = "<select name='sort'>";
        foreach ($sorts as $sort => $title) {
            $selection["sort"] .= "<option value='" . $sort . "' " . (($sort == $sort_selected) ? " selected='selected'" : "") . ">" . $title . "</option>";
        }
        $selection["sort"] .= "</select>";

        $selection["order"] = "<select name='order'>";
        $selection["order"] .= "<option value='0' " . (empty($this->vars["order"]) ? " selected='selected'" : "") . ">" . _DESCENDING . "</option>";
        $selection["order"] .= "<option value='1' " . (!empty($this->vars["order"]) ? " selected='selected'" : "") . ">" . _ASCENDING . "</option>";
        $selection["order"] .= "</select>";

        $since              = isset($this->vars['since']) ? $this->vars['since'] : $this->config["since_default"];
        $selection["since"] = newbb_sinceSelectBox($since);

        $xoopsTpl->assign_by_ref('selection', $selection);
    }

    /**
     * @param Smarty $xoopsTpl
     */
    public function buildSearch(Smarty &$xoopsTpl)
    {
        $search             = array();
        $search["forum"]    = @$this->vars["forum"];
        $search["since"]    = @$this->vars["since"];
        $search["searchin"] = "both";

        $xoopsTpl->assign_by_ref('search', $search);
    }

    /**
     * @param Smarty $xoopsTpl
     */
    public function buildHeaders(Smarty &$xoopsTpl)
    {
        $args = array();
        foreach ($this->vars as $var => $val) {
            if ($var == "sort" || $var == "order") {
                continue;
            }
            $args[] = "{$var}={$val}";
        }

        $headers = $this->getSort("", "title");
        foreach ($headers as $header => $title) {
            $_args = array("sort={$header}");
            if (@$this->vars["sort"] == $header) {
                $_args[] = "order=" . ((@$this->vars["order"] + 1) % 2);
            }
            $headers_data[$header]["title"] = $title;
            $headers_data[$header]["link"]  = $this->page . "?" . implode("&amp;", array_merge($args, $_args));
        }
        $xoopsTpl->assign_by_ref('headers', $headers_data);
    }

    /**
     * @param Smarty $xoopsTpl
     */
    public function buildFilters(Smarty &$xoopsTpl)
    {
        $args = array();
        foreach ($this->vars as $var => $val) {
            if ($var == "status") {
                continue;
            }
            $args[] = "{$var}={$val}";
        }

        $links = $this->getStatus($this->userlevel);

        $status = array();
        foreach ($links as $link => $title) {
            $_args                  = array("status={$link}");
            $status[$link]["title"] = $title;
            $status[$link]["link"]  = $this->page . "?" . implode("&amp;", array_merge($args, $_args));
        }
        $xoopsTpl->assign_by_ref('filters', $status);
    }

    /**
     * @param null $type_id
     * @return mixed
     */
    public function getTypes($type_id = null)
    {
        static $types;
        if (!isset($types)) {
            $type_handler =& xoops_getmodulehandler('type', 'newbb');
            $types        = $type_handler->getByForum(explode("|", @$this->vars["forum"]));
        }

        if (empty($type_id)) {
            return $types;
        }

        return @$types[$type_id];
    }

    /**
     * @param Smarty $xoopsTpl
     * @return bool
     */
    public function buildTypes(Smarty &$xoopsTpl)
    {
        if (!$types = $this->getTypes()) {
            return true;
        }

        $args = array();
        foreach ($this->vars as $var => $val) {
            if ($var == "type") {
                continue;
            }
            $args[] = "{$var}={$val}";
        }

        foreach ($types as $id => $type) {
            $_args                = array("type={$id}");
            $status[$id]["title"] = $type["type_name"];
            $status[$id]["link"]  = $this->page . "?" . implode("&amp;", array_merge($args, $_args));
        }
        $xoopsTpl->assign_by_ref('types', $status);
    }

    /**
     * @param Smarty $xoopsTpl
     * @return bool
     */
    public function buildCurrent(Smarty &$xoopsTpl)
    {
        if (empty($this->vars["status"]) && !$this->is_multiple) {
            return true;
        }

        $args = array();
        foreach ($this->vars as $var => $val) {
            $args[] = "{$var}={$val}";
        }

        $status          = array();
        $status["title"] = implode(",", $this->getStatus($this->userlevel, $this->vars["status"])); // irmtfan to accept multiple status
        //$status["link"] = $this->page.(empty($this->vars["status"]) ? "" : "?status=".$this->vars["status"]);
        $status["link"] = $this->page . (empty($args) ? "" : "?" . implode("&amp;", $args));

        $xoopsTpl->assign_by_ref('current', $status);
    }

    /**
     * @param Smarty $xoopsTpl
     */
    public function buildPagenav(Smarty &$xoopsTpl)
    {
        $count_topic = $this->getCount();
        if ($count_topic > $this->config['topics_per_page']) {
            $args = array();
            foreach ($this->vars as $var => $val) {
                if ($var == "start") {
                    continue;
                }
                $args[] = "{$var}={$val}";
            }
            require_once $GLOBALS['xoops']->path('class/pagenav.php');
            $nav = new XoopsPageNav($count_topic, $this->config['topics_per_page'], @$this->vars["start"], "start", implode("&amp;", $args));
            if (isset($xoopsModuleConfig['do_rewrite'])) {
                $nav->url = formatURL($_SERVER['SERVER_NAME']) . " /" . $nav->url;
            }
            if ($this->config['pagenav_display'] == 'select') {
                $navi = $nav->renderSelect();
            } elseif ($this->config['pagenav_display'] == 'bild') {
                $navi = $nav->renderImageNav(4);
            } else {
                $navi = $nav->renderNav(4);
            }
            $xoopsTpl->assign('pagenav', $navi);
        } else {
            $xoopsTpl->assign('pagenav', '');
        }
    }

    /**
     * @return int
     */
    public function getCount()
    {
        if ($this->noperm) {
            return 0;
        }

        $selects = array();
        $froms   = array();
        $joins   = array();
        $wheres  = array();

        // topic fields
        $selects[] = 'COUNT(*)';

        $froms[]  = $this->handler->db->prefix("bb_topics") . ' AS t ';
        $joins[]  = 'LEFT JOIN ' . $this->handler->db->prefix('bb_posts') . ' AS p ON p.post_id = t.topic_last_post_id';
        $wheres[] = "1 = 1";

        $sql = '	SELECT ' . implode(", ", $selects) .
               ' 	FROM ' . implode(", ", $froms) .
               '		' . implode(" ", $joins) .
               (!empty($this->query["join"]) ? '		' . implode(" ", $this->query["join"]) : '') . // irmtfan bug fix: Undefined index: join when post_excerpt = 0
               ' 	WHERE ' . implode(" AND ", $wheres) .
               '		AND ' . @implode(" AND ", @$this->query["where"]);

        if (!$result = $this->handler->db->query($sql)) {
            return 0;
        }
        list($count) = $this->handler->db->fetchRow($result);

        return $count;
    }

    /**
     * @param Smarty $xoopsTpl
     * @return array|void
     */
    public function renderTopics(Smarty $xoopsTpl = null)
    {
        $myts = MyTextSanitizer::getInstance(); // irmtfan Instanciate

        $ret = array();
        //$this->parseVars();

        if ($this->noperm) {
            if (is_object($xoopsTpl)) {
                $xoopsTpl->assign_by_ref("topics", $ret);

                return;
            }

            return $ret;
        }

        $selects = array();
        $froms   = array();
        $joins   = array();
        $wheres  = array();

        // topic fields
        $selects[] = 't.*';
        // post fields
        $selects[] = 'p.post_time as last_post_time, p.poster_name as last_poster_name, p.icon, p.post_id, p.uid';

        $froms[]  = $this->handler->db->prefix("bb_topics") . ' AS t ';
        $joins[]  = 'LEFT JOIN ' . $this->handler->db->prefix('bb_posts') . ' AS p ON p.post_id = t.topic_last_post_id';
        $wheres[] = "1 = 1";

        if (!empty($this->config['post_excerpt'])) {
            $selects[]             = 'p.post_karma, p.require_reply, pt.post_text';
            $this->query["join"][] = 'LEFT JOIN ' . $this->handler->db->prefix('bb_posts_text') . ' AS pt ON pt.post_id = t.topic_last_post_id';
        }
        //if (empty($this->query["sort"])) $this->query["sort"][] = 't.topic_last_post_id DESC'; // irmtfan commented no need

        $sql = '	SELECT ' . implode(", ", $selects) .
               ' 	FROM ' . implode(", ", $froms) .
               '		' . implode(" ", $joins) .
               (!empty($this->query["join"]) ? '		' . implode(" ", $this->query["join"]) : '') . // irmtfan bug fix: Undefined index join when post_excerpt = 0
               ' 	WHERE ' . implode(" AND ", $wheres) .
               '		AND ' . @implode(" AND ", @$this->query["where"]) .
               ' 	ORDER BY ' . implode(", ", $this->query["sort"]);

        if (!$result = $this->handler->db->query($sql, $this->config['topics_per_page'], @$this->vars["start"])) {
            if (is_object($xoopsTpl)) {
                $xoopsTpl->assign_by_ref("topics", $ret);

                return;
            }

            return $ret;
        }

        mod_loadFunctions("render", "newbb");
        mod_loadFunctions("session", "newbb");
        mod_loadFunctions("time", "newbb");
        mod_loadFunctions("read", "newbb");
        mod_loadFunctions("topic", "newbb");

        $sticky    = 0;
        $topics    = array();
        $posters   = array();
        $reads     = array();
        $types     = array();
        $forums    = array();
        $anonymous = $myts->htmlSpecialChars($GLOBALS["xoopsConfig"]['anonymous']);

        while ($myrow = $this->handler->db->fetchArray($result)) {
            if ($myrow['topic_sticky']) {
                ++$sticky;
            }

            // ------------------------------------------------------
            // START irmtfan remove topic_icon hardcode smarty
            // topic_icon: just regular topic_icon
            if (!empty($myrow['icon'])) {
                $topic_icon = '<img align="middle" src="' . XOOPS_URL . '/images/subject/' . htmlspecialchars($myrow['icon']) . '" alt="" />';
            } else {
                $topic_icon = '<img align="middle" src="' . XOOPS_URL . '/images/icons/no_posticon.gif" alt="" />';
            }
            // END irmtfan remove topic_icon hardcode smarty

            // ------------------------------------------------------
            // rating_img
            $rating = number_format($myrow['rating'] / 2, 0);
            // irmtfan - add alt key for rating
            if ($rating < 1) {
                $rating_img = newbb_displayImage('blank');
            } else {
                $rating_img = newbb_displayImage('rate' . $rating, constant('_MD_RATE' . $rating));
            }

            // ------------------------------------------------------
            // topic_page_jump
            $topic_page_jump      = '';
            $topic_page_jump_icon = '';
            $totalpages           = ceil(($myrow['topic_replies'] + 1) / $this->config['posts_per_page']);
            if ($totalpages > 1) {
                $topic_page_jump .= '&nbsp;&nbsp;';
                $append = false;
                for ($i = 1; $i <= $totalpages; ++$i) {
                    if ($i > 3 && $i < $totalpages) {
                        if (!$append) {
                            $topic_page_jump .= "...";
                            $append = true;
                        }
                    } else {
                        $topic_page_jump .= '[<a href="' . XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . $myrow['topic_id'] . '&amp;start=' . (($i - 1) * $this->config['posts_per_page']) . '">' . $i . '</a>]';
                        // irmtfan remove here and move
                        //$topic_page_jump_icon = "<a href='" . XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id=" . $myrow['topic_id'] . "&amp;start=" . (($i - 1) * $this->config['posts_per_page']) . "" . "'>" . newbb_displayImage('document',_MD_NEWBB_GOTOLASTPOST) . "</a>";
                    }
                }
            }
            // irmtfan - move here for both topics with and without pages - change topic_id to post_id
            $topic_page_jump_icon = "<a href='" . XOOPS_URL . "/modules/newbb/viewtopic.php?post_id=" . $myrow['topic_last_post_id'] . "" . "'>" . newbb_displayImage('lastposticon', _MD_NEWBB_GOTOLASTPOST) . "</a>";

            // ------------------------------------------------------
            // => topic array

            $topic_title = $myts->htmlSpecialChars($myrow['topic_title']);
            // irmtfan use topic_title_excerpt for block topic title length
            if (!empty($this->config['topic_title_excerpt'])) {
                $topic_title_excerpt = xoops_substr($topic_title, 0, $this->config['topic_title_excerpt']);
            } else {
                $topic_title_excerpt = $topic_title;
            }
            // irmtfan hardcode class commented
            //if ($myrow['topic_digest']) {
            //   $topic_title = "<span class='digest'>" . $topic_title . "</span>";
            //}

            if (empty($this->config["post_excerpt"])) {
                $topic_excerpt = "";
            } elseif (($myrow['post_karma'] > 0 || $myrow['require_reply'] > 0) && !newbb_isAdmin($myrow['forum_id'])) {
                $topic_excerpt = "";
            } else {
                $topic_excerpt = xoops_substr(newbb_html2text($myts->displayTarea($myrow['post_text'])), 0, $this->config["post_excerpt"]);
                $topic_excerpt = str_replace("[", "&#91;", $myts->htmlSpecialChars($topic_excerpt));
            }

            $topics[$myrow['topic_id']] = array(
                'topic_id'               => $myrow['topic_id'],
                'topic_icon'             => $topic_icon,
                'type_id'                => $myrow['type_id'],
                'topic_title_excerpt'    => $topic_title_excerpt, //irmtfan use topic_title_excerpt
                //'topic_link'	=> XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . $myrow['topic_id'], // . '&amp;forum=' . $myrow['forum_id'], // irmtfan comment
                'topic_link'             => 'viewtopic.php?topic_id=' . $myrow['topic_id'], // irmtfan remove hardcode link
                'rating_img'             => $rating_img,
                'votes'                  => $myrow['votes'], //irmtfan added
                'topic_page_jump'        => $topic_page_jump,
                'topic_page_jump_icon'   => $topic_page_jump_icon,
                'topic_replies'          => $myrow['topic_replies'],
                'topic_poster_uid'       => $myrow['topic_poster'],
                'topic_poster_name'      => !empty($myrow['poster_name']) ? $myts->htmlSpecialChars($myrow['poster_name']) : $anonymous,
                'topic_views'            => $myrow['topic_views'],
                'topic_time'             => newbb_formatTimestamp($myrow['topic_time']),
                'topic_last_post_id'     => $myrow['topic_last_post_id'], //irmtfan added
                'topic_last_posttime'    => newbb_formatTimestamp($myrow['last_post_time']),
                'topic_last_poster_uid'  => $myrow['uid'],
                'topic_last_poster_name' => !empty($myrow['last_poster_name']) ? $myts->htmlSpecialChars($myrow['last_poster_name']) : $anonymous,
                'topic_forum'            => $myrow['forum_id'],
                'topic_excerpt'          => $topic_excerpt,
                'sticky'                 => $myrow['topic_sticky'] ? newbb_displayImage('topic_sticky', _MD_TOPICSTICKY) : '', // irmtfan bug fixed
                'lock'                   => $myrow['topic_status'] ? newbb_displayImage('topic_locked', _MD_TOPICLOCK) : '', //irmtfan added
                'digest'                 => $myrow['topic_digest'] ? newbb_displayImage('topic_digest', _MD_TOPICDIGEST) : '', //irmtfan added
                'poll'                   => $myrow['topic_haspoll'] ? newbb_displayImage('poll', _MD_TOPICHASPOLL) : '', //irmtfan added
                'approve'                => $myrow['approved'], //irmtfan added
            );

            /* users */
            $posters[$myrow['topic_poster']] = 1;
            $posters[$myrow['uid']]          = 1;
            // reads
            if (!empty($this->config["read_mode"])) {
                $reads[$myrow['topic_id']] = ($this->config["read_mode"] == 1) ? $myrow['last_post_time'] : $myrow["topic_last_post_id"];
            }
            // types
            if (!empty($myrow['type_id'])) {
                //$types[$myrow['type_id']] = 1;
            }
            // forums
            $forums[$myrow['forum_id']] = 1;
        }
        $posters_name = newbb_getUnameFromIds(array_keys($posters), $this->config['show_realname'], true);
        $topic_isRead = newbb_isRead("topic", $reads);
        /*
        $type_list = array();
        if (count($types) > 0) {
            $type_handler =& xoops_getmodulehandler('type', 'newbb');
            $type_list = $type_handler->getAll(new Criteria("type_id", "(".implode(", ", array_keys($types)).")", "IN"), null, false);
        }
        */
        $type_list     = $this->getTypes();
        $forum_handler =& xoops_getmodulehandler('forum', 'newbb');

        if (count($forums) > 0) {
            $forum_list = $forum_handler->getAll(new Criteria("forum_id", "(" . implode(", ", array_keys($forums)) . ")", "IN"), array("forum_name", "hot_threshold"), false);
        } else {
            $forum_list = $forum_handler->getAll();
        }

        foreach (array_keys($topics) as $id) {
            $topics[$id]['topic_read']       = empty($topic_isRead[$id]) ? 0 : 1; // add topic-read/topic-new smarty variable
            $topics[$id]["topic_forum_link"] = '<a href="' . XOOPS_URL . '/modules/newbb/viewforum.php?forum=' . $topics[$id]["topic_forum"] . '">' . $forum_list[$topics[$id]["topic_forum"]]["forum_name"] . '</a>';

            //irmtfan use topic_title_excerpt -- add else
            if (!empty($topics[$id]["type_id"]) && isset($type_list[$topics[$id]["type_id"]])) {
                $topics[$id]["topic_title"] = getTopicTitle($topics[$id]["topic_title_excerpt"], $type_list[$topics[$id]["type_id"]]["type_name"], $type_list[$topics[$id]["type_id"]]["type_color"]);
            } else {
                $topics[$id]["topic_title"] = $topics[$id]["topic_title_excerpt"];
            }
            $topics[$id]["topic_poster"]      = !empty($posters_name[$topics[$id]["topic_poster_uid"]])
                ? $posters_name[$topics[$id]["topic_poster_uid"]]
                : $topics[$id]["topic_poster_name"];
            $topics[$id]["topic_last_poster"] = !empty($posters_name[$topics[$id]["topic_last_poster_uid"]])
                ? $posters_name[$topics[$id]["topic_last_poster_uid"]]
                : $topics[$id]["topic_last_poster_name"];
            // ------------------------------------------------------
            // START irmtfan remove hardcodes from topic_folder smarty
            // topic_folder: priority: newhot -> hot/new -> regular
            //list($topic_status, $topic_digest, $topic_replies) = $topics[$id]["stats"]; irmtfan
            // START irmtfan - add topic_folder_text for alt
            //if ($topics[$id]["lock"] == 1) {
            //    $topic_folder = 'topic_locked';
            //    $topic_folder_text = _MD_TOPICLOCKED;
            //} else {
            //if ($topic_digest) {
            //    $topic_folder = 'topic_digest';
            //	$topic_folder_text = _MD_TOPICDIGEST;
            if ($topics[$id]["topic_replies"] >= $forum_list[$topics[$id]["topic_forum"]]["hot_threshold"]) {
                $topic_folder      = empty($topic_isRead[$id]) ? 'topic_hot_new' : 'topic_hot';
                $topic_folder_text = empty($topic_isRead[$id]) ? _MD_MORETHAN : _MD_MORETHAN2;
            } else {
                $topic_folder      = empty($topic_isRead[$id]) ? 'topic_new' : 'topic';
                $topic_folder_text = empty($topic_isRead[$id]) ? _MD_NEWPOSTS : _MD_NONEWPOSTS;
            }
            //}
            // END irmtfan remove hardcodes from topic_folder smarty
            $topics[$id]['topic_folder'] = newbb_displayImage($topic_folder, $topic_folder_text);
            // END irmtfan - add topic_folder_text for alt

            unset($topics[$id]["topic_poster_name"], $topics[$id]["topic_last_poster_name"]);// irmtfan remove $topics[$id]["stats"] because it is not exist now
        }

        if (count($topics) > 0) {
            $sql = " SELECT DISTINCT topic_id FROM " . $this->handler->db->prefix("bb_posts") .
                   " WHERE attachment != ''" .
                   " AND topic_id IN (" . implode(',', array_keys($topics)) . ")";
            if ($result = $this->handler->db->query($sql)) {
                while (list($topic_id) = $this->handler->db->fetchRow($result)) {
                    $topics[$topic_id]['attachment'] = '&nbsp;' . newbb_displayImage('attachment', _MD_TOPICSHASATT);
                }
            }
        }

        if (is_object($xoopsTpl)) {
            $xoopsTpl->assign_by_ref("sticky", $sticky);
            $xoopsTpl->assign_by_ref("topics", $topics);

            return;
        }

        return array($topics, $sticky);
    }

    // START irmtfan to create an array from selected keys of an array
    /**
     * @param $array
     * @param null $keys
     * @return array
     */
    public function getFromKeys($array, $keys = null)
    {
        if (empty($keys)) {
            return $array;
        } // all keys
        $keyarr = is_string($keys) ? explode(",", $keys) : $keys;
        $keyarr = array_intersect(array_keys($array), $keyarr); // keys should be in array
        $ret    = array();
        foreach ($keyarr as $key) {
            $ret[$key] = $array[$key];
        }

        return $ret;
    }
    // END irmtfan to create an array from selected keys of an array
}
