<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/WebmasterTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\WebmasterTools\Data\Setup;

use Silex\Application;
use phpManufaktur\Basic\Control\CMS\InstallAdminTool;
use phpManufaktur\WebmasterTools\Data\Crawler\CrawlerURL;

class Setup
{
    protected $app = null;

    /**
     * Execute all steps needed to setup the WebmasterTools
     *
     * @param Application $app
     * @throws \Exception
     * @return string with result
     */
    public function Controller(Application $app)
    {
        try {
            $this->app = $app;

            $CrawlerURL = new CrawlerURL($app);
            $CrawlerURL->createTable();

            // setup kit_framework_webmastertools as Add-on in the CMS
            $admin_tool = new InstallAdminTool($app);
            $admin_tool->exec(MANUFAKTUR_PATH.'/WebmasterTools/extension.json', '/webmastertools/cms');

            return $app['translator']->trans('Successfull installed the extension %extension%.',
                array('%extension%' => 'WebmasterTools'));

        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
