<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Clickalicious\PhpMemAdmin;

/**
 * phpMemAdmin
 *
 * App.php - Core of phpMemAdmin. Responsible for aggregating data via
 * Memcached.php. Dispatching requests and rendering views from templates.
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
 * @subpackage Clickalicious_PhpMemAdmin_App
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2014 - 2015 Benjamin Carl
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @version    Git: $Id$
 * @link       https://github.com/clickalicious/phpMemAdmin
 */

require_once 'Exception.php';

use \Clickalicious\PhpMemAdmin\Exception;
use \Clickalicious\Memcached\Client;

/**
 * phpMemAdmin
 *
 * Core of phpMemAdmin. Responsible for aggregating data via
 * Memcached.php. Dispatching requests and rendering views from templates.
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
class App
{
    /**
     * The app title.
     *
     * @var string
     * @access protected
     */
    protected $title;

    /**
     * Master instance of Memcached client for cloning from.
     *
     * @var \Clickalicious\Memcached\Client
     * @access protected
     */
    protected $client;

    /**
     * The collection of clients instantiated and used by the current run.
     *
     * @var array
     * @access protected
     */
    protected $clients = array();

    /**
     * Whether authentication is required.
     *
     * @var bool
     * @access protected
     */
    protected $authentication = false;

    /**
     * The action currently processed : defaults to 1
     *
     * @var int
     * @access protected
     */
    protected $action;

    /**
     * Credentials valid for access.
     *
     * @var array
     * @access protected
     */
    protected $credentials = array();

    /**
     * The list of memcached hosts.
     *
     * @var array
     * @access protected
     */
    protected $hosts = array();

    /**
     * The list of memcached hosts.
     *
     * @var array
     * @access protected
     */
    protected $defaultTemplateVariables = array(
        'v' => self::VERSION
    );

    /**
     * The connections already established.
     *
     * @var array
     * @access protected
     * @static
     */
    protected static $connections = array();

    /**
     * The last error's message.
     *
     * @var string
     * @access protected
     */
    protected $error = false;

    /**
     * An info message
     *
     * @var string
     * @access protected
     */
    protected $info = false;

    /**
     * An success message
     *
     * @var string
     * @access protected
     */
    protected $success = false;

    /**
     * The format used when displaying date/time values.
     *
     * @var string
     * @access protected
     */
    protected $dateFormat = self::DEFAULT_DATEFORMAT;

    /**
     * Base URL required for building correct URLs.
     * Normally no need to be modified. But if App is run from outside a vhost that maps "/web" to "/"
     * then it is required to inform the App about the new path/URL.
     *
     * @example If the App is accessed by http://127.0.0.1/projects/phpMemAdmin/web then you should set the
     *          baseUrl to "/projects/phpMemAdmin/web/" instead of default "/" and everything should work fine.
     *
     * @var string
     * @access protected
     */
    protected $baseUrl = self::DEFAULT_BASEURL;

    /**
     * The configuration.
     *
     * @var \stdClass
     * @access protected
     */
    protected $config;

    /**
     * Error Messages.
     *
     * @var array
     * @access protected
     */
    protected $errorMessages = array(
          1 => 'Could not create key "%s" with value "%s".',                                   // (C)reate
          2 => 'Could not read key "%s"',                                                      // (R)ead
          3 => 'Could not update key "%s"',                                                    // (U)pdate
          4 => 'Could not delete key',                                                         // (D)elete
          5 => 'Could not increment key "%s" by "%s"',                                         // Increment
          6 => 'Could not decrement key "%s" by "%s"',                                         // Decrement
          7 => 'Could not prepend value to key "%s"',                                          // Prepend
          8 => 'Could not append value to key "%s"',                                           // Append
          9 => 'Could not flush keys.',                                                        // Flush
        100 => 'Unknown error occurred while processing request. Stacktrace: <code>%s</code>', // Generic (exceptions)
    );

    /**
     * The name of the cluster.
     *
     * @var string
     * @access protected
     */
    protected $cluster;

    /**
     * Error create
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_CREATE = 1;

    /**
     * Error read
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_READ = 2;

    /**
     * Error update
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_UPDATE = 3;

    /**
     * Error delete
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_DELETE = 4;

    /**
     * Error increment
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_INCREMENT = 5;

    /**
     * Error decrement
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_DECREMENT = 6;

    /**
     * Error prepend
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_PREPEND = 7;

    /**
     * Error append
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_APPEND = 8;

    /**
     * Error flush
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_FLUSH = 9;

    /**
     * Generic error also used for exceptions.
     *
     * @var int
     * @access public
     * @const
     */
    const ERROR_GENERIC = 100;

    /**
     * The default and original application title.
     * Can be overwritten when used as whitelabel integration.
     *
     * @var string
     * @access public
     * @const
     */
    const DEFAULT_TITLE = 'phpMemAdmin - Bringing Memcached to the web';

    /**
     * The default terminator for memcached commands.
     *
     * @var string
     * @access public
     * @const
     */
    const TERMINATOR = "\r\n";

    /**
     * Separator used to separate commands send to Memcached instance.
     *
     * @var string
     * @access public
     * @const
     */
    const SEPARATOR = ' ';

    /**
     * The command for retrieving the version from a memcached instance.
     *
     * @var string
     * @access public
     * @const
     */
    const MEMCACHE_COMMAND_VERSION = 'version';

    /**
     * The command for retrieving the stats from a memcached instance.
     *
     * @var string
     * @access public
     * @const
     */
    const MEMCACHE_COMMAND_STATS = 'stats';

    /**
     * The command for setting a key value pair to a memcached instance.
     *
     * @var string
     * @access public
     * @const
     */
    const MEMCACHE_COMMAND_SET = 'set';

    /**
     * The command for deleting a key value pair from a memcached instance.
     *
     * @var string
     * @access public
     * @const
     */
    const MEMCACHE_COMMAND_DELETE = 'delete';

    /**
     * The default base URL.
     * @example If installed like www.testdomain.com/
     *
     * @var string
     * @access public
     * @const
     */
    const DEFAULT_BASEURL = '/';

    /**
     * The argument in URL containing the action to process
     *
     * @var string
     * @access public
     * @const
     */
    const ARGUMENT_ACTION = 'action';

    /**
     * The name of the argument containing the desired host.
     *
     * @var string
     * @access public
     * @const
     */
    const ARGUMENT_HOST = 'host';

    /**
     * The default date format to format date values.
     *
     * @example ISO 8601 with UTC shift : 2014-12-31T01:00:00+1:00
     *
     * @var string
     * @access public
     * @const
     */
    const DEFAULT_DATEFORMAT = 'Y-m-d\TH:i:s';

    /**
     * Type for slabs.
     *
     * @var string
     * @access public
     * @const
     */
    const MEMCACHE_TYPE_SLABS = 'slabs';

    /**
     * Type for items.
     *
     * @var string
     * @access public
     * @const
     */
    const MEMCACHE_TYPE_ITEMS = 'items';

    /**
     * Type for cachedump.
     *
     * @var string
     * @access public
     * @const
     */
    const MEMCACHE_TYPE_CACHEDUMP = 'cachedump';

    /**
     * The current version following the semver versioning schema:
     * http://semver.org/
     *
     * @var string
     * @access public
     * @const
     */
    const VERSION = '0.1.0';

    /**
     * Action for: Cluster-Health (index/home) page which shows details
     * about the whole cluster (all configured hosts) and accumulated details.
     *
     * @var int
     * @access public
     * @const
     */
    const ACTION_DASHBOARD = 1;

    /**
     * Action for: Host-Statistics page which shows details about
     * a single host and its uptime, memory status and so on.
     *
     * @var int
     * @access public
     * @const
     */
    const ACTION_HOST_DETAILS = 2;

    /**
     * Action for: Data-Management page which provides CRUD control for
     * stored data.
     *
     * @var int
     * @access public
     * @const
     */
    const ACTION_DATA_MANAGEMENT = 4;

    /**
     * Action for: Memory-Details page which shows how memory is used and
     * how slabs are used by memcached.
     *
     * @var int
     * @access public
     * @const
     */
    const ACTION_MEMORY_DETAILS = 8;

    /**
     * Action for: Logout & Invalidate credentials
     *
     * @var int
     * @access public
     * @const
     */
    const ACTION_LOGOUT = 16;

    /**
     * Action for: Do nothing but show some custom message
     *
     * @param int
     * @access public
     * @const
     */
    const ACTION_NEUTRAL = 32;

    /**
     * Action for: Do nothing but show some custom message
     *
     * @param int
     * @access public
     * @const
     */
    const ACTION_ABOUT = 64;

    /**
     * Unsigned int 32 bit.
     *
     * @var int
     * @access public
     * @const
     */
    const UINT_32 = 32;

    /**
     * Unsigned int 64 bit.
     *
     * @var int
     * @access public
     * @const
     */
    const UINT_64 = 64;

    /**
     * The URL where to fetch version from.
     *
     * @var string
     * @access public
     * @const
     */
    const MEMCACHED_VERSION_URL = 'http://www.memcached.org/';

    /**
     * TOP cluster condition message
     *
     * @var string
     * @access public
     * @const
     */
    const CLUSTER_HEALTH_TOP_MESSAGE = '{{clusterName}} is in perfect condition!';

    /**
     * TOP cluster condition color
     *
     * @var string
     * @access public
     * @const
     */
    const CLUSTER_HEALTH_TOP_COLOR = 'green';

    /**
     * GOOD cluster condition message
     *
     * @var string
     * @access public
     * @const
     */
    const CLUSTER_HEALTH_NOTICE_MESSAGE = '{{clusterName}} seems to be in a good condition.';

    /**
     * GOOD cluster condition color
     *
     * @var string
     * @access public
     * @const
     */
    const CLUSTER_HEALTH_NOTICE_COLOR   = 'lightgreen';

    /**
     * WARNING cluster condition message
     *
     * @var string
     * @access public
     * @const
     */
    const CLUSTER_HEALTH_WARNING_MESSAGE = '{{clusterName}} should be checked for issues.';

    /**
     * WARNING cluster condition color
     *
     * @var string
     * @access public
     * @const
     */
    const CLUSTER_HEALTH_WARNING_COLOR = 'orange';

    /**
     * ERROR cluster condition message
     *
     * @var string
     * @access public
     * @const
     */
    const CLUSTER_HEALTH_ERROR_MESSAGE = '{{clusterName}} is in critical state!';

    /**
     * ERROR cluster condition color
     *
     * @var string
     * @access public
     * @const
     */
    const CLUSTER_HEALTH_ERROR_COLOR = 'red';


    /**
     * Constructor.
     *
     * @param \stdClass                       $config     The configuration containing all preset values at least.
     * @param \Clickalicious\Memcached\Client $client     The master client instance used for cloning instances.
     * @param string                          $baseUrl    The base URL of the application
     * @param string                          $dateFormat The date format used for formatting dates
     * @param string                          $title      The title of the application
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return App
     * @access public
     */
    public function __construct(
        \stdClass $config,
        Client    $client,
                  $baseUrl    = self::DEFAULT_BASEURL,
                  $dateFormat = self::DEFAULT_DATEFORMAT,
                  $title      = self::DEFAULT_TITLE
    ) {
        $this
            ->title($title)                                             // Set title of app for rendering, logging ...
            ->credentials($config->username, $config->password)         // Set credentials required (or NULL!)
            ->authenticate()                                            // Validate credentials
            ->client($client)                                           // Set the client used to talk to Memcached
            ->dateFormat($dateFormat)                                   // Set format of date/time values
            ->baseUrl($baseUrl)                                         // Set the base URL to the app
            ->hosts($config->cluster->hosts)                            // Set the hosts maintained by this app
            ->cluster($config->cluster->name)                           // Name of the active cluster
            ->config($config);                                          // Store configuration
    }

    /**
     * Returns a collection in logical order of templates required for passed action.
     *
     * @param int $action The action to return templates for
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array An collection of templates in logical order
     * @access protected
     */
    protected function actionToTemplates($action)
    {
        switch ($action) {
            case self::ACTION_DASHBOARD:
                $templates = array(
                    'header',
                    'content-cluster-dashboard',
                    'footer'
                );
                break;

            case self::ACTION_HOST_DETAILS:
                $templates = array(
                    'header',
                    'content-host-dashboard',
                    'footer'
                );
                break;

            case self::ACTION_DATA_MANAGEMENT:
                $templates = array(
                    'header',
                    'content-data-management',
                    'footer'
                );
                break;

            case self::ACTION_ABOUT:
                $templates = array(
                    'header',
                    'content-about',
                    'footer'
                );
                break;

            default:
                $templates = array(
                    '403',
                );
                break;
        }

        return $templates;
    }

    /**
     * Loads a template from filesystem.
     *
     * @param string|array $templates A single template as string or a collection as array
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string Content of template(s) as string
     * @access protected
     */
    protected function loadTemplates($templates)
    {
        if (is_array($templates) === false) {
            $templates = str_replace('/',  DIRECTORY_SEPARATOR, $templates);
            $templates = str_replace('\\', DIRECTORY_SEPARATOR, $templates);
            $templates = array($templates);
        }

        $result = '';

        foreach ($templates as $template) {
            $result .= file_get_contents(CLICKALICIOUS_PHPMEMADMIN_BASE_PATH . 'app/templates/' . $template . '.tpl');
        }

        return $result;
    }

    /**
     * Renders a template.
     *
     * @param string $html      The html of the template
     * @param array  $variables The template vars used for rendering (key => value)
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string Rendered HTML as string
     * @access protected
     */
    protected function renderTemplate($html, array $variables = array())
    {
        $variables = array_merge(
            $variables,
            $this->getDefaultTemplateVariables()
        );

        return $this->renderString($html, $variables);
    }

    /**
     * Renders the content of $string with variables from $variables.
     *
     * @param string $string    The html of the template
     * @param array  $variables The template vars used for rendering (key => value)
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The rendered string
     * @access protected
     */
    protected function renderString($string, array $variables = array())
    {
        foreach ($variables as $key => $value) {
            $string = str_replace('{{' . $key . '}}', $value, $string);
        }

        return $string;
    }

    /**
     * Returns the default template variables.
     * This method merges the pre-defined variables (defaultTemplateVariables)
     * with runtime and date/time information to a final collection of template variables.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The default template variables
     * @access protected
     */
    protected function getDefaultTemplateVariables()
    {
        $title = $this->getTitle();
        $titleShort = explode(' ', $title);
        $activeHost = $this->getActiveHost(true);

        $variables                = $this->defaultTemplateVariables;
        $variables['menu']        = $this->getMenuHtml();
        $variables['title']       = $title;
        $variables['titleShort']  = $titleShort[0];
        $variables['clusterName'] = $this->getCluster();
        $variables['uri']         = $_SERVER['PHP_SELF'] . '?action=' . $this->getAction() . '&host=' . $this->getActiveHost();
        $variables['hostFull']    = $this->getActiveHost();
        $variables['host']        = $activeHost[0];
        $variables['port']        = $activeHost[1];
        $variables['year']        = date('Y');
        $variables['php']         = phpversion();

        return $variables;
    }

    /**
     * Parser for arguments passed to this instance.
     * Parses arguments and stores the values.
     *
     * @param array $arguments The arguments to parse
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function parseArguments(array $arguments)
    {
        $this->setAction(
            (isset($arguments[self::ARGUMENT_ACTION]) === true) ? (int)$arguments[self::ARGUMENT_ACTION] : 1
        );
    }

    /**
     * Processes the operations passed with the current request.
     *
     * @param int   $action    The action we are running
     * @param array $arguments The arguments to parse for commands
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     * @throws \Exception
     */
    protected function processRequestOperations($action, array $arguments)
    {
        // Get the current active host.
        $host   = $this->getActiveHost(true);
        $result = null;
        $error  = null;
        $anchor = '';

        // Try to execute the operation ...
        try {
            if (
                isset($arguments['set']) === true &&
                $arguments['set'] = strip_tags($arguments['set'])
            ) {
                // Check required minimum input
                if (isset($arguments['value']) !== true) {
                    throw new \Exception(
                        sprintf('Cannot set key "%s" without value!', $arguments['set'])
                    );
                }

                $value = $this->castAsPhpType($arguments['value']);

                if (true !== $result = $this
                    ->getMemcachedClient($host[0], $host[1], $this->getConfig()->timeout)
                    ->set($arguments['set'], $value)
                ) {
                    $error = $this->errorContext(
                        self::ERROR_CREATE,
                        array(
                            $arguments['set'],
                            $arguments['value']
                        )
                    );
                } else {
                    $anchor = '#' . $arguments['set'];
                }

            } elseif (
                isset($arguments['replace']) === true &&
                $arguments['replace'] = strip_tags($arguments['replace'])
            ) {
                // Check required minimum input
                if (isset($arguments['value']) !== true) {
                    throw new \Exception(
                        sprintf('Cannot set key "%s" without value!', $arguments['set'])
                    );
                }

                $value = $this->castAsPhpType($arguments['value']);

                if (true !== $result = $this
                    ->getMemcachedClient($host[0], $host[1], $this->getConfig()->timeout)
                    ->replace($arguments['replace'], $value)
                ) {
                    $error = $this->errorContext(
                        self::ERROR_UPDATE,
                        array($arguments['replace'])
                    );
                } else {
                    $anchor = '#' . $arguments['replace'];
                }

            } elseif (
                isset($arguments['append']) === true &&
                $arguments['append'] = strip_tags($arguments['append'])
            ) {
                if (true !== $result = $this
                    ->getMemcachedClient($host[0], $host[1], $this->getConfig()->timeout)
                    ->append($arguments['append'], $arguments['value'])
                ) {
                    $error = $this->errorContext(
                        self::ERROR_APPEND,
                        array($arguments['append'])
                    );
                } else {
                    $anchor = '#' . $arguments['append'];
                }

            } elseif (
                isset($arguments['prepend']) === true &&
                $arguments['prepend'] = strip_tags($arguments['prepend'])
            ) {
                if (true !== $result = $this
                    ->getMemcachedClient($host[0], $host[1], $this->getConfig()->timeout)
                    ->prepend($arguments['prepend'], $arguments['value'])
                ) {
                    $error = $this->errorContext(
                        self::ERROR_PREPEND,
                        array($arguments['prepend'])
                    );
                } else {
                    $anchor = '#' . $arguments['prepend'];
                }

            } elseif (
                isset($arguments['increment']) === true &&
                $arguments['increment'] = strip_tags($arguments['increment'])
            ) {
                $value = $this->castAsPhpType($arguments['value']);

                if (true !== is_float($result = $this
                    ->getMemcachedClient($host[0], $host[1], $this->getConfig()->timeout)
                    ->incr($arguments['increment'], $value))
                ) {
                    $error = $this->errorContext(
                        self::ERROR_INCREMENT,
                        array($arguments['increment'])
                    );
                } else {
                    $anchor = '#' . $arguments['increment'];
                    $result = true;
                }

            } elseif (
                isset($arguments['decrement']) === true &&
                $arguments['decrement'] = strip_tags($arguments['decrement'])
            ) {
                $value = $this->castAsPhpType($arguments['value']);

                if (true !== is_float($result = $this
                    ->getMemcachedClient($host[0], $host[1], $this->getConfig()->timeout)
                    ->decr($arguments['decrement'], $value))
                ) {
                    $error = $this->errorContext(
                        self::ERROR_DECREMENT,
                        array($arguments['decrement'])
                    );
                } else {
                    $anchor = '#' . $arguments['decrement'];
                    $result = true;
                }

            } elseif (
                isset($arguments['delete']) === true &&
                $arguments['delete'] = strip_tags($arguments['delete'])
            ) {
                if (true !== $result = $this
                    ->getMemcachedClient($host[0], $host[1], $this->getConfig()->timeout)
                    ->delete($arguments['delete'])
                ) {
                    $error = $this->errorContext(
                        self::ERROR_DELETE,
                        array($arguments['delete'])
                    );
                }
            } elseif (
                isset($arguments['flush']) === true &&
                $arguments['flush'] = strip_tags($arguments['flush'])
            ) {
                if (true !== $result = $this
                    ->getMemcachedClient($host[0], $host[1], $this->getConfig()->timeout)
                    ->flush()
                ) {
                    $error = $this->errorContext(
                        self::ERROR_FLUSH
                    );
                }
            }

        } catch (\Clickalicious\Memcached\Exception $e) {
            $error = $this->errorContext(
                self::ERROR_GENERIC,
                array(var_export($e, true))
            );

        }

        //
        if ($error !== null) {
            $this->setError(
                vsprintf($this->getErrorMessageByNumber($error['number']), $error['context'])
            );
        }

        // Result = true = some operation done and redirect required!
        if ($result === true) {

            // So redirect to clean state self action
            $this->redirect(
                $this->getUrl(
                    array(
                        'action' => $action,
                        'host'   => $this->getActiveHost(),
                    ),
                    '',
                    $anchor
                )
            );
        }
    }

    /**
     * Returns an error context array which contains an error-number and variables related to the error.
     *
     * @param int   $errorNumber The error number
     * @param array $context     The related variables
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array An error context array containing key number and context
     * @access protected
     */
    protected function errorContext($errorNumber, array $context = array())
    {
        return array(
            'number'  => $errorNumber,
            'context' => $context,
        );
    }

    /**
     * Returns the error for the passed number if set, otherwise the string 'n.a.'.
     *
     * @param int $number The number to return error for
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The error message
     * @access protected
     */
    protected function getErrorMessageByNumber($number)
    {
        return (isset($this->errorMessages[$number]) === true) ? $this->errorMessages[$number] : 'n.a.';
    }

    /**
     * Returns the stored data from a Memcached instance.
     *
     * @param string $host The host to return data from
     * @param int    $port The port the Memcached daemon is listening on
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The data requested
     * @access protected
     */
    protected function retrieveStoredData($host, $port)
    {
        $result = array();

        // But at the end we will always fetch data ...
        try {
            $result = $this->dumpEntries($host, $port, null, true);

        } catch (\Exception $e) {
            $this->setError($e->getMessage());
        }

        return $result;
    }

    /**
     * Renders the result (HTML5 output) for passed $route and $arguments.
     * The application is split in this way so we are able to execute the whole
     * stack painless with mock data for unit tests and stuff like that.
     *
     * @param string $route     The route running
     * @param array  $arguments Arguments from request
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The rendered HTML(5) template ready for delivery
     * @access public
     */
    public function render($route = self::DEFAULT_BASEURL, array $arguments = array())
    {
        // Prepare argument input ...
        $this->parseArguments($arguments);
        $action = $this->getAction();
        $this->processRequestOperations($action, $arguments);

        // Now check for action
        switch ($action) {
            case self::ACTION_DASHBOARD:
            case self::ACTION_HOST_DETAILS:
            case self::ACTION_DATA_MANAGEMENT:
            case self::ACTION_MEMORY_DETAILS:
            case self::ACTION_ABOUT:
                break;

            case self::ACTION_LOGOUT:
                header('Location: ' . $this->getLinkForAction(self::ACTION_NEUTRAL));
                exit;

            default:
                // Intentionally left blank
                break;
        }

        // send headers
        header('Content-Type: text/html; charset=utf-8');

        // Return rendered HTML(5)
        return $this->renderTemplate(
            $this->loadTemplates($this->actionToTemplates($action)),
            array(
                'content' => $this->getContent($action),
                'message' => $this->getMessages()
            )
        );
    }

    /*-----------------------------------------------------------------------------------------------------------------+
    | Internal tools & helper
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Retrieves the messages to be shown in rendered page.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The message HTML string
     * @access protected
     */
    protected function getMessages()
    {
        $message = $this->renderTemplate(
            $this->loadTemplates('message'),
            array(
                'message' => $this->getErrorMessageHtml() . $this->getHtmlInfoMessage() . $this->getHtmlSuccessMessage()
            )
        );

        return $message;
    }

    /**
     * Returns an instance of Client for host and port combination.
     *
     * @param string $host    The host to return instance for
     * @param int    $port    The port to return instance for
     * @param int    $timeout The timeout used when connecting
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return \Clickalicious\Memcached\Client A client instance
     * @access protected
     */
    protected function getMemcachedClient($host, $port = Client::DEFAULT_PORT, $timeout = null)
    {
        $uuid    = $this->uuid($host, $port);
        $clients = $this->getClients();

        // Check if already exists ...
        if (isset($clients[$uuid]) === false) {
            $client = clone $this->getClient();

            $client
                ->host($host)
                ->port($port);

            // Check for custom timeout (maybe required when connecting outside localhost = more latency)
            if (null !== $timeout && true === is_int($timeout) && $timeout >= 0) {
                $client
                    ->timeout($timeout);
            }

            $clients[$uuid] = $client;
            $this->setClients($clients);
        } else {
            $client = $this->clients[$uuid];
        }

        return $client;
    }

    /**
     * Returns currently active host. If no host is active, the first one found in list
     * of servers is the active one.
     *
     * @param bool $asArray TRUE to return the host as prepared array, otherwise FALSE to return as string (default).
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The active host name or ip
     * @access public
     */
    public function getActiveHost($asArray = false)
    {
        $host = $this->getHostFromRequest();

        if ($host === null) {
            // Get host from config
            $hosts = $this->getHosts();
            $host  = $hosts[0];
        }

        return ($asArray === true) ? explode(':', $host) : $host;
    }

    /**
     * Returns host from request if exist, otherwise NULL.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string,null The active host name or ip if set, otherwise NULL
     * @access protected
     */
    protected function getHostFromRequest()
    {
        $host = null;

        if (isset($_GET['host']) && $_GET['host'] !== '' && (in_array($_GET['host'], $this->getHosts()) === true)) {
            // Get host from request
            $host = $_GET['host'];
        }

        return $host;
    }

    /**
     * Returns uptime converted to years, month, days, hours ...
     *
     * @param int $timestamp The timestamp to parse
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The result
     * @access protected
     */
    protected function convertUptime($timestamp)
    {
        // Factors
        $second = 1;
        $minute = 60;
        $hour   = 60     * 60;
        $day    = $hour  * 24;
        $month  = $day   * 30;
        $year   = $month * 12;

        $rest = $timestamp;

        // Convert ...
        $years   = floor($rest / $year);
        $rest   -= ($years * $year);
        $months  = floor($rest / $month);
        $rest   -= ($months * $month);
        $days    = floor($rest / $day);
        $rest   -= ($days * $day);
        $hours   = floor($rest / $hour);
        $rest   -= ($hours * $hour);
        $minutes = floor($rest / $minute);
        $rest   -= ($minutes * $minute);
        $seconds = floor($rest / $second);

        // Result ...
        $result = array(
            'years'   => $years,
            'months'  => $months,
            'days'    => $days,
            'hours'   => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
        );

        return $result;
    }

    /**
     * Returns the inner content of the pages by action.
     *
     * @param int $action The action to return content for
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string HTML
     * @access protected
     */
    protected function getContent($action)
    {
        $html = '';

        // Generate content by action ...
        switch ($action) {

            case self::ACTION_DASHBOARD:
                $time = time();
                $data = $this->aggregateStatistics(
                    $this->getHosts()
                );

                $data['grade'] = $data['bytes'] * 100 / $data['limit_maxbytes'];

                if ($data['grade'] >= $this->getConfig()->cluster->thresholds->notice) {
                    if ($data['grade'] < $this->getConfig()->cluster->thresholds->warning) {
                        // is notice
                        $thumbColor   = self::CLUSTER_HEALTH_NOTICE_COLOR;
                        $thumbMessage = $this->renderString(
                            self::CLUSTER_HEALTH_NOTICE_MESSAGE,
                            array('clusterName' => $this->getConfig()->cluster->name)
                        );

                    } elseif ($data['grade'] < $this->getConfig()->cluster->thresholds->error) {
                        // is warning
                        $thumbColor   = self::CLUSTER_HEALTH_WARNING_COLOR;
                        $thumbMessage = $this->renderString(
                            self::CLUSTER_HEALTH_WARNING_MESSAGE,
                            array('clusterName' => $this->getConfig()->cluster->name)
                        );

                    } else {
                        // is error
                        $thumbColor   = self::CLUSTER_HEALTH_ERROR_COLOR;
                        $thumbMessage = $this->renderString(
                            self::CLUSTER_HEALTH_ERROR_MESSAGE,
                            array('clusterName' => $this->getConfig()->cluster->name)
                        );
                    }

                } else {
                    $thumbColor   = self::CLUSTER_HEALTH_TOP_COLOR;
                    $thumbMessage = $this->renderString(
                        self::CLUSTER_HEALTH_TOP_MESSAGE,
                        array('clusterName' => $this->getConfig()->cluster->name)
                    );
                }

                $this->defaultTemplateVariables['thumbColor'] = $thumbColor;
                $this->defaultTemplateVariables['thumbMessage'] = $thumbMessage;

                // Convert units for some values ...
                $data['limit_maxmbytes'] = $data['limit_maxbytes'] / 1024 / 1024;
                $data['starttime']       = date(self::DEFAULT_DATEFORMAT, $time - $data['uptime']) . '.000Z';

                // Request data
                $hitsGet      = $data['get_hits'];
                $missesGet    = $data['get_misses'];
                $hitsDelete   = $data['delete_hits'];
                $missesDelete = $data['delete_misses'];
                $hitsIncr     = $data['incr_hits'];
                $missesIncr   = $data['incr_misses'];
                $hitsDecr     = $data['decr_hits'];
                $missesDecr   = $data['decr_misses'];
                $hitsCas      = $data['cas_hits'];
                $missesCas    = $data['cas_misses'];

                $hits   = $hitsGet   + $hitsDelete   + $hitsIncr   + $hitsDecr   + $hitsCas;
                $misses = $missesGet + $missesDelete + $missesIncr + $missesDecr + $missesCas;

                $factorSecond = $data['uptime'];
                $factorMinute = $factorSecond / 60;
                $factorHour   = $factorMinute / 60;
                $factorDay    = $factorHour   / 24;

                $sets = $data['cmd_set'];

                $requestRateSeconds = sprintf('%.2f', ($hits + $misses) / $factorSecond);
                $hitRateSeconds     = sprintf('%.2f', ($hits)           / $factorSecond);
                $missesRateSeconds  = sprintf('%.2f', ($misses)         / $factorSecond);
                $setRateSeconds     = sprintf('%.2f', ($sets)           / $factorSecond);
                $requestRateMinutes = sprintf('%.2f', ($hits + $misses) / $factorMinute);
                $hitRateMinutes     = sprintf('%.2f', ($hits)           / $factorMinute);
                $missesRateMinutes  = sprintf('%.2f', ($misses)         / $factorMinute);
                $setRateMinutes     = sprintf('%.2f', ($sets)           / $factorMinute);
                $requestRateHours   = sprintf('%.2f', ($hits + $misses) / $factorHour);
                $hitRateHours       = sprintf('%.2f', ($hits)           / $factorHour);
                $missesRateHours    = sprintf('%.2f', ($misses)         / $factorHour);
                $setRateHours       = sprintf('%.2f', ($sets)           / $factorHour);
                $requestRateDays    = sprintf('%.2f', ($hits + $misses) / $factorDay);
                $hitRateDays        = sprintf('%.2f', ($hits)           / $factorDay);
                $missesRateDays     = sprintf('%.2f', ($misses)         / $factorDay);
                $setRateDays        = sprintf('%.2f', ($sets)           / $factorDay);

                $data['seconds'] = array(
                    'requestRate' => $requestRateSeconds,
                    'hitRate'     => $hitRateSeconds,
                    'missesRate'  => $missesRateSeconds,
                    'setRate'     => $setRateSeconds,
                );

                $data['minutes'] = array(
                    'requestRate' => $requestRateMinutes,
                    'hitRate'     => $hitRateMinutes,
                    'missesRate'  => $missesRateMinutes,
                    'setRate'     => $setRateMinutes,
                );

                $data['hours'] = array(
                    'requestRate' => $requestRateHours,
                    'hitRate'     => $hitRateHours,
                    'missesRate'  => $missesRateHours,
                    'setRate'     => $setRateHours,
                );

                $data['days'] = array(
                    'requestRate' => $requestRateDays,
                    'hitRate'     => $hitRateDays,
                    'missesRate'  => $missesRateDays,
                    'setRate'     => $setRateDays,
                );

                $seconds = '[';
                foreach ($data['seconds'] as $key => $value) {
                    $seconds .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $seconds .= ']';

                $minutes = '[';
                foreach ($data['minutes'] as $key => $value) {
                    $minutes .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $minutes .= ']';

                $hours = '[';
                foreach ($data['hours'] as $key => $value) {
                    $hours .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $hours .= ']';

                $days = '[';
                foreach ($data['days'] as $key => $value) {
                    $days .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $days .= ']';

                $data['seconds']       = $seconds;
                $data['minutes']       = $minutes;
                $data['hours']         = $hours;
                $data['days']          = $days;


                $data['latestVersion'] = '';
                $data['latestVersionHeight'] = 40;

                if ($this->getConfig()->updatecheck === true) {
                    try {
                        $version = $this->getMemcachedLatestVersion();

                    } catch (Exception $e) {
                        $this->setInfo($e->getMessage());
                        $version = '1.0.0';
                    }

                    if ($version !== '1.0.0' && $version < $data['version']) {
                        $data['latestVersion']       = '<span class="label label-danger pull-right"><b><a href="https://code.google.com/p/memcached/wiki/ReleaseNotes' . str_replace('.', '', $version) . '" style="color: #fff;" target="_blank">' . $version . ' available</a></b></span>';
                        $data['latestVersionHeight'] = 60;
                    }
                }

                $hits = array(
                    'get'    => $hitsGet,
                    'delete' => $hitsDelete,
                    'incr'   => $hitsIncr,
                    'decr'   => $hitsDecr,
                    'cas'    => $hitsCas,
                );

                $misses = array(
                    'get'    => $missesGet,
                    'delete' => $missesDelete,
                    'incr'   => $missesIncr,
                    'decr'   => $missesDecr,
                    'cas'    => $missesCas,
                );

                $hitsJson = '[';
                foreach ($hits as $key => $value) {
                    $hitsJson .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $hitsJson = rtrim($hitsJson, ',') . ']';

                $missesJson = '[';
                foreach ($misses as $key => $value) {
                    $missesJson .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $missesJson = rtrim($missesJson, ',') . ']';

                $data['requestHits']   = $hitsJson;
                $data['requestMisses'] = $missesJson;


                /**
                 * Render subcontent for settings table
                 */
                $subcontent = '';
                $settings   = $this->getSettings($this->getActiveHost());
                $template   = $this->loadTemplates('content-host-dashboard-settings-entity');

                foreach ($settings as $setting => $value) {
                    $templateVariables = array('key' => $setting, 'value' => $value);
                    $subcontent .= $this->renderTemplate($template, $templateVariables);
                }

                $data['subcontent'] = $subcontent;


                $template = $this->loadTemplates('content-cluster-dashboard-environment');
                $html    .= $this->renderTemplate($template, $data);
                break;

            case self::ACTION_HOST_DETAILS:
                $time = time();
                $data = $this->aggregateStatistics(
                    array(
                        $this->getActiveHost()
                    )
                );

                // Convert units for some values ...
                $data['limit_maxmbytes'] = $data['limit_maxbytes'] / 1024 / 1024;
                $data['starttime']       = date(self::DEFAULT_DATEFORMAT, $time - $data['uptime']) . '.000Z';

                // Request data
                $hitsGet      = $data['get_hits'];
                $missesGet    = $data['get_misses'];
                $hitsDelete   = $data['delete_hits'];
                $missesDelete = $data['delete_misses'];
                $hitsIncr     = $data['incr_hits'];
                $missesIncr   = $data['incr_misses'];
                $hitsDecr     = $data['decr_hits'];
                $missesDecr   = $data['decr_misses'];
                $hitsCas      = $data['cas_hits'];
                $missesCas    = $data['cas_misses'];

                $hits   = $hitsGet   + $hitsDelete   + $hitsIncr   + $hitsDecr   + $hitsCas;
                $misses = $missesGet + $missesDelete + $missesIncr + $missesDecr + $missesCas;

                $factorSecond = $data['uptime'];
                $factorMinute = $factorSecond / 60;
                $factorHour   = $factorMinute / 60;
                $factorDay    = $factorHour   / 24;

                $sets = $data['cmd_set'];

                $requestRateSeconds = sprintf('%.2f', ($hits + $misses) / $factorSecond);
                $hitRateSeconds     = sprintf('%.2f', ($hits)           / $factorSecond);
                $missesRateSeconds  = sprintf('%.2f', ($misses)         / $factorSecond);
                $setRateSeconds     = sprintf('%.2f', ($sets)           / $factorSecond);
                $requestRateMinutes = sprintf('%.2f', ($hits + $misses) / $factorMinute);
                $hitRateMinutes     = sprintf('%.2f', ($hits)           / $factorMinute);
                $missesRateMinutes  = sprintf('%.2f', ($misses)         / $factorMinute);
                $setRateMinutes     = sprintf('%.2f', ($sets)           / $factorMinute);
                $requestRateHours   = sprintf('%.2f', ($hits + $misses) / $factorHour);
                $hitRateHours       = sprintf('%.2f', ($hits)           / $factorHour);
                $missesRateHours    = sprintf('%.2f', ($misses)         / $factorHour);
                $setRateHours       = sprintf('%.2f', ($sets)           / $factorHour);
                $requestRateDays    = sprintf('%.2f', ($hits + $misses) / $factorDay);
                $hitRateDays        = sprintf('%.2f', ($hits)           / $factorDay);
                $missesRateDays     = sprintf('%.2f', ($misses)         / $factorDay);
                $setRateDays        = sprintf('%.2f', ($sets)           / $factorDay);

                $data['seconds'] = array(
                    'requestRate' => $requestRateSeconds,
                    'hitRate'     => $hitRateSeconds,
                    'missesRate'  => $missesRateSeconds,
                    'setRate'     => $setRateSeconds,
                );

                $data['minutes'] = array(
                    'requestRate' => $requestRateMinutes,
                    'hitRate'     => $hitRateMinutes,
                    'missesRate'  => $missesRateMinutes,
                    'setRate'     => $setRateMinutes,
                );

                $data['hours'] = array(
                    'requestRate' => $requestRateHours,
                    'hitRate'     => $hitRateHours,
                    'missesRate'  => $missesRateHours,
                    'setRate'     => $setRateHours,
                );

                $data['days'] = array(
                    'requestRate' => $requestRateDays,
                    'hitRate'     => $hitRateDays,
                    'missesRate'  => $missesRateDays,
                    'setRate'     => $setRateDays,
                );

                $seconds = '[';
                foreach ($data['seconds'] as $key => $value) {
                    $seconds .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $seconds .= ']';

                $minutes = '[';
                foreach ($data['minutes'] as $key => $value) {
                    $minutes .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $minutes .= ']';

                $hours = '[';
                foreach ($data['hours'] as $key => $value) {
                    $hours .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $hours .= ']';

                $days = '[';
                foreach ($data['days'] as $key => $value) {
                    $days .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $days .= ']';

                $data['seconds']       = $seconds;
                $data['minutes']       = $minutes;
                $data['hours']         = $hours;
                $data['days']          = $days;


                $data['latestVersion'] = '';
                $data['latestVersionHeight'] = 40;

                if ($this->getConfig()->updatecheck === true) {
                    try {
                        $version = $this->getMemcachedLatestVersion();

                    } catch (Exception $e) {
                        $this->setInfo($e->getMessage());
                        $version = '1.0.0';
                    }

                    if ($version !== '1.0.0' && $version < $data['version']) {
                        $data['latestVersion']       = '<span class="label label-danger pull-right"><b><a href="https://code.google.com/p/memcached/wiki/ReleaseNotes' . str_replace('.', '', $version) . '" style="color: #fff;" target="_blank">' . $version . ' available</a></b></span>';
                        $data['latestVersionHeight'] = 60;
                    }
                }

                $hits = array(
                    'get'    => $hitsGet,
                    'delete' => $hitsDelete,
                    'incr'   => $hitsIncr,
                    'decr'   => $hitsDecr,
                    'cas'    => $hitsCas,
                );

                $misses = array(
                    'get'    => $missesGet,
                    'delete' => $missesDelete,
                    'incr'   => $missesIncr,
                    'decr'   => $missesDecr,
                    'cas'    => $missesCas,
                );

                $hitsJson = '[';
                foreach ($hits as $key => $value) {
                    $hitsJson .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $hitsJson = rtrim($hitsJson, ',') . ']';

                $missesJson = '[';
                foreach ($misses as $key => $value) {
                    $missesJson .= (($value !== null && $value !== false) ? $value : '0') . ',';
                }
                $missesJson = rtrim($missesJson, ',') . ']';

                $data['requestHits']   = $hitsJson;
                $data['requestMisses'] = $missesJson;


                /**
                 * Render subcontent for settings table
                 */
                $subcontent = '';
                $settings   = $this->getSettings($this->getActiveHost());
                $template   = $this->loadTemplates('content-host-dashboard-settings-entity');

                foreach ($settings as $setting => $value) {
                    $templateVariables = array('key' => $setting, 'value' => $value);
                    $subcontent .= $this->renderTemplate($template, $templateVariables);
                }

                $data['subcontent'] = $subcontent;


                $template = $this->loadTemplates('content-host-dashboard-environment');
                $html    .= $this->renderTemplate($template, $data);
                $template = $this->loadTemplates('content-host-dashboard-stored-keys');
                $html    .= $this->renderTemplate($template, $data);
                $template = $this->loadTemplates('content-host-dashboard-memory');
                $html    .= $this->renderTemplate($template, $data);
                $template = $this->loadTemplates('content-host-dashboard-requests');
                $html    .= $this->renderTemplate($template, $data);
                $template = $this->loadTemplates('content-host-dashboard-average-load');
                $html    .= $this->renderTemplate($template, $data);
                $template = $this->loadTemplates('content-host-dashboard-settings');
                $html    .= $this->renderTemplate($template, $data);
                break;

            case self::ACTION_DASHBOARD:
                /*
                // Output only on dashboard
                if ($this->getAction() < 2) {
                    $template .= '<li class="list-group-item"><span class="badge">' . count($this->getHosts()) . ' </span>Hosts</li>';
                }
                */
                break;

            case self::ACTION_DATA_MANAGEMENT:

                $entityHtml = $this->loadTemplates('content-data-management-entity');
                $host       = $this->getActiveHost(true);
                $entries    = $this->dumpEntries($host[0], $host[1], null, true);

                // Iterate the entries and generate one block of HTML per entry! each with own controls
                foreach($entries as $key => $data) {
                    $data['valueType'] = ucfirst(gettype($data['value']));

                    if (is_array($data['value']) === true || is_object($data['value']) === true) {
                        $data['bytes'] = sizeof($data['value']);
                        $data['value'] = var_export($data['value'], true);

                    } elseif (is_int($data['value']) === true || is_double($data['value']) === true) {
                        $data['bytes'] = 2;

                    } else {
                        $data['bytes'] = strlen($data['value']);

                    }

                    $data['bytes'] = number_format($data['bytes'], 0, ',', '.');

                    // Format FLAGS
                    $flags = decbin($data['flags']);
                    $flags = str_pad($flags, self::UINT_32, 0, STR_PAD_LEFT);
                    $flags = str_split($flags, 16);
                    $flags = 'UINT 32-bit' . "\n" . implode('&crarr;' . PHP_EOL, $flags) . '&crarr;';
                    $data['flags'] = $data['flags'] . '&nbsp;<span title="' . $flags . '" data-placement="bottom" class="tooltips pull-right"><span class="text-muted glyphicon glyphicon-eye-open"></span></span>';

                    // Format CAS
                    $cas = decbin($data['cas']);
                    $cas = str_pad($cas, self::UINT_64, 0, STR_PAD_LEFT);
                    $cas = str_split($cas, 16);
                    $cas = 'UINT 64-bit' . "\n" . implode('&crarr;' . PHP_EOL, $cas) . '&crarr;';
                    $data['cas'] = $data['cas'] . '&nbsp;<span title="' . $cas . '" data-placement="bottom" class="tooltips pull-right"><span class="text-muted glyphicon glyphicon-eye-open"></span></span>';

                    switch ($data['valueType']) {
                        case 'String':
                            $data['buttons'] =
                                $this->getDeleteButtonHtml($data['key']) .
                                $this->getEditButtonHtml($data['key'], $data['value'])   .
                                $this->getAppendButtonHtml($data['key']) .
                                $this->getPrependButtonHtml($data['key']);
                            break;

                        case 'Double':
                            $data['buttons'] =
                                $this->getDeleteButtonHtml($data['key']) .
                                $this->getEditButtonHtml($data['key'], $data['value']);
                            break;

                        case 'Integer':
                            $data['buttons'] =
                                $this->getDeleteButtonHtml($data['key']) .
                                $this->getEditButtonHtml($data['key'], $data['value'])   .
                                $this->getDecrementButtonHtml($data['key']) .
                                $this->getIncrementButtonHtml($data['key']);
                            break;

                        default:
                            $data['buttons'] =
                                $this->getDeleteButtonHtml($data['key']);
                            break;
                    }

                    $html .= $this->renderTemplate($entityHtml, $data);
                }
                break;

            default:
                // Intentionally left blank
                break;
        }

        return $html;
    }

    /*------------------------------------------------------------------------------------------------------------------
    | HTML generating Methods
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Returns the edit button rendered by template render engine.
     *
     * @param string $key   The key for button
     * @param string $value The value to edit
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The HTML of the button
     * @access protected
     */
    protected function getEditButtonHtml($key, $value)
    {
        $value = str_replace('\'', '\\\'', $value);

        return $this->renderTemplate(
            $this->loadTemplates('buttons\edit'),
            array(
                'key'   => $key,
                'value' => htmlentities($value),
            )
        );
    }

    /**
     * Returns the delete button rendered by template render engine.
     *
     * @param string $key The key for button
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The HTML of the button
     * @access protected
     */
    protected function getDeleteButtonHtml($key)
    {
        return $this->renderTemplate(
            $this->loadTemplates('buttons\delete'),
            array(
                'key' => $key
            )
        );
    }

    /**
     * Returns the HTML for the increment button.
     *
     * @param string $key The key to render button for
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The HTML of the button
     * @access protected
     */
    protected function getIncrementButtonHtml($key)
    {
        return $this->renderTemplate(
            $this->loadTemplates('buttons\increment'),
            array(
                'key' => $key
            )
        );
    }

    /**
     * Returns the HTML for the decrement button.
     *
     * @param string $key The key to render button for
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The HTML of the button
     * @access protected
     */
    protected function getDecrementButtonHtml($key)
    {
        return $this->renderTemplate(
            $this->loadTemplates('buttons\decrement'),
            array(
                'key' => $key
            )
        );
    }

    /**
     * Returns the HTML for the prepend button.
     *
     * @param string $key The key to render button for
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The HTML of the button
     * @access protected
     */
    protected function getPrependButtonHtml($key)
    {
        return $this->renderTemplate(
            $this->loadTemplates('buttons\prepend'),
            array(
                'key' => $key
            )
        );
    }

    /**
     * Returns the HTML for the append button.
     *
     * @param string $key The key to render button for
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The HTML of the button
     * @access protected
     */
    protected function getAppendButtonHtml($key)
    {
        return $this->renderTemplate(
            $this->loadTemplates('buttons\append'),
            array(
                'key' => $key
            )
        );
    }

    /**
     * Returns the HTML for the error message if error is set, otherwise empty string.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The HTML as string
     * @access protected
     */
    protected function getErrorMessageHtml()
    {
        $html = '';

        if (false !== $error = $this->getError()) {
            $html = $this->renderTemplate(
                $this->loadTemplates('messages\error'),
                array(
                    'error' => $error
                )
            );
        }

        return $html;
    }

    /**
     * Returns the HTML for the info message if info is set, otherwise empty string.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The HTML as string
     * @access protected
     */
    protected function getHtmlInfoMessage()
    {
        $html = '';

        if (false !== $info = $this->getInfo()) {
            $html = $this->renderTemplate(
                $this->loadTemplates('messages\info'),
                array(
                    'info' => $info
                )
            );
        }

        return $html;
    }

    /**
     * Returns the HTML for the success message if info is set, otherwise empty string.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The HTML as string
     * @access protected
     */
    protected function getHtmlSuccessMessage()
    {
        $html = '';

        if (false !== $success = $this->getSuccess()) {
            $html = $this->renderTemplate(
                $this->loadTemplates('messages\success'),
                array(
                    'success' => $success
                )
            );
        }

        return $html;
    }


    /**
     * @return string
     */
    public function getMenuHtml()
    {
        $html  = '<ul class="nav navbar-nav">';
        $html .= '<li>&nbsp;</li>';
        $html .= $this->getHtmlNavigationEntry(self::ACTION_DASHBOARD,       '&nbsp;&nbsp;Cluster',         'glyphicon glyphicon-cloud');
        $html .= $this->getHtmlNavigationEntry(self::ACTION_HOST_DETAILS,    '&nbsp;&nbsp;Host',            'glyphicon glyphicon-stats');
        $html .= $this->getHtmlNavigationEntry(self::ACTION_DATA_MANAGEMENT, '&nbsp;&nbsp;Data', 'glyphicon glyphicon-tags');
        //$html .= $this->getHtmlNavigationEntry(self::ACTION_MEMORY_DETAILS,  '&nbsp;&nbsp;Memory',  'glyphicon glyphicon-tasks');

        $html .= '</ul><ul class="nav navbar-nav navbar-right">';

        // Check for additional controls required ...
        if (
            $this->getAction() !== 1 && (
                $this->getAction() === self::ACTION_HOST_DETAILS ||
                $this->getAction() === self::ACTION_DATA_MANAGEMENT ||
                $this->getAction() === self::ACTION_MEMORY_DETAILS
            )
        ) {

            $activeHost       = $this->getActiveHost();
            $hosts            = $this->getHosts();
            $htmlHostDropdown = '';

            if (count($hosts) > 1) {
                foreach ($hosts as $host) {
                    $htmlHostDropdown .= '<li role="presentation"><a role="menuitem" tabindex="-1" href="' .
                        $this->getLinkForAction($this->getAction(), array('host' => $host)) . '">' . $host . '</a></li>';
                }

                $html .= '<li>';
                $html .= '<div class="dropdown" style="margin-top: 8px;">
                    <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                    ' . $activeHost . '
                    <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                    ' . $htmlHostDropdown . '
                    </ul>
                    </div>';
                $html .= '</li>';
            } else {
                $html .= '<li><a href="#">' . $activeHost . '</a></li>';
            }
        }

        $html .= '<li>';
        $html .= '<p class="navbar-text pull-right">Signed in as <b>' . $this->getConfig()->username .'</b></p>';
        $html .= '</li>';

        $html .= '<li>';
        $html .= '<a href="' . $this->getUrl(array(self::ARGUMENT_ACTION => self::ACTION_LOGOUT), 'logout:logout@') . '"><span class="glyphicon glyphicon-log-out">&nbsp;</span>Logout</a></li>';
        $html .= '</li>';

        $html .= '</ul>';

        return $html;
    }

    /**
     * Creates <li> elements HTML used for generating navigation entries for example.
     *
     * @param int    $action    The action of the entry
     * @param string $title     The text of the menu element
     * @param string $glyphicon The optional glyphicon
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string Generated HTML as string
     * @access protected
     */
    protected function getHtmlNavigationEntry($action, $title, $glyphicon = '')
    {
        // Retrieve the active host
        $host = $this->getActiveHost(true);

        // Build URL for action passed and active host!
        $url = $this->getUrl(
            array(
                self::ARGUMENT_ACTION => $action,
                self::ARGUMENT_HOST   => $host[0] . ':' . $host[1],
            )
        );

        // The name of the class
        $className = ($action === $this->getAction()) ? 'active' : '';

        // Render the HTML from template entry
        return $this->renderString(
            $this->loadTemplates(
                'navigation/entry'
            ),
            array(
                'glyphicon' => $glyphicon,
                'url'       => $url,
                'title'     => $title,
                'className' => $className,
            )
        );
    }

    /**
     * Retrieve settings from Memcached instance.
     *
     * @param string $host The host to retrieve statistics from.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array Containing the result
     * @access protected
     */
    protected function getSettings($host)
    {
        $host   = explode(':', $host);
        $client = $this->getMemcachedClient($host[0], $host[1], $this->getConfig()->timeout);

        return $client->stats(CLIENT::STATS_TYPE_SETTINGS);
    }

    /**
     * Fetch statistics for a single host or (aggregated) for a collection of hosts (cluster).
     *
     * @param array $hosts An array containing a single host or a collection of hosts to fetch statistics for.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The aggregated statistics
     * @access protected
     */
    protected function aggregateStatistics(array $hosts = array())
    {
        $result     = array();
        $statistics = array();

        // Passed host(s) to this method
        foreach ($hosts as $host) {
            $currentHost       = explode(':', $host);
            $client            = $this->getMemcachedClient(
                $currentHost[0],
                $currentHost[1],
                $this->getConfig()->timeout
            );
            $statistics[$host] = $client->stats();
        }

        // Calculate sums for cluster statistics
        foreach ($statistics as $host => $statistics) {
            foreach ($statistics as $key => $value) {
                if (isset($result[$key]) === false) {
                    $result[$key] = 0;
                }

                $value = $this->castAsPhpType($value);

                if (is_double($value) || is_int($value)) {
                    $result[$key] += $value;
                } else {
                    $result[$key]  = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the link to the phpMemAdmin installation with optional action argument.
     *
     * @param int   $action    The action to attach
     * @param array $arguments The arguments to attach to URL
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The link to phpMemAdmin
     * @access protected
     */
    protected function getLinkForAction($action = null, array $arguments = array())
    {
        $link = $_SERVER['PHP_SELF'];
        $link = $link . ($action !== null) ? '?action=' . $action : '';

        foreach ($arguments as $key => $value) {
            $link .= '&' . $key . '=' . $value;
        }

        return $link;
    }

    /**
     * Return the URL for the current executed route.
     *
     * @param array  $arguments Optional arguments to add
     * @param string $prefix    An optional prefix to prepend
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The URL
     * @access protected
     */
    protected function getUrl(array $arguments = array(), $prefix = '', $anchor = '')
    {
        $pageUrl = 'http';

        if (isset($_SERVER['HTTPS']) === true && $_SERVER['HTTPS'] === 'on') {
            $pageUrl .= 's';
        }

        $pageUrl .= '://' . $prefix;

        if ($_SERVER['SERVER_PORT'] !== '80') {
            $pageUrl .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['PHP_SELF'];

        } else {
            $pageUrl .= $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];

        }

        // Add arguments if passed ...
        if (count($arguments) > 0) {
            foreach ($arguments as $key => $value) {
                $pageUrl .= ((strpos($pageUrl, '?') !== false) ? '&' : '?') . $key . '=' . $value;
            }
        }

        if ($anchor !== '') {
            $pageUrl .= $anchor;
        }

        return $pageUrl;
    }

    /**
     * Try to authenticate the user for active session.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function authenticate()
    {
        // Get credentials stored ...
        $credentials = $this->getCredentials();
        $username    = $credentials['username'];
        $password    = $credentials['password'];

        // Check if $username set = login required!
        if (
            $username !== null &&
            !($this->getAction() === self::ACTION_NEUTRAL) &&
            !($this->getAction() === self::ACTION_LOGOUT && $username === 'logout' && $password === 'logout')
        ) {
            if (
                isset($_SERVER['PHP_AUTH_USER']) === false     ||
                isset($_SERVER['PHP_AUTH_PW'])   === false     ||
                $_SERVER['PHP_AUTH_USER']        !== $username ||
                $_SERVER['PHP_AUTH_PW']          !== $password
            ) {
                Header('WWW-Authenticate: Basic realm="phpMemAdmin | Login"');
                Header('HTTP/1.0 401 Unauthorized');
                echo '<html><body><h1>Access denied</h1><b>Invalid credentials.</b></body></html>';
                exit;
            }
        }

        // Return this instance ...
        return $this;
    }

    /**
     * Returns all entries for a passed slab (hostname & port) by passed namespace or all.
     *
     * @param string      $host      The host (slab) to fetch items from
     * @param int         $port      The port
     * @param string|null $namespace The namespace to filter on as string, otherwise NULL to fetch all
     * @param bool        $flat      TRUE to return plain key/value pairs, FALSE to return meta-data as well
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array List of entries indexed by key
     * @access protected
     */
    protected function dumpEntries($host, $port, $namespace = null, $flat = false)
    {
        // Assume empty result
        $result = array();

        $client = $this->getMemcachedClient($host, $port, $this->getConfig()->timeout);

        // Fetch all keys and all values ...
        $allSlabs  = $client->stats(Client::STATS_TYPE_SLABS);
        $items     = $client->stats(Client::STATS_TYPE_ITEMS);

        if (isset($slabs['active_slabs']) === true) {
            unset($slabs['active_slabs']);
        }

        if (isset($slabs['total_malloced']) === true) {
            unset($slabs['total_malloced']);
        }

        foreach ($allSlabs AS $slabId => $slabMeta) {

            $cachedump = $client->stats(
                Client::STATS_TYPE_CACHEDUMP,
                (int)$slabId,
                Client::CACHEDUMP_ITEMS_MAX
            );

            foreach($cachedump as $key => $value) {

                if ($flat === true) {
                    $metaData = $client->gets(array($key), true);

                    $result[] = array(
                        'key'    => $key,
                        'value'  => $metaData[$key]['value'],
                        'cas'    => $metaData[$key]['meta']['cas'],
                        'frames' => $metaData[$key]['meta']['frames'],
                        'flags'  => $metaData[$key]['meta']['flags'],
                    );

                } else {
                    $result[$key] = array(
                        'raw'    => $value,
                        'value'  => $client->gets(array($key)),
                        'server' => $host . ':' . $port,
                        'slabId' => $slabId,
                        'age'    => $items['items'][$slabId]['age'],
                    );
                }
            }
        }

        return $result;
    }

    /*-----------------------------------------------------------------------------------------------------------------+
    | Internal setter & getter
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Setter for title.
     *
     * @param string $title The title
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Setter for title.
     *
     * @param string $title The title
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function title($title)
    {
        $this->setTitle($title);
        return $this;
    }

    /**
     * Getter for title.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The title of the application
     * @access protected
     */
    protected function getTitle()
    {
        return $this->title;
    }

    /**
     * Setter for action.
     *
     * @param int $action The action to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Setter for action.
     *
     * @param int $action The action to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function action($action)
    {
        $this->setAction($action);
        return $this;
    }

    /**
     * Getter for action.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return int The current action if set, otherwise NULL
     * @access public
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Setter for dateFormat.
     *
     * @param string $dateFormat The date-format string.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Setter for dateFormat.
     *
     * @param string $dateFormat The date-format string.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function dateFormat($dateFormat)
    {
        $this->setDateFormat($dateFormat);
        return $this;
    }

    /**
     * Getter for dateFormat.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The active date-time format
     * @access protected
     */
    protected function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Setter for baseUrl.
     *
     * @param string $baseUrl The base URL to set.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Setter for baseUrl.
     *
     * @param string $baseUrl The base URL to set.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function baseUrl($baseUrl)
    {
        $this->setBaseUrl($baseUrl);
        return $this;
    }

    /**
     * Getter for baseUrl.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The base URL for this instance
     * @access protected
     */
    protected function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Setter for client.
     *
     * @param \Clickalicious\Memcached\Client $client The client to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Setter for client.
     *
     * @param \Clickalicious\Memcached\Client $client The client to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function client(Client $client)
    {
        $this->setClient($client);
        return $this;
    }

    /**
     * Getter for client.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return \Clickalicious\Memcached\Client|null $client The client if set, otherwise NULL
     * @access protected
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * Setter for clients.
     *
     * @param \Clickalicious\Memcached\Client[] $clients The client to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setClients(array $clients)
    {
        $this->clients = $clients;
    }

    /**
     * Setter for clients.
     *
     * @param \Clickalicious\Memcached\Client[] $clients The clients to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function clients(array $clients)
    {
        $this->setClients($clients);
        return $this;
    }

    /**
     * Getter for clients.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return \Clickalicious\Memcached\Client[] The clients if set, otherwise empty array
     * @access protected
     */
    protected function getClients()
    {
        return $this->clients;
    }

    /**
     * Setter for credentials.
     *
     * @param string $username The username used to authenticate
     * @param string $password The password used to authenticate
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setCredentials($username, $password)
    {
        $this->credentials['username'] = $username;
        $this->credentials['password'] = $password;
    }

    /**
     * Setter for credentials.
     *
     * @param string $username The username used to authenticate
     * @param string $password The password used to authenticate
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function credentials($username, $password)
    {
        $this->setCredentials($username, $password);
        return $this;
    }

    /**
     * Getter for credentials.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The credentials
     * @access protected
     */
    protected function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Setter for hosts.
     *
     * @param array $hosts The host array
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setHosts(array $hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * Setter for hosts.
     *
     * @param array $hosts The host array
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function hosts(array $hosts)
    {
        foreach ($hosts as $host) {
            $hostCollection[] = $host->host . ':' . $host->port;
        }
        $this->setHosts(array_unique($hostCollection));
        return $this;
    }

    /**
     * Getter for hosts.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The list of hosts
     * @access protected
     */
    protected function getHosts()
    {
        return $this->hosts;
    }

    /**
     * Setter for cluster.
     *
     * @param string $cluster The name of the cluster
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setCluster($cluster)
    {
        $this->cluster = $cluster;
    }

    /**
     * Setter for cluster.
     *
     * @param string $cluster The name of the cluster
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function cluster($cluster)
    {
        $this->setCluster($cluster);
        return $this;
    }

    /**
     * Getter for cluster.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The name of the cluster
     * @access protected
     */
    protected function getCluster()
    {
        return $this->cluster;
    }

    /**
     * Setter for config.
     *
     * @param \stdClass $config The config to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setConfig(\stdClass $config)
    {
        $this->config = $config;
    }

    /**
     * Setter for config.
     *
     * @param \stdClass $config The config to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function config(\stdClass $config)
    {
        $this->setConfig($config);
        return $this;
    }

    /**
     * Getter for config.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return \stdClass config
     * @access protected
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * Setter for error.
     *
     * @param string $error The error to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setError($error)
    {
        $this->error = $error;
    }

    /**
     * Setter for error.
     *
     * @param string $error The error to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function error($error)
    {
        $this->setError($error);
        return $this;
    }

    /**
     * Getter for error.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string Error
     * @access protected
     */
    protected function getError()
    {
        return $this->error;
    }

    /**
     * Setter for success.
     *
     * @param string $success The success message to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setSuccess($success)
    {
        $this->success = $success;
    }

    /**
     * Setter for success.
     *
     * @param string $success The success message to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function success($success)
    {
        $this->setSuccess($success);
        return $this;
    }

    /**
     * Getter for success.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The success message
     * @access protected
     */
    protected function getSuccess()
    {
        return $this->success;
    }

    /**
     * Setter for info.
     *
     * @param string $info The info message to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * Setter for info.
     *
     * @param string $info The info message to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function info($info)
    {
        $this->setInfo($info);
        return $this;
    }

    /**
     * Getter for info.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The info message
     * @access protected
     */
    protected function getInfo()
    {
        return $this->info;
    }

    /*------------------------------------------------------------------------------------------------------------------
    | Internal Helper & Tools
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Formats a number to display it better readable.
     *
     * @param mixed  $value     The value to format
     * @param string $lineEnding The line-ending used for formatting value
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The formatted value
     * @access protected
     */
    protected function formatNumberAsText($value, $lineEnding = '&crarr;')
    {
        $formatted = decbin($value);
        $formatted = str_pad($formatted, 64, 0, STR_PAD_LEFT);
        $formatted = str_split($formatted, 16);
        $formatted = implode($lineEnding . PHP_EOL, $formatted) . $lineEnding;

        return $formatted;
    }

    /**
     * Returns the latest version from memcached official website or 1.0.0 as fallback.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The latest version of Memcached from website or 1.0.0 as fallback (timeout)
     * @access protected
     * @throws Exception
     */
    protected function getMemcachedLatestVersion()
    {
        if (ini_get('allow_url_fopen') !== '1') {
            throw new Exception(
                'URL aware wrappers are disabled! Check "allow_url_fopen" in your systems php.ini ' .
                'OR disable update check in your config.'
            );
        }

        $homepage = file_get_contents(self::MEMCACHED_VERSION_URL);
        $result   = preg_match('/<div id="ver">v([\w\.].*)<\/div>/miu', $homepage, $matches);

        if ($result > 0) {
            $result = htmlentities(strip_tags($matches[1]));
        } else {
            $result = '1.0.0';
        }

        return $result;
    }

    /**
     * Redirects to the passed URL.
     *
     * @param string $url The URL to redirect to
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function redirect($url)
    {
        header('HTTP/1.1 307 Temporary Redirect');
        header('Location: ' . $url);
    }

    /**
     * Simple generic hashing of dynamic input.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The calculated UUID
     * @access protected
     */
    protected function uuid()
    {
        return sha1(implode('.', func_get_args()));
    }

    /**
     * Returns the value casted to a native PHP type.
     *
     * @param mixed $value The value to cast magically.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return mixed The resulting value
     * @access protected
     */
    protected function castAsPhpType($value)
    {
        // We only check string values for type - object and array not - and double and int can keep as they are.
        if (is_string($value) === true && is_numeric($value) === true) {
            // Try to map a string or some similar type of a real type in PHP

            // Try INT
            if ((int)$value . "" === $value) {
                $value = (int)$value;

                // Try DOUBLE
            } elseif ((double)$value . "" === $value) {
                $value = (double)$value;

                // Try BOOL
            } elseif ((bool)$value . "" === $value) {
                $value = (bool)$value;

            }
        }

        return $value;
    }
}
