<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/flexContent
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\WebmasterTools\Data\Setup;

use Silex\Application;
use phpManufaktur\Basic\Control\CMS\UninstallAdminTool;
use phpManufaktur\WebmasterTools\Data\Crawler\CrawlerURL;

class Uninstall
{

    protected $app = null;

    public function Controller(Application $app)
    {
        try {

            $CrawlerURL = new CrawlerURL($app);
            $CrawlerURL->dropTable();

            // uninstall kit_framework_webmastertools from the CMS
            $admin_tool = new UninstallAdminTool($app);
            $admin_tool->exec(MANUFAKTUR_PATH.'/WebmasterTools/extension.json');

            return $app['translator']->trans('Successfull uninstalled the extension %extension%.',
                array('%extension%' => 'WebmasterTools'));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
