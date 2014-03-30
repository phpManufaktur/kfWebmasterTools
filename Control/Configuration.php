<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/flexContent
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\WebmasterTools\Control;

use Silex\Application;

class Configuration
{
    protected $app = null;
    protected static $config = null;
    protected static $config_path = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$config_path = MANUFAKTUR_PATH.'/WebmasterTools/config.webmastertools.json';
        $this->readConfiguration();
    }

    /**
     * Return the default configuration array for the WebmasterTools
     *
     * @return array
     */
    public function getDefaultConfigArray()
    {
        return array(
            'general' => array(
                'max_execution_time' => 60,
                'buffer_execution_time' => 10
            ),
            'protocol' => array(
                'pattern' => array(
                    '/logfile/framework-*.log',
                    '/logfile/crawler-*.log'
                )
            ),
            'configuration' => array(
                'framework' => array(
                    'pattern' => array(
                        '/*.json'
                    )
                ),
                'extension' => array(
                    'pattern' => array(
                        '/config.*.json'
                    )
                ),
                'information' => array(
                    'cms.json' => array(
                        'path' => '/framework.json',
                        'image' => '/framework.jpg',
                        'wiki' => 'https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#cmsjson'
                    ),
                    'config.webmastertools.json' => array(
                        'path' => '/extension/phpmanufaktur/phpManufaktur/WebmasterTools/extension.json',
                        'image' => '/extension/phpmanufaktur/phpManufaktur/WebmasterTools/extension.jpg',
                        'wiki' => 'https://github.com/phpManufaktur/kfWebmasterTools/wiki/config.webmastertools.json'
                    ),
                    'doctrine.cms.json' => array(
                        'path' => '/framework.json',
                        'image' => '/framework.jpg',
                        'wiki' => 'https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#doctrinecmsjson'
                    ),
                    'framework.json' => array(
                        'path' => '/framework.json',
                        'image' => '/framework.jpg',
                        'wiki' => 'https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#frameworkjson'
                    ),
                    'proxy.json' => array(
                        'path' => '/framework.json',
                        'image' => '/framework.jpg',
                        'wiki' => 'https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#proxyjson'
                    ),
                    'recaptcha.json' => array(
                        'path' => '/framework.json',
                        'image' => '/framework.jpg',
                        'wiki' => 'https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#recaptchajson'
                    ),
                    'swift.cms.json' => array(
                        'path' => '/framework.json',
                        'image' => '/framework.jpg',
                        'wiki' => 'https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#swiftcmsjson'
                    )
                )
            ),
            'sitemap' => array(
                'url' => array(
                    'index' => array(
                        CMS_URL
                    ),
                    'scheme' => array(
                        'http',
                        'https'
                    ),
                    'crawl' => array(
                        'hours' => 24
                    )
                ),
                'tag' => array(
                    'a' => array(
                        'href' => array(
                            'parameter' => array(
                                'create_url' => array(

                                )
                            ),
                            'external' => array(
                                'check' => false
                            )
                        )
                    ),
                    'iframe' => array(
                        'src' => array(
                            'follow' => array(
                                'internal' => true
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Read the configuration file
     */
    protected function readConfiguration()
    {
        if (!file_exists(self::$config_path)) {
            self::$config = $this->getDefaultConfigArray();
            $this->saveConfiguration();
        }
        self::$config = $this->app['utils']->readConfiguration(self::$config_path);
    }

    /**
     * Save the configuration file
     */
    public function saveConfiguration()
    {
        // write the formatted config file to the path
        file_put_contents(self::$config_path, $this->app['utils']->JSONFormat(self::$config));
        $this->app['monolog']->addDebug('Save configuration to '.basename(self::$config_path));
    }

    /**
     * Get the configuration array
     *
     * @return array
     */
    public function getConfiguration()
    {
        return self::$config;
    }

    /**
     * Set the configuration array
     *
     * @param array $config
     */
    public function setConfiguration($config)
    {
        self::$config = $config;
    }

}
