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

\defined('NEWBB_FUNCTIONS_INI') || require $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class RateHandler
 */
class RateHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @param \XoopsDatabase|null $db
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        parent::__construct($db, 'newbb_votedata', Rate::class, 'ratingid', '');
    }

    /**
     * @return void
     */
    public function synchronization(): void
    {
        //        return;
    }

    /**
     * clean orphan items from database
     *
     * @param string $table_link
     * @param string $field_link
     * @param string $field_object
     * @return bool   true on success
     */
    public function cleanOrphan($table_link = '', $field_link = '', $field_object = '') //cleanOrphan()
    {
        return parent::cleanOrphan($this->db->prefix('newbb_topics'), 'topic_id');
    }
}
