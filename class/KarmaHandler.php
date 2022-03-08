<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */
class KarmaHandler
{
    /**
     * @param null $user
     * @return int
     */
    public function getUserKarma($user = null)
    {
        $user = $user ?? $GLOBALS['xoopsUser'];

        return $this->calculateUserKarma($user);
    }

    /**
     * Placeholder for calculating user karma
     * @param \XoopsUser $user
     * @return int
     */
    public function calculateUserKarma($user)
    {
        if (\is_object($user)) {
            $user_karma = $user->getVar('posts') * 50;
        } else {
            $user_karma = 0;
        }

        return $user_karma;
    }

    public function updateUserKarma(): void
    {
    }

    public function writeUserKarma(): void
    {
    }

    public function readUserKarma(): void
    {
    }
}
