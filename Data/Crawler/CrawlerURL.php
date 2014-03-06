<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/flexContent
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\WebmasterTools\Data\Crawler;

use Silex\Application;

class CrawlerURL
{
    protected $app = null;
    protected static $table_name = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'webmastertools_crawler_url';
    }

    /**
     * Create the table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `index_url` VARCHAR(128) NOT NULL DEFAULT '',
        `index_type` ENUM('SCAN', 'BACKUP') NOT NULL DEFAULT 'SCAN',
        `url` TEXT NOT NULL,
        `parent` TEXT NOT NULL,
        `internal` ENUM('YES','NO') NOT NULL DEFAULT 'NO',
        `target` VARCHAR(16) NOT NULL DEFAULT '',
        `http_status` VARCHAR(64) NOT NULL DEFAULT '- unknown -',
        `exists` ENUM('YES','NO') NOT NULL DEFAULT 'NO',
        `status` ENUM('PENDING','CHECKED') NOT NULL DEFAULT 'PENDING',
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX (`index_url`, `exists`, `status`)
    )
    COMMENT='Table for processing URLs by the Crawler'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'webmastertools_crawler_url'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop the table
     */
    public function dropTable()
    {
        $this->app['db.utils']->dropTable(self::$table_name);
    }

    /**
     * Insert a new record
     *
     * @param array $data
     * @param integer reference $id
     * @throws \Exception
     */
    public function insert($data, &$id=null)
    {
        try {
            $insert = array();
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    if (($key == 'id') || ($key == 'timestamp')) {
                        continue;
                    }
                    $insert[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
                }
                if (!empty($insert)) {
                    $this->app['db']->insert(self::$table_name, $insert);
                    $id = $this->app['db']->lastInsertId();
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the given record
     *
     * @param integer $id
     * @param array $data
     * @throws \Exception
     */
    public function update($id, $data)
    {
        try {
            $update = array();
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    if (($key == 'id') || ($key == 'timestamp')) {
                        continue;
                    }
                    $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
                }
                if (!empty($update)) {
                    $this->app['db']->update(self::$table_name, $update, array('id' => "$id"));
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given URL already exists
     *
     * @param string $url
     * @param string $index_url
     * @throws \Exception
     * @return Ambigous <boolean, integer> return false or the ID of the record
     */
    public function existsURL($url, $index_url, $index_type='SCAN')
    {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `url`='$url' AND `index_url`='$index_url' ".
                "AND `index_type`='$index_type'";
            $id = $this->app['db']->fetchColumn($SQL);
            return ($id > 0) ? $id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all PENDING URLs for the given index URL
     *
     * @param string $index_url
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectPendingURLs($index_url, $index_type='SCAN')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `status`='PENDING' AND `index_url`='$index_url' ".
                "AND `index_type`='$index_type'";
            $results = $this->app['db']->fetchAll($SQL);
            return (is_array($results)) ? $results : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check for PENDING URLs
     *
     * @param string $index_url
     * @throws \Exception
     * @return boolean
     */
    public function hasPendingURLs($index_url, $index_type='SCAN')
    {
        try {
            $SQL = "SELECT COUNT(`id`) AS `pending` FROM `".self::$table_name."` WHERE `status`='PENDING' AND ".
                "`index_url`='$index_url' AND `index_type`='$index_type'";
            $pending = $this->app['db']->fetchColumn($SQL);
            return ($pending > 0);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given index URL exists
     *
     * @param string $index_url
     * @param string $index_type optional, type = SCAN or BACKUP
     * @throws \Exception
     * @return boolean
     */
    public function existsIndexUrl($index_url, $index_type='SCAN')
    {
        try {
            $SQL = "SELECT COUNT(`id`) AS `count` FROM `".self::$table_name."` WHERE `index_url`='$index_url' ".
                "AND `index_type`='$index_type'";
            $count = $this->app['db']->fetchColumn($SQL);
            return ($count > 0);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the last crawled URL for the given index URL
     *
     * @param string $index_url
     * @throws \Exception
     * @return Ambigous <boolean, string>
     */
    public function selectLastCrawledURL($index_url)
    {
        try {
            $SQL = "SELECT `url` FROM `".self::$table_name."` WHERE `index_url`='$index_url' AND ".
                "`index_type`='SCAN' ORDER BY `timestamp` DESC, `id` DESC LIMIT 1";
            $url = $this->app['db']->fetchColumn($SQL);
            return (strlen($url) > 0) ? $url : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the last Timestamp a crawl was processed/checked for the given index URL
     *
     * @param string $index_url
     * @param string $index_type
     * @throws \Exception
     * @return string DATETIME
     */
    public function selectLastCrawlDateTime($index_url, $index_type='SCAN')
    {
        try {
            $SQL = "SELECT `timestamp` FROM `".self::$table_name."` WHERE `index_url`='$index_url' ".
                "AND `index_type`='$index_type' ORDER BY `timestamp` DESC LIMIT 1";
            $timestamp = $this->app['db']->fetchColumn($SQL);
            return $timestamp;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the backup data for the given index URL
     *
     * @param string $index_url
     * @throws \Exception
     */
    public function deleteBackupData($index_url)
    {
        try {
            $this->app['db']->delete(self::$table_name,
                array('index_url' => $index_url, 'index_type' => 'BACKUP'));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Change the actual SCAN records to BACKUP for the given index URL
     *
     * @param string $index_url
     * @throws \Exception
     */
    public function changeScanToBackup($index_url)
    {
        try {
            $this->app['db']->update(self::$table_name, array('index_type' => 'BACKUP'),
                array('index_url' => $index_url, 'index_type' => 'SCAN'));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
