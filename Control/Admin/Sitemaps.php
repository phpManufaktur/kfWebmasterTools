<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/WebmasterTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\WebmasterTools\Control\Admin;

use phpManufaktur\WebmasterTools\Control\Admin\Admin;
use Silex\Application;
use phpManufaktur\WebmasterTools\Data\Crawler\CrawlerURL;

class Sitemaps extends Admin {

    protected $Crawler = null;

    /**
     * Show the about dialog for the WebmasterTools
     *
     * @return string rendered dialog
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        $crawlerURL = new CrawlerURL($app);

        $sitemaps = array();
        foreach (self::$config['sitemap']['url']['index'] as $url) {
            $exists = $crawlerURL->existsIndexUrl($url, 'SCAN');
            $sitemaps[$url] = array(
                'url' => $url,
                'scan' => array(
                    'exists' => $exists,
                    'crawled' => array(
                        'last' => array(
                            'timestamp' => $crawlerURL->selectLastCrawlDateTime($url, 'SCAN'),
                            'url' => $crawlerURL->selectLastCrawledURL($url, 'SCAN')
                        ),
                        'total' => $crawlerURL->countCrawledURLs($url, 'SCAN')
                    ),
                    'finished' => ($exists && !$crawlerURL->hasPendingURLs($url, 'SCAN')),
                )
            );
        }

        print_r($sitemaps);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/WebmasterTools/Template', 'admin/sitemaps.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('sitemaps'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'sitemaps' => $sitemaps
            ));
    }

}
