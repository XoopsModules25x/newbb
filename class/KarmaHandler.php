<?php namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */
class KarmaHandler
{
    public $user;

    /**
     * @param  null $user
     * @return int
     */
    public function getUserKarma($user = null)
    {
        $user = (null === $user) ? $GLOBALS['xoopsUser'] : $user;

        return $this->calculateUserKarma($user);
    }

    /**
     * Placeholder for calculating user karma
     * @param \XoopsUser $user
     * @return int
     */
    public function calculateUserKarma($user)
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
