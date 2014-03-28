<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/flexContent
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\WebmasterTools\Control\Crawler;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use phpManufaktur\WebmasterTools\Control\Configuration;
use phpManufaktur\WebmasterTools\Data\Crawler\CrawlerURL;
use Carbon\Carbon;

require_once MANUFAKTUR_PATH.'/WebmasterTools/Control/Crawler/http_build_url.php';

class Crawler
{
    protected $app = null;
    protected $CrawlerData = null;

    protected static $config = null;
    protected static $script_start = null;
    protected static $script_stop = null;
    protected static $index_url = null;
    protected static $index_url_use_www = null;
    protected static $index_url_alternate = null;
    protected static $index_host = null;
    protected static $index_host_alternate = null;
    protected static $check_external_url = null;
    protected static $parent_url = null;

    protected static $status = null;

    /**
     * Initialize the Crawler
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        $this->app = $app;

        // set the script start
        self::$script_start = microtime(true);

        // get the configuration
        $Config = new Configuration($app);
        self::$config = $Config->getConfiguration();

        // try to set the maximum execution time
        if (!ini_set('max_execution_time', self::$config['general']['max_execution_time'])) {
            $app['monolog']->addError("Can't set max_execution_time!", array(__METHOD__, __LINE__));
            $app['monolog.crawler']->addError("Can't set max_execution_time!", array(__METHOD__, __LINE__));
        }
        // get the really available max. execution time
        $max_execution_time = ini_get('max_execution_time');
        if ($max_execution_time < self::$config['general']['max_execution_time']) {
            $app['monolog']->addError(sprintf("Can't increase the max_execution_time from %s to %s seconds!",
                $max_execution_time, self::$config['general']['max_execution_time']),
                array(__METHOD__, __LINE__));
            $app['monolog.crawler']->addError(sprintf("Can't increase the max_execution_time from %s to %s seconds!",
                $max_execution_time, self::$config['general']['max_execution_time']),
                array(__METHOD__, __LINE__));
        }
        // set the limited execution time
        $limit_execution_time = ($max_execution_time - self::$config['general']['buffer_execution_time']);
        if ($limit_execution_time < 0) {
            // 20 seconds should be always possible ...
            $limit_execution_time = 20;
        }

        self::$script_stop = self::$script_start + $limit_execution_time;

        // set index URL - always remove trailing slash
        self::$index_url = rtrim(self::$config['sitemap']['url']['index'][0], '/');
        self::$status = 'UNDEFINED';

        $this->CrawlerData = new CrawlerURL($app);

        // __destructor() doesn't get called on Fatal errors
        register_shutdown_function(array($this, 'shutdown'));
    }

    /**
     * shutdown() will be always called if the script is finished
     *
     */
    public function shutdown()
    {
        $this->app['monolog.crawler']->addInfo(sprintf('SCRIPT: Seconds remaining: %s', (self::$script_stop - microtime(true))));
        $this->app['monolog.crawler']->addInfo(sprintf('Finished the Crawler with status: %s', self::$status), array(__METHOD__, __LINE__));

        $this->app['monolog']->addDebug(sprintf('Finished the Crawler with status: %s', self::$status), array(__METHOD__, __LINE__));
    }

    protected function checkIndexURL()
    {
        $parsed_url = parse_url(self::$index_url);

        if (!isset($parsed_url['host'])) {
            $this->app['monolog.crawler']->addError(sprintf('Invalid INDEX URL: %s, must contain scheme and path!', self::$index_url));
            throw new \Exception(sprintf('[Crawler] Invalid INDEX URL: %s, must contain scheme and path!', self::$index_url));
        }

        self::$index_host = $parsed_url['host'];

        if (strpos($parsed_url['host'], 'www.') == 0) {
            self::$index_url_use_www = true;
            $parsed = array(
                'scheme' => $parsed_url['scheme'],
                'host' => substr($parsed_url['host'], strlen('www.')),
                'path' => (isset($parsed_url['path']) && !empty($parsed_url['path'])) ? $parsed_url['path'] : ''
            );
            self::$index_host_alternate = $parsed['host'];
            self::$index_url_alternate = http_build_url(self::$index_url, $parsed);
        }
        else {
            self::$index_url_use_www = false;
            $parsed = array(
                'scheme' => $parsed_url['scheme'],
                'host' => 'www.'.$parsed_url['host'],
                'path' => (isset($parsed_url['path']) && !empty($parsed_url['path'])) ? $parsed_url['path'] : ''
            );
            self::$index_host_alternate = $parsed['host'];
            self::$index_url_alternate = http_build_url(self::$index_url, $parsed);
        }

        self::$check_external_url = self::$config['sitemap']['tag']['a']['href']['external']['check'];

    }

    /**
     * Check if URL exists and fill the $headers array
     *
     * @param string $url
     * @param array reference $headers
     * @return boolean
     */
    protected function existsURL($url, &$headers=array())
    {
        @$headers = get_headers($url, 1);
        return (bool) preg_match('/^HTTP\/\d\.\d\s+(200|301|302)/', $headers[0]);
    }

    /**
     * Check the given Link logical and add it as PENDING to the database
     *
     * @param string $link
     * @param string $target
     * @return boolean
     */
    protected function addLinkToDatabase($link, $target='')
    {
        if (false === ($parsed_url = parse_url($link))) {
            // invalid or malformed URL - possibly also JavaScript ...
            $this->app['monolog.crawler']->addInfo(sprintf('Skipped %s, can not parse URL.', $link));
            continue;
        }

        if (!isset($parsed_url['scheme']) && !isset($parsed_url['host']) &&
            isset($parsed_url['path']) && !empty($parsed_url['path'])) {
            // incomplete URL - add the index URL
            $path = ($parsed_url['path'][0] == '/') ? $parsed_url['path'] : '/'.$parsed_url['path'];
            $url = self::$index_url;
            $parsed_url = parse_url($url.$path);
            $this->app['monolog.crawler']->addInfo(sprintf('Changed %s to URL %s', $link, $url), $parsed_url);
        }
        elseif (isset($parsed_url['scheme']) && !in_array($parsed_url['scheme'], self::$config['sitemap']['url']['scheme'])) {
            // URL scheme is not supported
            $this->app['monolog.crawler']->addInfo(sprintf('Skipped %s, URL scheme is not supported', $link), $parsed_url);
            return false;
        }
        elseif ((!isset($parsed_url['path']) || empty($parsed_url['path'])) &&
            ((!isset($parsed_url['scheme']) || empty($parsed_url['scheme'])) &&
             (!isset($parsed_url['host']) || empty($parsed_url['host'])))) {
            // no valid URL, probably an anchor ...
            $this->app['monolog.crawler']->addInfo('Skipped empty URL, probably an anchor?', $parsed_url);
            return false;
        }
        elseif (!self::$check_external_url && (isset($parsed_url['host']) &&
            (strtolower($parsed_url['host']) != self::$index_host) &&
            (strtolower($parsed_url['host']) != self::$index_host_alternate))) {
            // skip external URL
            $this->app['monolog.crawler']->addInfo(sprintf('Skipped %s, external URL.', $link), $parsed_url);
            return false;
        }

        $add_query = false;
        if (!empty(self::$config['sitemap']['tag']['a']['href']['parameter']['create_url']) &&
            isset($parsed_url['query']) && !empty($parsed_url['query'])) {
            $queries = (strpos($parsed_url['query'], '&')) ? explode('&', $parsed_url['query']) : array($parsed_url['query']);
            foreach ($queries as $query) {
                if (strpos($query, '=')) {
                    list($key, $value) = explode('=', $query);
                    if (in_array($key, self::$config['sitemap']['tag']['a']['href']['parameter']['create_url'])) {
                        // add query to this URL
                        $add_query = true;
                        break;
                    }
                }
            }
        }

        $parsed = array(
            'scheme' => $parsed_url['scheme'],
            'host' => (strtolower($parsed_url['host']) == self::$index_host_alternate) ? self::$index_host : $parsed_url['host'],
            'path' => isset($parsed_url['path']) ? $parsed_url['path'] : ''
        );
        if ($add_query) {
            $this->app['monolog.crawler']->addInfo(sprintf('Added query %s to URL %s', $parsed['query'], $link));
            $parsed['query'] = $parsed_url['query'];
        }

        $url = http_build_url('', $parsed);

        if (!$this->CrawlerData->existsURL($url, self::$index_url)) {
            $data = array(
                'index_url' => self::$index_url,
                'url' => $url,
                'internal' => ($parsed['host'] == self::$index_host) ? 'YES' : 'NO',
                'target' => $target,
                'parent' => self::$parent_url
            );
            $this->CrawlerData->insert($data);
            $this->app['monolog.crawler']->addInfo(sprintf('Added %s to database.', $url));
            return true;
        }

        $this->app['monolog.crawler']->addInfo(sprintf('Skipped %s, already in database.', $url));
        return false;
    }

    /**
     * Crawling through all URLs starting at the INDEX URL
     *
     * @param string $url
     * @return boolean
     */
    protected function processURL($url, &$http_status='- undefined -')
    {
        $this->app['monolog.crawler']->addInfo(sprintf('Start processing URL %s', $url));

        $headers = array();
        if (!$this->existsURL($url, $headers)) {
            $http_status = (isset($headers[0])) ? $headers[0] : '- undefined -';
            $this->app['monolog.crawler']->addError(sprintf('The URL %s does not exists or is not accessible!',
                $url), array(__METHOD__, __LINE__));
            return false;
        }
        $http_status = (isset($headers[0])) ? $headers[0] : '- undefined -';

        // enable internal error handling
        libxml_use_internal_errors(true);
        $DOM = new \DOMDocument();
        try {
            if (!$DOM->loadHTMLFile($url)) {
                foreach (libxml_get_errors() as $error) {
                    // log each error
                    $this->app['monolog.crawler']->addError(sprintf('Can not read the DOM file %s', $url), $error);
                    $this->app['monolog']->addError(sprintf('[Crawler] Can not read the DOM file %s', $url), $error);
                }
                $http_status = 'DOM ERROR';
                return false;
            }
        } catch (\Exception $e) {
            $this->app['monolog.crawler']->addError(sprintf('Can not read the DOM file %s', $url),
                array(__METHOD__, __LINE__));
            $this->app['monolog']->addError(sprintf('[Crawler] Can not read the DOM file %s', $url),
                array(__METHOD__, __LINE__));
            $http_status = 'DOM ERROR';
            return false;
        }
        // clear the internal errors
        libxml_clear_errors();
echo 'xx:'.$DOM->saveHTML();
        // gather all links
        self::$parent_url = $url;

        $this->app['monolog.crawler']->addInfo(sprintf('Start gathering all links for URL %s', $url));

        $checkedLink = false;
        foreach($DOM->getElementsByTagName('a') as $link) {
            if (microtime(true) > self::$script_stop) {
                $this->app['monolog.crawler']->addInfo('BREAK the SCRIPT');
                self::$status = 'BREAK';
                return false;
            }
            if ($link->hasAttribute('href') || $link->hasAttribute('target')) {
                $this->addLinkToDatabase($link->getAttribute('href'), $link->getAttribute('target'));
                $checkedLink = true;
            }
        }

        // dive into the iframes
        foreach($DOM->getElementsByTagName('iframe') as $iframe) {
            $url = $iframe->getAttribute('src');

            $parsed_url = parse_url($url);
            if ((strtolower($parsed_url['host']) == self::$index_host) ||
                (strtolower($parsed_url['host']) == self::$index_host_alternate)) {

                // loop through the iframe
                libxml_use_internal_errors(true);
                try {
                    $subDOM = new \DOMDocument();
                    $subDOM->loadHTMLFile($url);
                } catch (\Exception $e) {
                    $this->app['monolog']->addError(sprintf('[Crawler] Can not read the DOM file %s', $url),
                        array(__METHOD__, __LINE__));
                    $this->app['monolog.crawler']->addError(sprintf('Can not read the DOM file %s', $url),
                        array(__METHOD__, __LINE__));
                    $http_status = 'DOM ERROR';
                    return false;
                }
                libxml_clear_errors();

                $this->app['monolog.crawler']->addInfo(sprintf('Start processing the iFrame for URL %s', $url));

                self::$parent_url = $url;
                foreach($subDOM->getElementsByTagName('a') as $link) {
                    if (microtime(true) > self::$script_stop) {
                        $this->app['monolog.crawler']->addInfo('BREAK the SCRIPT');
                        self::$status = 'BREAK';
                        return false;
                    }
                    if ($link->hasAttribute('href') || $link->hasAttribute('target')) {
                        $this->addLinkToDatabase($link->getAttribute('href'), $link->getAttribute('target'));
                        $checkedLink = true;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Loop through the URLs and check them
     *
     * @return boolean
     */
    protected function checkURLs()
    {
        self::$status = 'CHECKING';

        if (false === ($links = $this->CrawlerData->selectPendingURLs(self::$index_url))) {
            $this->app['monolog.crawler']->addInfo(sprintf('No pending URLs for %s', self::$index_url),
                array(__METHOD__, __LINE__));
            self::$status = 'FINISHED';
            return false;
        }

        foreach ($links as $link) {
            if (microtime(true) > self::$script_stop) {
                $this->app['monolog.crawler']->addInfo('BREAK the SCRIPT');
                self::$status = 'BREAK';
                return false;
            }
            $http_status = '- undefined -';
            if ($this->processURL($link['url'], $http_status)) {
                $data = array(
                    'status' => 'CHECKED',
                    'exists' => 'YES',
                    'http_status' => $http_status
                );
            }
            else {
                $data = array(
                    'status' => 'CHECKED',
                    'exists' => 'NO',
                    'http_status' => $http_status
                );
            }
            $this->CrawlerData->update($link['id'], $data);

        }

        self::$status = 'FINISHED';

        return true;
    }

    /**
     * The main controller for the Crawler
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        $action = 'UNDEFINED';

        foreach (self::$config['sitemap']['url']['index'] as $url) {
            // loop through the index urls
            self::$index_url = $url;
            $this->checkIndexURL();

            if ($this->CrawlerData->existsIndexUrl(self::$index_url)) {
                // index url exists
                if ($this->CrawlerData->hasPendingURLs(self::$index_url)) {
                    // this index url has pending urls, so we process it
                    $action = 'PENDING';
                    break;
                }
                // check if a new scan should be processed ...
                $timestamp = $this->CrawlerData->selectLastCrawlDateTime(self::$index_url);
                $scan = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp);
                $scan->addHours(self::$config['sitemap']['url']['crawl']['hours']);
                $now = Carbon::now();
                if ($now->gt($scan)) {
                    // CREATE a new crawling for this index URL
                    $this->CrawlerData->deleteBackupData(self::$index_url);
                    $this->CrawlerData->changeScanToBackup(self::$index_url);
                    $action = 'CREATE';
                    break;
                }
            }
            else {
                // this index does not exists
                $action = 'CREATE';
                break;
            }
        }

        switch ($action) {
            case 'CREATE':
                // create a new set for the given index URL
                if (!$this->processURL(self::$index_url)) {
                    self::$status = 'ERROR';
                    $this->saveCrawlerStatus();
                    return new Response('Crawler finished with errors, please check the logfile');
                }
                $this->app['monolog.crawler']->addInfo(sprintf('SCRIPT: Seconds remaining: %s', (self::$script_stop - microtime(true))));
                $process = true;

                do {
                    // loop through the URLs and check them
                    if (!$this->CrawlerData->hasPendingURLs(self::$index_url)) {
                        break;
                    }
                    if (!$this->checkURLs()) {
                        break;
                    }
                } while ($process);
                break;
            case 'PENDING':
                // continue the crawling for the given index URL
                $process = true;
                do {
                    // loop through the URLs and check them
                    if (!$this->CrawlerData->hasPendingURLs(self::$index_url)) {
                        break;
                    }
                    if (!$this->checkURLs()) {
                        break;
                    }
                } while ($process);
                break;
            default:
                $this->app['monolog.crawler']->addError(sprintf('Don\'t know how to handle the action %s!', $action),
                    array(__METHOD__, __LINE__));
                break;
        }

        if ($action == 'UNDEFINED') {
            self::$status = 'OK';
        }
        // return with the actual status
        return new Response(self::$status);
    }

}
