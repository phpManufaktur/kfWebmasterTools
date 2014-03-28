<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/flexContent
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\Basic\Control\CMS\EmbeddedAdministration;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;


// scan the /Locale directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/WebmasterTools/Data/Locale');
// scan the /Locale/Custom directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/WebmasterTools/Data/Locale/Custom');

/**
 * Use the EmbeddedAdministration feature to connect the extension with the CMS
 *
 * @link https://github.com/phpManufaktur/kitFramework/wiki/Extensions-%23-Embedded-Administration
 */
$app->get('/webmastertools/cms/{cms_information}', function ($cms_information) use ($app) {
    $administration = new EmbeddedAdministration($app);
    return $administration->route('/admin/webmastertools/about', $cms_information, 'ROLE_ADMIN');
});

// create a logger for the crawler
$app['monolog.crawler'] = $app->share(function($app) {
    $logger = new Logger('crawler');
    $logger->pushHandler(new RotatingFileHandler(FRAMEWORK_PATH.'/logfile/crawler.log', Logger::DEBUG));
    return $logger;
});

/**
 * ADMIN routes
 */

$app->get('/admin/webmastertools/setup',
    // setup routine for the WebmasterTools
    'phpManufaktur\WebmasterTools\Data\Setup\Setup::Controller');
$app->get('/admin/webmastertools/update',
    // update the WebmasterTools
    'phpManufaktur\WebmasterTools\Data\Setup\Update::Controller');
$app->get('/admin/webmastertools/uninstall',
    // uninstall routine for flexContent
    'phpManufaktur\WebmasterTools\Data\Setup\Uninstall::Controller');


$app->get('/admin/webmastertools/about',
    'phpManufaktur\WebmasterTools\Control\Admin\About::Controller');
$app->get('/admin/webmastertools/sitemaps',
    'phpManufaktur\WebmasterTools\Control\Admin\Sitemaps::Controller');
$app->get('/admin/webmastertools/protocol',
    'phpManufaktur\WebmasterTools\Control\Admin\Protocols::Controller');
$app->post('/admin/webmastertools/protocol/select',
    'phpManufaktur\WebmasterTools\Control\Admin\Protocols::Controller');
$app->get('/admin/webmastertools/configuration',
    'phpManufaktur\WebmasterTools\Control\Admin\Configurations::Controller');
$app->post('/admin/webmastertools/configuration/select',
    'phpManufaktur\WebmasterTools\Control\Admin\Configurations::Controller');
$app->post('/admin/webmastertools/configuration/save',
    'phpManufaktur\WebmasterTools\Control\Admin\Configurations::ControllerSaveJSON');

$app->get('/webmastertools/crawler',
    'phpManufaktur\WebmasterTools\Control\Crawler\Crawler::Controller');
