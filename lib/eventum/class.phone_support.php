<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 encoding=utf-8: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 - 2008 MySQL AB                                   |
// | Copyright (c) 2008 - 2010 Sun Microsystem Inc.                       |
// | Copyright (c) 2011 - 2014 Eventum Team.                              |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 51 Franklin Street, Suite 330                                          |
// | Boston, MA 02110-1301, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// | Authors: Elan Ruusamäe <glen@delfi.ee>                               |
// +----------------------------------------------------------------------+

/**
 * Class to handle the business logic related to the phone support
 * feature of the application.
 */

class Phone_Support
{
    /**
     * Method used to add a new category to the application.
     *
     * @return  integer 1 if the update worked properly, any other value otherwise
     */
    public static function insertCategory()
    {
        if (Validation::isWhitespace($_POST["title"])) {
            return -2;
        }
        $stmt = "INSERT INTO
                    {{%project_phone_category}}
                 (
                    phc_prj_id,
                    phc_title
                 ) VALUES (
                    ?, ?
                 )";
        try {
            DB_Helper::getInstance()->query($stmt, array($_POST["prj_id"], $_POST["title"]));
        } catch (DbException $e) {
            return -1;
        }

        return 1;
    }

    /**
     * Method used to update the values stored in the database.
     * Typically the user would modify the title of the category in
     * the application and this method would be called.
     *
     * @return  integer 1 if the update worked properly, any other value otherwise
     */
    public static function updateCategory()
    {
        if (Validation::isWhitespace($_POST["title"])) {
            return -2;
        }
        $stmt = "UPDATE
                    {{%project_phone_category}}
                 SET
                    phc_title=?
                 WHERE
                    phc_prj_id=? AND
                    phc_id=?";
        try {
            DB_Helper::getInstance()->query($stmt, array($_POST["title"], $_POST["prj_id"], $_POST["id"]));
        } catch (DbException $e) {
            return -1;
        }

        return 1;
    }

    /**
     * Method used to remove user-selected categories from the
     * database.
     *
     * @return  boolean Whether the removal worked or not
     */
    public static function removeCategory()
    {
        $items = $_POST["items"];
        $itemlist = DB_Helper::buildList($items);

        $stmt = "DELETE FROM
                    {{%project_phone_category}}
                 WHERE
                    phc_id IN ($itemlist)";
        try {
            DB_Helper::getInstance()->query($stmt, $items);
        } catch (DbException $e) {
            return false;
        }

        return true;
    }

    /**
     * Method used to get the full details of a category.
     *
     * @param   integer $phc_id The category ID
     * @return  array The information about the category provided
     */
    public static function getCategoryDetails($phc_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    {{%project_phone_category}}
                 WHERE
                    phc_id=?";
        try {
            $res = DB_Helper::getInstance()->getRow($stmt, array($phc_id));
        } catch (DbException $e) {
            return "";
        }

        return $res;
    }

    /**
     * Method used to get the full list of categories associated with
     * a specific project.
     *
     * @param   integer $prj_id The project ID
     * @return  array The full list of categories
     */
    public static function getCategoryList($prj_id)
    {
        $stmt = "SELECT
                    phc_id,
                    phc_title
                 FROM
                    {{%project_phone_category}}
                 WHERE
                    phc_prj_id=?
                 ORDER BY
                    phc_title ASC";
        try {
            $res = DB_Helper::getInstance()->getAll($stmt, array($prj_id));
        } catch (DbException $e) {
            return "";
        }

        return $res;
    }

    /**
     * Method used to get an associative array of the list of
     * categories associated with a specific project.
     *
     * @param   integer $prj_id The project ID
     * @return  array The associative array of categories
     */
    public static function getCategoryAssocList($prj_id)
    {
        $stmt = "SELECT
                    phc_id,
                    phc_title
                 FROM
                    {{%project_phone_category}}
                 WHERE
                    phc_prj_id=?
                 ORDER BY
                    phc_id ASC";
        try {
            $res = DB_Helper::getInstance()->getPair($stmt, array($prj_id));
        } catch (DbException $e) {
            return "";
        }

        return $res;
    }

    /**
     * Method used to get the details of a given phone support entry.
     *
     * @param   integer $phs_id The phone support entry ID
     * @return  array The phone support entry details
     */
    public static function getDetails($phs_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    {{%phone_support}}
                 WHERE
                    phs_id=?";
        try {
            $res = DB_Helper::getInstance()->getRow($stmt, array($phs_id));
        } catch (DbException $e) {
            return "";
        }

        return $res;
    }

    /**
     * Method used to get the full listing of phone support entries
     * associated with a specific issue.
     *
     * @param   integer $issue_id The issue ID
     * @return  array The list of notes
     */
    public static function getListing($issue_id)
    {
        $stmt = "SELECT
                    {{%phone_support}}.*,
                    usr_full_name,
                    phc_title,
                    iss_prj_id
                 FROM
                    {{%phone_support}},
                    {{%project_phone_category}},
                    {{%user}},
                    {{%issue}}
                 WHERE
                    phs_iss_id=iss_id AND
                    iss_prj_id=phc_prj_id AND
                    phs_phc_id=phc_id AND
                    phs_usr_id=usr_id AND
                    phs_iss_id=?
                 ORDER BY
                    phs_created_date ASC";
        try {
            $res = DB_Helper::getInstance()->getAll($stmt, array($issue_id));
        } catch (DbException $e) {
            return "";
        }

        for ($i = 0; $i < count($res); $i++) {
            $res[$i]["phs_description"] = Misc::activateLinks(nl2br(htmlspecialchars($res[$i]["phs_description"])));
            $res[$i]["phs_description"] = Link_Filter::processText($res[$i]['iss_prj_id'], $res[$i]["phs_description"]);
            $res[$i]["phs_created_date"] = Date_Helper::getFormattedDate($res[$i]["phs_created_date"]);
        }

        return $res;
    }

    /**
     * Method used to add a phone support entry using the user
     * interface form available in the application.
     *
     * @return  integer 1 if the insert worked, -1 or -2 otherwise
     */
    public static function insert()
    {
        $usr_id = Auth::getUserID();
        // format the date from the form
        $created_date = sprintf('%04d-%02d-%02d %02d:%02d:%02d',
            $_POST["date"]["Year"], $_POST["date"]["Month"],
            $_POST["date"]["Day"], $_POST["date"]["Hour"],
            $_POST["date"]["Minute"], 0);
        // convert the date to GMT timezone
        $created_date = Date_Helper::convertDateGMT($created_date . " " . Date_Helper::getPreferredTimezone());
        $stmt = "INSERT INTO
                    {{%phone_support}}
                 (
                    phs_iss_id,
                    phs_usr_id,
                    phs_phc_id,
                    phs_created_date,
                    phs_type,
                    phs_phone_number,
                    phs_description,
                    phs_phone_type,
                    phs_call_from_lname,
                    phs_call_from_fname,
                    phs_call_to_lname,
                    phs_call_to_fname
                 ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?
                 )";
        $params = array(
            $_POST["issue_id"],
            $usr_id,
            $_POST["phone_category"],
            $created_date,
            $_POST["type"],
            $_POST["phone_number"],
            $_POST["description"],
            $_POST["phone_type"],
            $_POST["from_lname"],
            $_POST["from_fname"],
            $_POST["to_lname"],
            $_POST["to_fname"],
        );
        try {
            DB_Helper::getInstance()->query($stmt, $params);
        } catch (DbException $e) {
            return -1;
        }

        // enter the time tracking entry about this phone support entry
        $phs_id = DB_Helper::get_last_insert_id();
        $prj_id = Auth::getCurrentProject();
        $_POST['category'] = Time_Tracking::getCategoryID($prj_id, 'Telephone Discussion');
        $_POST['time_spent'] = $_POST['call_length'];
        $_POST['summary'] = ev_gettext("Time entry inserted from phone call.");
        Time_Tracking::insertEntry();
        $stmt = "SELECT
                    max(ttr_id)
                 FROM
                    {{%time_tracking}}
                 WHERE
                    ttr_iss_id = ? AND
                    ttr_usr_id = ?";
        $ttr_id = DB_Helper::getInstance()->getOne($stmt, array($_POST["issue_id"], $usr_id));

        Issue::markAsUpdated($_POST['issue_id'], 'phone call');
        // need to save a history entry for this
        History::add($_POST['issue_id'], $usr_id, History::getTypeID('phone_entry_added'),
                        ev_gettext('Phone Support entry submitted by %1$s', User::getFullName($usr_id)));
        // XXX: send notifications for the issue being updated (new notification type phone_support?)

        // update phone record with time tracking ID.
        if ((!empty($phs_id)) && (!empty($ttr_id))) {
            $stmt = "UPDATE
                        {{%phone_support}}
                     SET
                        phs_ttr_id = ?
                     WHERE
                        phs_id = ?";
            try {
                DB_Helper::getInstance()->query($stmt, array($ttr_id, $phs_id));
            } catch (DbException $e) {
                return -1;
            }
        }

        return 1;
    }

    /**
     * Method used to remove a specific phone support entry from the
     * application.
     *
     * @param   integer $phone_id The phone support entry ID
     * @return  integer 1 if the removal worked, -1 or -2 otherwise
     */
    public static function remove($phone_id)
    {
        $stmt = "SELECT
                    phs_iss_id,
                    phs_ttr_id,
                    phs_usr_id
                 FROM
                    {{%phone_support}}
                 WHERE
                    phs_id=?";
        $details = DB_Helper::getInstance()->getRow($stmt, array($phone_id));
        if ($details['phs_usr_id'] != Auth::getUserID()) {
            return -2;
        }

        $stmt = "DELETE FROM
                    {{%phone_support}}
                 WHERE
                    phs_id=?";
        try {
            DB_Helper::getInstance()->query($stmt, array($phone_id));
        } catch (DbException $e) {
            return -1;
        }

        Issue::markAsUpdated($details["phs_iss_id"]);
        // need to save a history entry for this
        $summary = ev_gettext('Phone Support entry removed by %1$s', User::getFullName(Auth::getUserID()));
        History::add($details["phs_iss_id"], Auth::getUserID(), History::getTypeID('phone_entry_removed'), $summary);

        if (!empty($details["phs_ttr_id"])) {
            $time_result = Time_Tracking::removeEntry($details["phs_ttr_id"], $details['phs_usr_id']);
            if ($time_result == 1) {
                return 2;
            }

            return $time_result;
        }

        return 1;
    }

    /**
     * Method used to remove all phone support entries associated with
     * a given set of issues.
     *
     * @param   array $ids The array of issue IDs
     * @return  boolean
     */
    public static function removeByIssues($ids)
    {
        $items = DB_Helper::buildList($ids);

        $stmt = "DELETE FROM
                    {{%phone_support}}
                 WHERE
                    phs_iss_id IN ($items)";
        try {
            DB_Helper::getInstance()->query($stmt, $ids);
        } catch (DbException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns the number of calls by a user in a time range.
     *
     * @param   string $usr_id The ID of the user
     * @param   integer $start The timestamp of the start date
     * @param   integer $end The timestamp of the end date
     * @return  integer The number of phone calls by the user.
     */
    public static function getCountByUser($usr_id, $start, $end)
    {
        $stmt = "SELECT
                    COUNT(phs_id)
                 FROM
                    {{%phone_support}},
                    {{%issue}}
                 WHERE
                    phs_iss_id = iss_id AND
                    iss_prj_id = ? AND
                    phs_created_date BETWEEN ? AND ? AND
                    phs_usr_id = ?";
        $params = array(Auth::getCurrentProject(), $start, $end, $usr_id);
        try {
            $res = DB_Helper::getInstance()->getOne($stmt, $params);
        } catch (DbException $e) {
            return "";
        }

        return $res;
    }
}
