<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/flexContent
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\WebmasterTools\Control\Admin;

use Silex\Application;
use phpManufaktur\WebmasterTools\Control\Configuration;
use phpManufaktur\Basic\Control\Pattern\Alert;

class Admin extends Alert
{

    protected static $usage = null;
    protected static $usage_param = null;
    protected static $config = null;

    /**
     * Initialize the class with the needed parameters
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';
        // set the locale from the CMS locale
        if (self::$usage != 'framework') {
            $app['translator']->setLocale($this->app['session']->get('CMS_LOCALE', 'en'));
        }
        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        // try to set the maximum execution time
        if (!ini_set('max_execution_time', self::$config['general']['max_execution_time'])) {
            $app['monolog']->addError("Can't set max_execution_time!", array(__METHOD__, __LINE__));
        }
    }

    /**
     * Get the toolbar for all backend dialogs
     *
     * @param string $active dialog
     * @return array
     */
    public function getToolbar($active) {
        $toolbar_array = array(
            'sitemap' => array(
                'name' => 'sitemap',
                'text' => 'Sitemap',
                'hint' => 'Create Sitemaps for your Website',
                'link' => FRAMEWORK_URL.'/admin/webmastertools/sitemap'.self::$usage_param,
                'active' => ($active == 'sitemap')
                ),
            'protocol' => array(
                'name' => 'protocol',
                'text' => 'Protocol',
                'hint' => 'The crawler protocols',
                'link' => FRAMEWORK_URL.'/admin/webmastertools/protocol/crawler'.self::$usage_param,
                'active' => ($active == 'protocol')
            ),
            'about' => array(
                'name' => 'about',
                'text' => 'About',
                'hint' => 'Information about the WebmasterTools',
                'link' => FRAMEWORK_URL.'/admin/webmastertools/about'.self::$usage_param,
                'active' => ($active == 'about')
                ),
        );
        return $toolbar_array;
    }
 }
