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
 * Installer.php - Installer for phpMemAdmin. This installer automate the single
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
 * @subpackage Clickalicious_PhpMemAdmin_Installer
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2014 - 2015 Benjamin Carl
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @version    Git: $Id$
 * @link       https://github.com/clickalicious/phpMemAdmin
 */

use Composer\Script\CommandEvent;

require_once 'Exception.php';
require_once 'BaseInstaller.php';

use \Clickalicious\PhpMemAdmin\Exception;
use \Clickalicious\PhpMemAdmin\BaseInstaller;

define(
    'PROJECT_INSTALLER_VERSION',
    '$Id$'
);

/**
 * Installer for phpMemAdmin. This installer automate the single
 * steps of symlink folders to document root. On Windows the symlink functionality
 * requires administrator privileges on *nix platforms you will need the right
 * to symlink to the folder in general.
 *
 * @category   Clickalicious
 * @package    Clickalicious_PhpMemAdmin
 * @subpackage Clickalicious_PhpMemAdmin_Installer
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2014 - 2015 Benjamin Carl
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @version    Git: $Id$
 * @link       https://github.com/clickalicious/phpMemAdmin
 * @see        https://github.com/wp-cli/php-cli-tools
 */
class Installer extends BaseInstaller
{
    /**
     * Default folders we install.
     *
     * @var array
     * @access protected
     * @static
     */
    protected static $folders = array(
        'app',
        'web',
        'bin',
    );

    /**
     * The project name or slug in full length.
     *
     * @var string
     * @access public
     * @const
     */
    const PROJECT_NAME = 'phpMemAdmin - Bringing Memcached to the web';

    /**
     * The project short name.
     *
     * @var string
     * @access public
     * @const
     */
    const PROJECT_NAME_SHORT = 'phpMemAdmin';


    /**
     * Installer process for project based on post install event hook on composer.
     *
     * @param CommandEvent $event The event passed in by Composer.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean|null TRUE on success, otherwise FALSE (signal for Composer to resolve with error)
     * @access public
     * @static
     */
    public static function postInstall(CommandEvent $event)
    {
        // Detect path to composer.json
        self::setInstallPath(
            self::retrieveInstallPath()
        );

        // We must include autoloader - funny.
        require_once self::getInstallPath() . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        // Store extra from composer
        self::setExtra(
            $event->getComposer()->getPackage()->getExtra()
        );

        // Force colors
        \cli\Colors::enable();

        // Process event
        return self::handleEvent($event);
    }

    /**
     * Handles a received event - dispatcher.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean|null TRUE on success, otherwise FALSE
     * @access protected
     */
    protected static function handleEvent(CommandEvent $event)
    {
        $valid = false;

        // Construct menus ...
        $menu1 = array(
            'install' => \cli\Colors::colorize('%N%gInstall ' . self::PROJECT_NAME_SHORT . '%N'),
            'quit'    => \cli\Colors::colorize('%N%rQuit %N'),
        );
        $menu2 = \cli\Colors::colorize('%NInstall to %g' . self::getInstallPath() . ' %N');
        $menu3 = array(
            'change' => \cli\Colors::colorize('%N%gChange path to install%N'),
            'quit'   => \cli\Colors::colorize('%N%rQuit%N'),
        );
        $menu4 = 'Enter path';

        // Show the big old school banner - yeah i like :)
        self::showBanner();

        // Retrieve and store arguments
        self::initArguments($event);

        // show Version information
        self::showVersion();

        // Begin CLI loop
        while (true) {
            // Ask for OK for install in general ...
            $resolved = self::resolveTree($menu1, 'install');
            if ($resolved === 'quit') {
                break;

            } else {

                // Ask if autodetected install path is ok ...
                $resolved = self::resolveChoice($menu2);

                if ($resolved === 'y') {
                    // Try to validate and use the auto detected path ...
                    try {
                        $path  = self::validatePath(self::getInstallPath());
                        $valid = true;

                    } catch (Exception $e) {
                        return self::showError($e->getMessage());
                    }

                    // If operation failed above -> Ask user for alternative path to install
                    if ($valid !== true) {
                        self::showError('Automatic detected path seems to be invalid. Please choose another path!');
                        $path = self::askAlternatePath($menu4);
                    }

                } else {
                    // Check for alternate path ...
                    $resolved = self::resolveTree($menu3, 'change');

                    // If user decided to change the path
                    if ($resolved === 'change') {
                        // If operation failed above -> Ask user for path to install
                        $valid = self::askAlternatePath($menu4);

                    } else {
                        // Quit
                        break;
                    }
                }

                // Check if alternate path also failed in case of exception ...
                if ($valid === true) {
                    if (self::install(self::getInstallPath() . DIRECTORY_SEPARATOR) === true) {
                        self::showSuccess();
                        self::showVhostExample(self::getInstallPath() . DIRECTORY_SEPARATOR);
                        self::showOutro(self::getInstallPath());

                    } else {
                        self::showFailed();
                    }
                }
            }

            // OK, continue on to composer install
            return $valid;
        }

        // Something failed and we ended up here.
        return false;
    }

    /**
     * @param string $menu
     */
    protected static function askAlternatePath($menu)
    {
        $valid = false;
        while ($valid === false) {
            try {
                self::setInstallPath(
                    self::validatePath(
                        \cli\prompt($menu, $default = false, $marker = ': ')
                    )
                );

                return true;

            } catch (Exception $e) {
                self::showError($e->getMessage());
            }
        }
    }

    /**
     * Shows the success message after install was successful.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean
     * @access protected
     * @static
     */
    protected static function showSuccess()
    {
        \cli\line();
        \cli\line(
            \cli\Colors::colorize('%N%n%gInstallation of %y' . self::PROJECT_NAME_SHORT . '%g was successful.%N%n')
        );

        return true;
    }

    /**
     * @param string $message
     */
    protected static function showError($message)
    {
        \cli\line();
        \cli\err(
            \cli\Colors::colorize('%N%n%1' . $message . '%N%n')
        );

        return false;
    }

    /**
     * @param string $menu
     */
    protected static function resolveChoice($menu, $choices = 'yn', $default = 'y')
    {
        $choice = false;
        while ($choice === false) {
            $choice = \cli\choose($menu, $choices, $default);
        }
        \cli\line();

        return $choice;
    }

    protected static function resolveTree($menu, $default = 'quit', $text = 'Your choice:')
    {
        $choice = false;
        while ($choice === false) {
            $choice = \cli\menu($menu, $default, \cli\Colors::colorize($text));
        }
        \cli\line();

        return $choice;
    }

    /**
     * Shows the failed message after install failed.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @static
     */
    protected static function showFailed()
    {
        \cli\line();
        \cli\line(
            \cli\Colors::colorize('%N%n%1Installation of ' . self::PROJECT_NAME_SHORT . ' failed.%N%n')
        );
    }

    /**
     * Shows the outro message after install succeeded to inform about management console.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @static
     */
    protected static function showOutro($projectRoot = 'n.a.')
    {
        \cli\line();
        \cli\line(\cli\Colors::colorize(
            '%nEnjoy your installation of %y' . self::PROJECT_NAME_SHORT . ' (document root: "' . $projectRoot . '")%n')
        );
        \cli\line();
    }

    /**
     * Installs the folders required for the bootstrap project from repo to project folder.
     *
     * @param string $targetDirectory The directory where to put the files/folders.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return bool TRUE on success, otherwise FALSE
     * @access protected
     * @static
     */
    protected static function install($targetDirectory)
    {
        $notify = new \cli\notify\Spinner(\cli\Colors::colorize('%N%n%yInstalling ...%N%n'), 100);

        // Define source & destination
        $source      = self::getSourcePath();
        $destination = $targetDirectory;

        // Iterate and copy ...
        foreach (self::getFolders() as $folder) {
            self::xcopy($source . $folder, $destination . $folder);
        }

        $notify->finish();

        return true;
    }

    /**
     * Setter for folders.
     *
     * @param array $folders The folders to set.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected static function setFolders(array $folders = array())
    {
        self::$folders = $folders;
    }

    /**
     * Getter for folders.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The folders.
     * @access protected
     */
    protected static function getFolders()
    {
        return self::$folders;
    }

    /**
     * Validates a path for installation.
     *
     * @param string $path The path to validate
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string TRUE if path is valid, otherwise FALSE
     * @access protected
     * @throws Exception
     */
    protected static function validatePath($path)
    {
        // Validate path by default logic
        $path = parent::validatePath($path);

        // Collection of existing folders for error message
        $existingFolders = array();

        // Check now if any of the target directories exists
        foreach (self::getFolders() as $folder) {
            if (file_exists($path . $folder)) {
                $existingFolders[] =  $folder . '/';
            }
        }

        // Any folder found? => Exception
        if (count($existingFolders) > 0) {
            throw new Exception(
                'The target directory contains the following files/folders already: ' .
                implode(' & ', $existingFolders) . '.' . PHP_EOL . 'Remove those files/folders first and try again.' .
                PHP_EOL
            );
        }

        return $path;
    }
}
