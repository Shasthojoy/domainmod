<?php
/**
 * /reset.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2016 Greg Chetcuti <greg@chetcuti.com>
 *
 * Project: http://domainmod.org   Author: http://chetcuti.com
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
?>
<?php
include("_includes/start-session.inc.php");
include("_includes/init.inc.php");

require_once(DIR_ROOT . "classes/Autoloader.php");
spl_autoload_register('DomainMOD\Autoloader::classAutoloader');

$system = new DomainMOD\System();
$error = new DomainMOD\Error();
$maint = new DomainMOD\Maintenance();
$layout = new DomainMOD\Layout();
$time = new DomainMOD\Time();
$form = new DomainMOD\Form();

include(DIR_INC . "head.inc.php");
include(DIR_INC . "config.inc.php");
include(DIR_INC . "software.inc.php");
include(DIR_INC . "database.inc.php");

$system->loginCheck();

$page_title = "Reset Password";
$software_section = "resetpassword";

$new_username = $_REQUEST['new_username'];

if ($new_username != "") {

    $sql = "SELECT username, email_address
            FROM users
            WHERE username = '" . $new_username . "'
              AND active = '1'";

    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

    if (mysqli_num_rows($result) == 1) {

        while ($row = mysqli_fetch_object($result)) {

            $new_password = substr(md5(time()), 0, 8);

            $sql_update = "UPDATE users
                           SET `password` = password('$new_password'),
                                  new_password = '1',
                               update_time = '" . $time->stamp() . "'
                           WHERE username = '$row->username'
                             AND email_address = '$row->email_address'";
            $result_update = mysqli_query($connection, $sql_update);

            $username = $row->username;
            $email_address = $row->email_address;

            include(DIR_INC . "email/send-new-password.inc.php");

            $_SESSION['s_message_success'] .= "If there is a matching username in the system your new password will been emailed to you.<BR>";

            header("Location: " . $web_root . "/");
            exit;

        }

    } else {

        $_SESSION['s_message_success'] .= "If there is a matching username in the system your new password will been emailed to you.<BR>";

        header("Location: " . $web_root . "/");
        exit;

    }

} else {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if ($new_username == "") {
            $_SESSION['s_message_danger'] .= "Enter your username<BR>";
        }
    }

}
?>
<?php include(DIR_INC . 'doctype.inc.php'); ?>
<html>
<head>
    <title><?php echo $system->pageTitle($software_title, $page_title); ?></title>
    <?php include(DIR_INC . "layout/head-tags.inc.php"); ?>
</head>
<body class="hold-transition skin-red" onLoad="document.forms[0].elements[0].focus()">
<?php include(DIR_INC . "layout/header-login.inc.php"); ?>
<?php
    echo $form->showFormTop('');
    echo $form->showInputText('new_username', 'Username', '', $new_username, '20', '', '', '');
    echo $form->showSubmitButton('Reset Password', '', '');
    echo $form->showFormBottom('');
?>
<BR><a href="<?php echo $web_root; ?>/">Cancel Password Reset</a>
<?php include(DIR_INC . "layout/footer-login.inc.php"); ?>
</body>
</html>
