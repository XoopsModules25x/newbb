<?php

/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright    XOOPS Project (http://xoops.org)
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
        $user = (null === $user) ? $GLOBALS["xoopsUser"] : $user;

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
