<?php
// $Id: rate.php 62 2012-08-17 10:15:26Z alfred $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: http://xoopsforge.com, http://xoops.org.cn                          //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //
 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

defined("NEWBB_FUNCTIONS_INI") || include XOOPS_ROOT_PATH.'/modules/newbb/include/functions.ini.php';
newbb_load_object();

class Nrate extends ArtObject {
    function Nrate()
    {
	    $this->ArtObject("bb_votedata");
        $this->initVar('ratingid', XOBJ_DTYPE_INT);
        $this->initVar('topic_id', XOBJ_DTYPE_INT);
        $this->initVar('ratinguser', XOBJ_DTYPE_INT);
        $this->initVar('rating', XOBJ_DTYPE_INT);
        $this->initVar('ratingtimestamp', XOBJ_DTYPE_INT);
        $this->initVar('ratinghostname', XOBJ_DTYPE_TXTBOX);
    }
}

class NewbbRateHandler extends ArtObjectHandler 
{
    function NewbbRateHandler(&$db) {
        $this->ArtObjectHandler($db, 'bb_votedata', 'Nrate', 'ratingid');
    }
    
	function synchronization()
    {
		return;
	}

    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
	    return parent::cleanOrphan($this->db->prefix("bb_topics"), "topic_id");
    }
}

?>