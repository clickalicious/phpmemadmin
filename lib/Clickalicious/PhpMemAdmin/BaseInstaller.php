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
 * BaseInstaller.php - Base of installer for phpMemAdmin. This installer automate the single
 * steps of symlink folders to document root. On Windows the symlink functionality
 * requires administrator privileges on *nix platforms you will need the right
 * to symlink to the folder in general.
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
 * @subpackage Clickalicious_PhpMemAdmin_BaseInstaller
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2014 - 2015 Benjamin Carl
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @version    Git: $Id$
 * @link       https://github.com/clickalicious/phpMemAdmin
 */

use Composer\Script\CommandEvent;

ini_set('html_errors', 0);

/**
 * phpMemAdmin
 *
 * Base of installer for phpMemAdmin. This installer automate the single
 * steps of symlink folders to document root. On Windows the symlink functionality
 * requires administrator privileges on *nix platforms you will need the right
 * to symlink to the folder in general.
 *
 * @category   Clickalicious
 * @package    Clickalicious_PhpMemAdmin
 * @subpackage Clickalicious_PhpMemAdmin_BaseInstaller
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2014 - 2015 Benjamin Carl
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @version    Git: $Id$
 * @link       https://github.com/clickalicious/phpMemAdmin
 */
class BaseInstaller
{
    /**
     * The arguments from CLI (argv) parsed.
     *
     * @var array
     * @access protected
     * @static
     */
    protected static $arguments = array();

    /**
     * The extra(s) from composer.json passed.
     *
     * @var array
     * @access protected
     * @static
     */
    protected static $extra = array();

    /**
     * The install path
     *
     * @var string
     * @access protected
     * @static
     */
    protected static $installPath;


    /**
     * Setter for extra.
     *
     * @param array $extra The extra to set.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @static
     */
    protected static function setExtra(array $extra)
    {
        self::$extra = $extra;
    }

    /**
     * Getter for extra.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array Extra
     * @access protected
     * @static
     */
    protected static function getExtra()
    {
        return self::$extra;
    }

    /**
     * Setter for arguments.
     *
     * @param array $arguments The arguments to set.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @static
     */
    protected static function setArguments(array $arguments)
    {
        self::$arguments = $arguments;
    }

    /**
     * Getter for arguments.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The arguments
     * @access protected
     * @static
     */
    protected static function getArguments()
    {
        return self::$arguments;
    }

    /**
     * Setter for installPath.
     *
     * @param string $installPath The installPath to set.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @static
     */
    protected static function setInstallPath($installPath)
    {
        self::$installPath = $installPath;
    }

    /**
     * Getter for installPath.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The install path
     * @access protected
     * @static
     */
    protected static function getInstallPath()
    {
        return self::$installPath;
    }

    /**
     * Returns install path relative to current path.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The install path
     * @access protected
     */
    protected static function retrieveInstallPath()
    {
        $path = DIRECTORY_SEPARATOR . implode(
                DIRECTORY_SEPARATOR,
                array(
                    'vendor',
                    'clickalicious',
                    'phpmemadmin',
                    'lib',
                    'Clickalicious',
                    'PhpMemAdmin',
                    'BaseInstaller.php'
                )
            );

        return realpath(str_replace($path, '', __FILE__));
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @param string $source      Source path
     * @param string $destination Destination path
     * @param mixed  $permissions New folder creation permissions
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return bool Returns true on success, false on failure
     * @access protected
     */
    protected static function xcopy($source, $destination, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $destination);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $destination);
        }

        // Make destination directory
        if (!is_dir($destination)) {
            mkdir($destination, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            self::xcopy(
                $source . DIRECTORY_SEPARATOR . $entry,
                $destination . DIRECTORY_SEPARATOR . $entry
            );
        }

        // Clean up
        $dir->close();
        return true;
    }


    /**
     * Detect and return source path containing the bootstrap project structure.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return bool Returns true on success, false on failure
     * @access protected
     */
    protected static function getSourcePath()
    {
        $path = DIRECTORY_SEPARATOR . implode(
                DIRECTORY_SEPARATOR,
                array('lib', 'Clickalicious','PhpMemAdmin', 'BaseInstaller.php')
            );

        return realpath(str_replace($path, '', __FILE__)) . DIRECTORY_SEPARATOR;
    }

    /**
     * Shows phpMemAdmin version banner.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @static
     */
    protected static function showVersion()
    {
        \cli\line();
        \cli\line(\cli\Colors::colorize('%y+----------------------------------------------------------------------+%N'));
        \cli\line(\cli\Colors::colorize('%y| Installer                                                            |%N'));
        \cli\line(\cli\Colors::colorize('%y| Version: ' . PROJECT_INSTALLER_VERSION . '             |%N'));
        \cli\line(\cli\Colors::colorize('%y+----------------------------------------------------------------------+%N'));
    }

    /**
     * Retrieve arguments from global to prepare them for further use.
     *
     * @param CommandEvent $event The Composer event fired to retrieve arguments from
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return bool TRUE on success, otherwise FALSE
     * @access protected
     * @static
     */
    protected static function initArguments(CommandEvent $event = null)
    {
        // Check for retrieving arguments from Composer event ...
        if ($event !== null) {
            $arguments = $event->getArguments();
        } else {
            $arguments = $_SERVER['argv'];
        }

        // Check for strict mode
        $strict = in_array('--strict', $arguments);

        $arguments = new \cli\Arguments(compact('strict'), $arguments);
        $arguments->addFlag(array('verbose', 'v'), 'Turn on verbose output');
        $arguments->addFlag(array('version', 'V'), 'Display the version');
        $arguments->addFlag(array('quiet',   'q'), 'Disable all output');
        $arguments->addFlag(array('help',    'h'), 'Show this help screen');

        // Parse the arguments
        $arguments->parse();

        // Store arguments ...
        self::$arguments = $arguments;

        if (isset(self::$arguments['help']) === true) {
            self::showHelp();
        }
    }

    /**
     * Shows help dialog.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @static
     */
    protected static function showHelp()
    {
        \cli\line();
        \cli\out(self::$arguments->getHelpScreen(\cli\Colors::colorize('%N%n%gAvailable commands:')));
        \cli\line();
    }

    /**
     * Show project banner.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @static
     */
    protected static function showBanner()
    {
        \cli\line('%N%n%y');
        \cli\line('Installer');
        \cli\line('%N%n');
    }

    /**
     * Echoes a VHost skeleton with correct path inserted.
     *
     * @param string $installPath The install path to insert.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @static
     */
    protected static function showVhostExample($installPath)
    {
        \cli\line();
        \cli\line('You could use this skeleton (example and not production ready!) for your vhost:');
        \cli\line();
        \cli\line(\cli\Colors::colorize('%y<VirtualHost *:80>'));
        \cli\line('    ServerName www.example.com:80');
        \cli\line('    ServerAlias example.com *.example.com');
        \cli\line('    ServerAdmin webmaster@example.com');
        \cli\line('    DocumentRoot "' . $installPath . 'web"');
        \cli\line('    <Directory "' . $installPath . 'web">');
        \cli\line('        Options Indexes FollowSymLinks Includes ExecCGI');
        \cli\line('        AllowOverride All');
        \cli\line('        Order allow,deny');
        \cli\line('        Allow from all');
        \cli\line('        DirectoryIndex app.php index.php index.html index.htm');
        \cli\line('    </Directory>');
        \cli\line('</VirtualHost>');
    }

    /**
     * Validates a path for installation.
     *
     * @param string $path The path to validate
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return bool TRUE if path is valid, otherwise FALSE
     * @access protected
     * @throws Exception
     */
    protected static function validatePath($path)
    {
        if (realpath($path) === false) {
            throw new Exception(
                'Path "' . $path . '" does not exist.'
            );
        }

        if (is_dir($path) === false || is_writable($path) === false) {
            throw new Exception(
                'Make sure path "' . $path . '" exists and that it\'s writable.'
            );
        }

        // Make full usable with trailing slash
        $path = realpath($path) . DIRECTORY_SEPARATOR;

        return $path;
    }
}
