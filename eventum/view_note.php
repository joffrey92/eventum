<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 encoding=utf-8: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004, 2005, 2006, 2007 MySQL AB                  |
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
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: view_note.php 3192 2007-01-11 22:07:36Z glen $
//
require_once("config.inc.php");
require_once(APP_INC_PATH . "class.template.php");
require_once(APP_INC_PATH . "class.auth.php");
require_once(APP_INC_PATH . "class.issue.php");
require_once(APP_INC_PATH . "class.misc.php");
require_once(APP_INC_PATH . "class.note.php");
require_once(APP_INC_PATH . "class.user.php");
require_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("view_note.tpl.html");

Auth::checkAuthentication(APP_COOKIE, 'index.php?err=5', true);
$usr_id = Auth::getUserID();

$note = Note::getDetails($_GET["id"]);

if ($note == '') {
    $tpl->assign("note", '');
    $tpl->displayTemplate();
    exit;
} else {
    $note["message"] = $note["not_note"];
    $issue_id = Note::getIssueID($_GET["id"]);
    $usr_id = Auth::getUserID();
}

if ((User::getRoleByUser($usr_id, Issue::getProjectID($issue_id)) < User::getRoleID('Standard User')) || (!Issue::canAccess($issue_id, Auth::getUserID()))) {
    $tpl->setTemplate("permission_denied.tpl.html");
    $tpl->displayTemplate();
    exit;
}

$note = Note::getDetails($_GET["id"]);
$note["message"] = $note["not_note"];

$issue_id = Note::getIssueID($_GET["id"]);
$tpl->bulkAssign(array(
    "note"        => $note,
    "issue_id"    => $issue_id,
    'extra_title' => "Note #" . $_GET['num'] . ": " . $note['not_title']
));

if (!empty($issue_id)) {
    $sides = Note::getSideLinks($issue_id, $_GET["id"]);
    $tpl->assign(array(
        'previous' => $sides['previous'],
        'next'     => $sides['next']
    ));
}

$tpl->displayTemplate();
