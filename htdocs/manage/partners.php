<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 encoding=utf-8: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 Eventum Team              .                       |
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
// | Authors: Bryan Alsdorf <balsdorf@gmail.com>                          |
// +----------------------------------------------------------------------+

require_once dirname(__FILE__) . '/../../init.php';

$tpl = new Template_Helper();
$tpl->setTemplate("manage/partners.tpl.html");

Auth::checkAuthentication(APP_COOKIE);
$tpl->assign("type", "partners");

$role_id = Auth::getCurrentRole();
if ($role_id < User::getRoleID('manager')) {
    Misc::setMessage(ev_gettext("Sorry, you are not allowed to access this page."), Misc::MSG_ERROR);
    $tpl->displayTemplate();exit;
}

if (@$_POST["cat"] == "update") {
    $res = Partner::update($_POST['code'], @$_POST['projects']);
    $tpl->assign("result", $res);
    Misc::mapMessages($res, array(
            1   =>  array(ev_gettext('Thank you, the partner was updated successfully.'), Misc::MSG_INFO),
            -1  =>  array(ev_gettext('An error occurred while trying to update the partner information.'), Misc::MSG_ERROR),
    ));
}

if (@$_GET["cat"] == "edit") {
    $info = Partner::getDetails($_GET["code"]);
    $tpl->assign("info", $info);
}

$tpl->assign("list", Partner::getList());
$tpl->assign("project_list", Project::getAll());

$tpl->displayTemplate();
