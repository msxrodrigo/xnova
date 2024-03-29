<?php
/**
 * This file is part of XNova:Legacies
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see http://www.xnova-ng.org/
 *
 * Copyright (c) 2009-2010, XNova Support Team <http://www.xnova-ng.org>
 * All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *                                --> NOTICE <--
 *  This file is part of the core development branch, changing its contents will
 * make you unable to use the automatic updates manager. Please refer to the
 * documentation for further information about customizing XNova.
 *
 */

define('PS', PATH_SEPARATOR);
define('DS', DIRECTORY_SEPARATOR);

/**
 * @var string The root directory of the game files.
 */
define('ROOT_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

/**
 * Start up session handler
 *
 * @deprecated
 * @todo
 */
// {{{
session_start();
//}}}

/**
 * Set up environment
 */
defined('DEBUG') || define('DEBUG', ($env = strtolower(getenv('DEBUG'))) === '1' || $env === 'true' || $env === 'on');

@ini_set('display_errors', DEBUG);

set_include_path(implode(PS, array(
    ROOT_PATH . 'library' . DS,
    ROOT_PATH . 'includes' . DS . 'classes' . DS,
    get_include_path()
    )));

/**
 * Zend Framework's autoloader class.
 */
require 'Nova.php';
require 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Legacies_');

/**
 * @deprecated
 * @todo
 */
//{{{
define('PHPEXT', include ROOT_PATH . 'extension.inc');
define('DEFAULT_SKINPATH', '../skins/xnova/');
define('TEMPLATE_DIR', realpath(ROOT_PATH . '/templates/'));
define('TEMPLATE_NAME', 'OpenGame');
define('DEFAULT_LANG', 'fr_FR');
$dpath = DEFAULT_SKINPATH;
//}}}

$debug = Nova::getSingleton('core/debug');

/**
 * @deprecated
 */
// {{{
include(ROOT_PATH . 'includes/constants.' . PHPEXT);
include(ROOT_PATH . 'includes/functions.' . PHPEXT);
include(ROOT_PATH . 'includes/unlocalised.' . PHPEXT);
include(ROOT_PATH . 'includes/todofleetcontrol.' . PHPEXT);
include(ROOT_PATH . 'language/' . DEFAULT_LANG . '/lang_info.cfg');
include(ROOT_PATH . 'includes/vars.' . PHPEXT);
include(ROOT_PATH . 'includes/db.' . PHPEXT);
include(ROOT_PATH . 'includes/strings.' . PHPEXT);

include(ROOT_PATH . 'includes/databaseinfos.php');
include(ROOT_PATH . 'includes/migrateinfo.php');
// }}}

$mode = isset($_GET['mode']) ? strval($_GET['mode']) : 'intro';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$nextPage = $page + 1;

$mainTpl = gettemplate('install/ins_body');
includeLang('install/install');

switch ($mode) {
    case 'intro':
            $subTpl = gettemplate('install/ins_intro');
            $bloc = $lang;
            $bloc['dpath'] = $dpath;
            $frame  = parsetemplate($subTpl, $bloc);
         break;

    case 'ins':
        if ($page == 1) {
            if (isset($_GET['error']) && intval($_GET['error']) == 1) {
	            adminMessage ($lang['ins_error1'], $lang['ins_error']);
            }
            elseif (isset($_GET['error']) && intval($_GET['error']) == 2) {
	            adminMessage ($lang['ins_error2'], $lang['ins_error']);
            }

            $subTpl = gettemplate('install/ins_form');
            $bloc   = $lang;
            $bloc['dpath'] = $dpath;
            $frame  = parsetemplate($subTpl, $bloc);
        } else if ($page == 2) {
            $host   = $_POST['host'];
            $user   = $_POST['user'];
            $pass   = $_POST['passwort'];
            $prefix = $_POST['prefix'];
            $db     = $_POST['db'];

            $connection = @mysql_connect($host, $user, $pass);
            if (!$connection) {
                header("Location: ?mode=ins&page=1&error=1");
                exit();
            }

            $dbselect = @mysql_select_db($db);
            if (!$dbselect) {
                header("Location: ?mode=ins&page=1&error=1");
                exit();
            }

            $dz = fopen("../config.php", "w");
            if (!$dz) {
	            header("Location: ?mode=ins&page=1&error=2");
	            exit();
            }
            $fileData =<<<EOF
<?php return array(
    'global' => array(
        'database' => array(
            'engine' => 'mysql',
            'options' => array(
                'hostname' => '{$host}',
                'username' => '{$user}',
                'password' => '{$pass}',
                'database' => '{$db}'
                ),
            'table_prefix' => '{$prefix}',
            )
        )
    );
EOF;
            fwrite($dz, $fileData);
            fclose($dz);

            doquery($QryTableAks, 'aks');
            doquery($QryTableAnnonce, 'annonce');
            doquery($QryTableAlliance, 'alliance');
            doquery($QryTableBanned, 'banned');
            doquery($QryTableBuddy, 'buddy');
            doquery($QryTableChat, 'chat');
            doquery($QryTableConfig, 'config');
            doquery($QryInsertConfig, 'config');
            doquery($QryTabledeclared, 'declared');
            doquery($QryTableErrors, 'errors');
            doquery($QryTableFleets, 'fleets');
            doquery($QryTableGalaxy, 'galaxy');
            doquery($QryTableIraks, 'iraks');
            doquery($QryTableLunas, 'lunas');
            doquery($QryTableMessages, 'messages');
            doquery($QryTableNotes, 'notes');
            doquery($QryTablePlanets, 'planets');
            doquery($QryTableRw, 'rw');
            doquery($QryTableStatPoints, 'statpoints');
            doquery($QryTableUsers, 'users');
            doquery($QryTableMulti, 'multi');

            $subTpl = gettemplate ('install/ins_form_done');
            $bloc   = $lang;
            $bloc['dpath']        = $dpath;
            $frame  = parsetemplate ( $subTpl, $bloc );
        } elseif ($page == 3) {
            if (isset($_GET['error']) && intval($_GET['error']) == 3) {
                adminMessage($lang['ins_error3'], $lang['ins_error']);
            }

            $subTpl = gettemplate ('install/ins_acc');
            $bloc   = $lang;
            $bloc['dpath']        = $dpath;
            $frame  = parsetemplate ( $subTpl, $bloc );
        } elseif ($page == 4) {
            $adm_user   = $_POST['adm_user'];
            $adm_pass   = $_POST['adm_pass'];
            $adm_email  = $_POST['adm_email'];
            $adm_planet = $_POST['adm_planet'];
            $adm_sex    = $_POST['adm_sex'];
            $md5pass    = md5($adm_pass);

            if (!isset($_POST['adm_user']) || empty($_POST['adm_user'])) {
                header("Location: ?mode=ins&page=3&error=3");
                exit();
            }
            if (!isset($_POST['adm_pass']) || empty($_POST['adm_pass'])) {
                header("Location: ?mode=ins&page=3&error=3");
                exit();
            }
            if (!isset($_POST['adm_email']) || empty($_POST['adm_email'])) {
                header("Location: ?mode=ins&page=3&error=3");
                exit();
            }
            if (!isset($_POST['adm_planet']) || empty($_POST['adm_planet'])) {
                header("Location: ?mode=ins&page=3&error=3");
                exit();
            }

            $config = include(ROOT_PATH . 'config.php');
            $db_host   = $config['global']['database']['options']['hostname'];
            $db_user   = $config['global']['database']['options']['username'];
            $db_pass   = $config['global']['database']['options']['password'];
            $db_db     = $config['global']['database']['options']['database'];
            $db_prefix = $config['global']['database']['table_prefix'];

            $connection = @mysql_connect($db_host, $db_user, $db_pass);
            if (!$connection) {
                header("Location: ?mode=ins&page=1&error=1");
                exit();
            }

            $dbselect = @mysql_select_db($db_db);
            if (!$dbselect) {
                header("Location: ?mode=ins&page=1&error=1");
                exit();
            }

            $sql  =<<<EOF
INSERT INTO {{table}} SET
  `id` = 1,
  `username` = "{$adm_user}",
  `email` = "{$adm_email}",
  `email_2` = "{$adm_email}",
  `authlevel` = 3,
  `sex` = "{$adm_sex}",
  `id_planet` = 1,
  `galaxy` = 1,
  `system` = 1,
  `planet` = 1,
  `current_planet` = 1,
  `register_time` = UNIX_TIMESTAMP(),
  `password` = "{$md5pass}"
EOF;
            doquery($sql, 'users');

            $sql =<<<EOF
INSERT INTO {{table}} SET
  `name`              = '". $adm_planet ."',
  `id_owner`          = 1,
  `galaxy`            = 1,
  `system`            = 1,
  `planet`            = 1,
  `last_update`       = UNIX_TIMESTAMP(),
  `planet_type`       = 1,
  `image`             = 'normaltempplanet02',
  `diameter`          = 12750,
  `field_max`         = 163,
  `temp_min`          = 47,
  `temp_max`          = 87,
  `metal`             = 500,
  `metal_perhour`     = 0,
  `metal_max`         = 1000000,
  `crystal`           = 500,
  `crystal_perhour`   = 0,
  `crystal_max`       = 1000000,
  `deuterium`         = 500,
  `deuterium_perhour` = 0,
  `deuterium_max`     = 1000000
EOF;
            doquery($sql, 'planets');

            $sql  =<<<EOF
INSERT INTO {{table}} SET
  `galaxy` = 1,
  `system` = 1,
  `planet` = 1,
  `id_planet` = 1
EOF;
            doquery($sql, 'galaxy');

            doquery("UPDATE {{table}} SET `config_value` = '1' WHERE `config_name` = 'LastSettedGalaxyPos';", 'config');
            doquery("UPDATE {{table}} SET `config_value` = '1' WHERE `config_name` = 'LastSettedSystemPos';", 'config');
            doquery("UPDATE {{table}} SET `config_value` = '1' WHERE `config_name` = 'LastSettedPlanetPos';", 'config');
            doquery("UPDATE {{table}} SET `config_value` = `config_value` + '1' WHERE `config_name` = 'users_amount' LIMIT 1;", 'config');

            $subTpl = gettemplate ('install/ins_acc_done');
            $bloc   = $lang;
            $bloc['dpath'] = $dpath;
            $frame  = parsetemplate ( $subTpl, $bloc );
        }
        break;

    case 'goto':
        if ($page == 1) {
            $subTpl = gettemplate ('install/ins_goto_intro');
            $bloc   = $lang;
            $bloc['dpath']        = $dpath;
            $frame  = parsetemplate ( $subTpl, $bloc );
        } elseif ($page == 2) {
            if ($_GET['error'] == 1) {
            adminMessage ($lang['ins_error1'], $lang['ins_error']);
            }
            elseif ($_GET['error'] == 2) {
            adminMessage ($lang['ins_error2'], $lang['ins_error']);
            }

            $subTpl = gettemplate ('install/ins_goto_form');
            $bloc   = $lang;
            $bloc['dpath']        = $dpath;
            $frame  = parsetemplate ( $subTpl, $bloc );
        } elseif ($page == 3) {
            $host   = $_POST['host'];
            $user   = $_POST['user'];
            $pass   = $_POST['passwort'];
            $prefix = $_POST['prefix'];
            $db     = $_POST['db'];

            if (!mysql_connect($host, $user, $pass)) {
                header("Location: ?mode=goto&page=2&error=1");
                exit();
            }

            if (!mysql_select_db($db)) {
                header("Location: ?mode=goto&page=2&error=1");
                exit();
            }

            $dz = fopen("../config.php", "w");
            if (!$dz) {
                header("Location: ?mode=ins&page=1&error=2");
                exit();
            }

            $fileData =<<<EOF
<?php return array(
    'global' => array(
        'database' => array(
            'engine' => 'mysql',
            'options' => array(
                'hostname' => '{$host}',
                'username' => '{$user}',
                'password' => '{$pass}',
                'database' => '{$db}'
                ),
            'table_prefix' => '{$prefix}',
            )
        )
    );
EOF;
            fwrite($dz, $fileData);
            fclose($dz);

            foreach ($QryMigrate as $query) {
                doquery($query, $prefix);
            }

            $subTpl = gettemplate('install/ins_goto_done');
            $bloc   = $lang;
            $bloc['dpath']        = $dpath;
            $frame  = parsetemplate($subTpl, $bloc);
         }
         break;

    case 'bye':
            header("Location: ../");
         break;

    default:
        header('Location: ?mode=intro');
        die();
}


$parse                 = $lang;
$parse['ins_state']    = $page;
$parse['ins_page']     = $frame;
$parse['dis_ins_btn']  = "?mode=$mode&page=$nextPage";
$parse['dpath']        = $dpath;
$data                 = parsetemplate($mainTpl, $parse);

display($data, "Installeur", false, '', true);
