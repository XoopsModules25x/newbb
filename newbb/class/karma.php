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
class NewbbKarmaHandler
{
    public $user;

    /**
     * @param null $user
     * @return int
     */
    public function getUserKarma($user = null)
    {
        $user = is_null($user) ? $GLOBALS["xoopsUser"] : $user;

        return NewbbKarmaHandler::calUserKarma($user);
    }

    /**
     * Placeholder for calcuating user karma
     * @param $user
     * @return int
     */
    public function calUserKarma($user)
    {
        if (!is_object($user)) {
            $user_karma = 0;
        } else {
            $user_karma = $user->getVar('posts') * 50;
        }

        return $user_karma;
    }

    public function updateUserKarma()
    {
    }

    public function writeUserKarma()
    {
    }

    public function readUserKarma()
    {
    }
}
