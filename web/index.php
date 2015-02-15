<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * phpMemAdmin
 *
 * index.php - Loader for phpMemAdmin.
 *
 *
 * PHP versions 5.5
 *
 * LICENSE:
 * phpMemAdmin - Bringing Memcached to the web.
 *
 * Copyright (c) 2014 - 2015, Benjamin Carl
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * - Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * - Neither the name of Memcached.php nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * Please feel free to contact us via e-mail: opensource@clickalicious.de
 *
 * @category   Clickalicious
 * @package    Clickalicious_PhpMemAdmin
 * @subpackage Clickalicious_PhpMemAdmin_App
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2014 - 2015 Benjamin Carl
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @version    Git: $Id$
 * @link       https://github.com/clickalicious/phpMemAdmin
 */

$path = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

define(
    'CLICKALICIOUS_PHPMEMADMIN_BASE_PATH',
    $path
);

// Is composer setup we're running in?
if (true === file_exists('../vendor/autoload.php')) {
    include_once '../vendor/autoload.php';

} else {
    set_include_path(
        get_include_path() . PATH_SEPARATOR . $path
    );
}

// Bootstrapping is just error handling and stuff like that ...
require_once 'Clickalicious/PhpMemAdmin/Bootstrap.php';

/**
 * I decided to make config handling not part of phpMemAdmin (currently not!).
 * So i wrote this tiny config loader which returns the JSON content of app/.config
 * as stdClass. Currently good enough for PoC.
 */
if (file_exists(CLICKALICIOUS_PHPMEMADMIN_BASE_PATH . 'app/.config') === true) {
    $config = CLICKALICIOUS_PHPMEMADMIN_BASE_PATH . 'app/.config';
} else {
    $config = CLICKALICIOUS_PHPMEMADMIN_BASE_PATH . 'app/.config.dist';
}

$config = json_decode(
    file_get_contents($config)
);

/**
 * Init the applications core ...
 */
$app = new \Clickalicious\PhpMemAdmin\App(
    $config,
    new \Clickalicious\Memcached\Client()       //\\ Memcached.php client as master to clone
);

/**
 * We need to pass this arguments from outside. Required to keep responsibilities clean.
 * So it is possible to modify and control the App from outside for unit-testing.
 *
 * If auto render enabled.
 */
if ($config->render->auto === true) {

    echo $app->render(
        $_SERVER['SCRIPT_NAME'],
        $_GET
    );
}
