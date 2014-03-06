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

use phpManufaktur\WebmasterTools\Control\Admin\Admin;
use Silex\Application;
use phpManufaktur\WebmasterTools\Control\Crawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SitemapCrawler extends Admin {

    protected $Crawler = null;

    /**
     * Show the about dialog for the WebmasterTools
     *
     * @return string rendered dialog
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        $subRequest = Request::create('/webmastertools/crawler', 'GET', array(

        ));
        return $app->handle($subRequest, HttpKernelInterface::MASTER_REQUEST);

        $this->Crawler = new Crawler();
        $result = $this->Crawler->Controller($app);

        return __METHOD__ . "result: $result";

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/WebmasterTools/Template', 'admin/crawler.sitemap.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('sitemap'),
                'alert' => $this->getAlert(),

            ));
    }

}
