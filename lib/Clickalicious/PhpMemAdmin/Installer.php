<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Clickalicious\PhpMemAdmin;

// Check for execution from www or something!
if (php_sapi_name() !== 'cli') {
    die('Please execute the installed from command line.');
}

/**
 * phpMemAdmin
 *
 * Installer.php - ...
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
 * - Neither the name of phpMemAdmin nor the names of its
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
 * @subpackage Clickalicious_PhpMemAdmin_Installer
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2014 - 2015 Benjamin Carl
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @version    Git: $Id$
 * @link       https://github.com/clickalicious/phpMemAdmin
 */

use Composer\Script\CommandEvent;

#require_once 'Exception.php';
#require_once 'BaseInstaller.php';
#use \Clickalicious\PhpMemAdmin\Exception;
#use \Clickalicious\PhpMemAdmin\BaseInstaller;

define('PHPMEMADMIN_INSTALLER_VERSION', '$Id$');

/**
 * phpMemAdmin
 *
 * ...
 *
 * @category   Clickalicious
 * @package    Clickalicious_PhpMemAdmin
 * @subpackage Clickalicious_PhpMemAdmin_Installer
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2014 - 2015 Benjamin Carl
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @version    Git: $Id$
 * @link       https://github.com/clickalicious/phpMemAdmin
 */
class Installer //extends BaseInstaller
{
    public static function postInstall(CommandEvent $event)
    {
        var_dump($event);
        echo 1;
        die;
    }
}
