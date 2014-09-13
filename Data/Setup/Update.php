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

class Update
{
    protected $app = null;
    protected $Configuration = null;
    protected static $config = null;

    /**
     * Execute the update for the WebmasterTools
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->app = $app;


        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'flexContent'));
    }
}
