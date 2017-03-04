<?php
/**
 * Application Environment.
 *
 * @category PHP
 * @package  PHP_CompatInfo_Db
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version  GIT: $Id$
 * @link     http://php5.laurent-laville.org/compatinfo/
 */

namespace Bartlett\CompatInfoDb;

use PDO;

/**
 * Application Environment.
 *
 * @category PHP
 * @package  PHP_CompatInfo_Db
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version  Release: @package_version@
 * @link     http://php5.laurent-laville.org/compatinfo/
 * @since    Class available since Release 3.6.0 of PHP_CompatInfo
 * @since    Class available since Release 1.0.0alpha1 of PHP_CompatInfo_Db
 */
class Environment
{
    const PHP_MIN = '5.4.0';

    private static $dbFile;

    /**
     * Initializes installation of the Reference database
     *
     * @return PDO Instance of pdo_sqlite
     */
    public static function initRefDb($empty = false)
    {
        if (static::$dbFile === null
            || !file_exists(static::$dbFile)
        ) {
            // install DB only if necessary
            $tempDir = sys_get_temp_dir() . '/bartlett';

            if (!file_exists($tempDir)) {
                mkdir($tempDir);
            }

            $dest   = tempnam($tempDir, 'db');
            $source = dirname(dirname(dirname(__DIR__))) . '/data/compatinfo.sqlite';

            if (!$empty) {
                copy($source, $dest);
            }
            static::$dbFile = $dest;
        }

        $pdo = new PDO('sqlite:' . static::$dbFile);
        return $pdo;
    }

    /**
     * Return current DB filename
     *
     * @return string
     * @since  1.19.0
     */
    public static function getDbFilename()
    {
        return static::$dbFile;
    }

    /**
     * Gets version informations about the Reference database
     *
     * @return array
     */
    public static function versionRefDb()
    {
        $pdo = self::initRefDb();

        $stmt = $pdo->prepare(
            'SELECT build_string as "build.string", build_date as "build.date", build_version as "build.version"' .
            ' FROM bartlett_compatinfo_versions'
        );
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Checks the minimum requirements on current platform for the phar distribution
     *
     * @throws \RuntimeException when min requirements does not match
     */
    public static function checkRequirements()
    {
        $error = '';

        if (version_compare(PHP_VERSION, self::PHP_MIN, '<')) {
            $error .= sprintf(
                "\n- Expected PHP %s or above, actual version is %s",
                self::PHP_MIN,
                PHP_VERSION
            );
        }

        $ext = 'pdo_sqlite';
        if (!extension_loaded($ext)) {
            $error .= sprintf(
                "\n- Expected PHP extension %s loaded to use SQLite DataBase, extension may be missing",
                $ext
            );
        }

        if (!empty($error)) {
            throw new \RuntimeException(
                'Your platform does not satisfy CompatInfo minimum requirements' .
                $error
            );
        }
    }
}
