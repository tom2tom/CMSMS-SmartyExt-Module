<?php
/**
 * A set of high level convenience methods.
 *
 * Migrated from CMSMSExt module
 *
 * @author Robert Campbell
 */
namespace SmartyExt;

//use SmartyExt\config; TODO CHECK
//use SmartyExt\xt_settings;
use cms_config;
use cms_utils;
use CMSMS\Database\Connection as Database;
use CMSMS\Database\compatibility as db_compatibility;
use CmsSQLErrorException;
use Exception;
use LogicException;
use RuntimeException;
use function startswith;

final class xt_utils
{
    /**
     * @ignore
     */
    private $mod;

    /**
     * @ignore
     */
    private static $_instance;

    /**
     * @ignore
     * @deprecated  will be changed to private _settings
     */
    private $settings;

    /**
     * @ignore
     */
    public function __construct(SmartyExt $mod, xt_settings $settings)
    {
        if (self::$_instance) {
            throw new LogicException('Only one instance of '.__CLASS__.' is permitted');
        }
        self::$_instance = $this;

        $this->mod = $mod;
        $this->settings = $settings;
    }

    /**
     * Get a database connection object that supports exceptions.
     * Note: This function creates a new database abstraction object.
     * @return Database object
     */
    public static function get_db()
    {
//        if (method_exists(self::$_instance->mod, 'get_extended_db')) {
//            return self::$_instance->mod->get_extended_db();
//        } else {
            return self::_get_db();
//        }
    }

    /**
     * Convert the supplied unix timestamp into a database compatible datetime string
     *
     * @param int  $unixtime
     * @param bool $trim Wether or not to trim quotes from the output
     *                   return string
     *
     * @return string
     * @deprecated
     */
    public static function db_time($unixtime, $trim = true)
    {
        $db = self::get_db();
        $tmp = $db->DbTimeStamp($unixtime);
        if ($trim) {
            $tmp = trim($tmp, "'");
        }
        return $tmp;
    }

    /**
     * Given a datatime string convert it to a unix timestamp
     *
     * @param string|null $str The datetime string
     * @return int
     */
    public static function unix_time($str = '')
    {
        // snarfed from smarty.
        $str = trim((string)$str);
        $time = '';
        if (!$str) {
            // use "now":
            $time = time();
        } elseif (preg_match('/^\d{14}$/', $str)) {
            // it is mysql timestamp format of YYYYMMDDHHMMSS?
            $time = mktime(
                substr($str, 8, 2),
                substr($str, 10, 2),
                substr($str, 12, 2),
                substr($str, 4, 2),
                substr($str, 6, 2),
                substr($str, 0, 4)
            );
        } elseif (preg_match("/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/", $str, $dt)) {
            $time = mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]);
        } elseif (is_numeric($str)) {
            // it is a numeric string, we handle it as timestamp
            $time = (int)$str;
        } else {
            // strtotime should handle it
            $time = strtotime($str);
            if ($time == -1 || $time === false) {
                // strtotime() was not able to parse $str, use "now":
                // but try one more thing
                [$p1,$p2] = explode(' ', $str, 2);

                $db = self::get_db();
                $time = $db->UnixTimeStamp($str);
                if (!$time) {
                    $time = time();
                }
            }
        }

        return $time;
    }

    /**
     * Return the list of image extensions that a user is allowed to upload
     *
     * @deprecated instead use FileTypeHelper
     * @return string
     */
    public static function get_image_extensions()
    {
        return self::$_instance->settings->imageextensions;
    }

    /**
     * A quick wrapper around cms_utils::get_module that will try to use a
     * module name saved in tmpdata
     *
     * @deprecated
     * @see cms_utils::get_module
     * @param string $module_name Default ''
     * @param string $version The desired module version Default ''
     * @return mixed The module object or null
     */
    public static function get_module($module_name = '', $version = '')
    {
        if (!$module_name) {
            $module_name = cms_utils::get_app_data('module'); // might be null
            $version = '';
        }
        if ($module_name) {
            return cms_utils::get_module($module_name, $version);
        }
        return null;
    }

    /**
     * Get the SmartyExt module object
     *
     * @see xt_utils::get_module
     * @return SmartyExt The SmartyExt module object.
     */
    public static function get_xt()
    {
        return self::$_instance->mod;
    }

    /**
     * Given a file name, return its mime type
     *
     * Requires the fileinfo php extension (which is included by default since PHP 5.3)
     * Throws an exception if the fileinfo extension is not available.
     *
     * @param string $filename - The file name.
     * @return string The returned mime type or null
     */
    public static function get_mime_type($filename)
    {
        if (!function_exists('finfo_open')) {
            throw new RuntimeException('Problem with host setup.  the finfo_open function does not exist');
        }
        if ((is_file($filename) && is_readable($filename)) || startswith($filename, 'http')) {
            $fh = finfo_open(FILEINFO_MIME_TYPE);
            if ($fh) {
                $mime_type = finfo_file($fh, $filename);
                finfo_close($fh);
                return $mime_type;
            }
        }
    }

    /* *
     * Send a text file (like a CSV file) to the browser and exit
     * This is a convenience method.  It also handles clearing any data that has already been sent to output buffers.
     *
     * @param string $data The output data
     * @param string $content_type The output MIME type
     * @param string $filename The output filename
     */
/*    public static function send_data_and_exit($data, $content_type = 'text/plain', $filename = 'report.txt')
    {
        $handlers = ob_list_handlers();
        for ($cnt = 0; $cnt < sizeof($handlers); ++$cnt) {
            ob_end_clean();
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Description: File Transfer');
        header('Content-Type: '.$content_type);
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Content-Transfer-Encoding: binary');
        //header('Content-Length: ' . count($data));

        // send the data
        print($data);

        // don't allow any further processing.
        exit;
    }
*/
    /* *
     * View a file in the browser.
     * This is a convenience method.  It also handles clearing any data that has already been sent to output buffers.
     *
     * @param string $file The absolute path to the output file
     * @param string $mime_type The output mime type
     * @param string $filename The output filename (suggested to the browser)
     */
/*    public static function view_file_and_exit($file, $mime_type = '', $filename = '')
    {
        if (!file_exists($file)) {
            return false;
        }

        if (!$mime_type) {
            $mime_type = self::get_mime_type($file);
            if ($mime_type == 'unknown') {
                $mime_type = 'application/octet-stream';
            }
        }
        if (!$filename) {
            $filename = $file;
        }
        $filename = basename($filename);

        $handlers = ob_list_handlers();
        for ($cnt = 0; $cnt < sizeof($handlers); ++$cnt) {
            ob_end_clean();
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        //header('Content-Description: File Transfer');
        header('Content-Type: '.$mime_type);
        header("Content-Disposition: inline; filename=\"$filename\"");
        header('Content-Transfer-Encoding: binary');
/*
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . filesize($file));
* /

        $chunksize = 65535;
        $handle = fopen($file, 'rb');
        $contents = '';
        do {
            $data = fread($handle, $chunksize);
            if (strlen($data) == 0) {
                break;
            }
            print($data);
        } while (true);
        fclose($handle);

        // don't allow any more processing
        exit;
    }
*/
    /* *
     * Download a file to the browser, and then exit the current request
     * This method is useful when the user has requested to download a large file.
     * This is a convenience method.  It also handles clearing any data that has already been sent to output buffers.
     *
     * @param string $file The absolute path to the output file
     * @param int $chunksize The amount of data to read from the file at one time
     * @param string $mime_type The output mime type
     * @param string $filename The output filename (suggested to the browser)
     */
/*    public static function send_file_and_exit($file, $chunksize = 65535, $mime_type = '', $filename = '')
    {
        if (!file_exists($file)) {
            return false;
        }

        if (empty($mime_type)) {
            $mime_type = self::get_mime_type($file);
            if ($mime_type == 'unknown') {
                $mime_type = 'application/octet-stream';
            }
        }

        if (empty($filename)) {
            $filename = $file;
        }
        $filename = basename($filename);

        $handlers = ob_list_handlers();
        for ($cnt = 0; $cnt < sizeof($handlers); ++$cnt) {
            ob_end_clean();
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Description: File Transfer');
        header('Content-Type: '.$mime_type);
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file));

        $handle = fopen($file, 'rb');
        $contents = '';
        do {
            $data = fread($handle, $chunksize);
            if (strlen($data) == 0) {
                break;
            }
            print($data);
        } while (true);
        fclose($handle);

        // don't allow any more processing
        exit();
    }
*/
    /* *
     * Given an output array or object, encode it to json, and exit.
     * This is a convenience method.  It also handles clearing any data that has already been sent to output buffers.
     *
     * @param mixed $output
     */
/*    public static function send_ajax_and_exit($output)
    {
        $handlers = ob_list_handlers();
        for ($cnt = 0; $cnt < sizeof($handlers); ++$cnt) {
            ob_end_clean();
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/json');
        $output = json_encode($output);
        echo $output;
        exit;
    }
*/
    /**
     * Use various methods to return the user's real IP address.
     * including when using a proxy server.
     *
     * @return string
     */
    public static function get_real_ip()
    {
        $ip = null;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Given a string input that theoretically represents a boolean value
     * return either true or false.
     *
     * @param mixed $in input value
     * @param boolean $strict Whether strict testing should be used.
     * @return bool
     */
    public static function to_bool($in, $strict = false)
    {
        if (is_bool($in) && $in) {
            return true;
        }
        if (is_bool($in) && !$in) {
            return false;
        }
        $in = strtolower($in);
        if (in_array($in, ['1', 'y', 'yes', 'true', 't', 'on'])) {
            return true;
        }
        if (in_array($in, ['0', 'n', 'no', 'false', 'f', 'off'])) {
            return false;
        }
        if ($strict) {
            return false;
        }
        return ($in ? true : false);
    }

    /* *
     * Get the singleton xt_browser object.
     *
     * @return xt_browser
     */
/*    public static function get_browser()
    {
        static $_browser = null;

        if ($_browser == null) {
            $_browser = new xt_browser();
        }
        return $_browser;
    }
*/
    /**
     * A platform independent fgets utility.
     * This method understands MAC (\r) as well as DOS/Unix line endings
     *
     * @see fgets
     * @param resource $fh
     * @return string or null
     */
    public static function fgets($fh)
    {
        if (!$fh || !is_resource($fh)) {
            return;
        }
        $pos1 = ftell($fh);

        $line = fgets($fh);
        if (strpos($line, "\r") === false) {
            return $line;
        }

        // there are line endings in here
        // line is probably a crappy mac line.
        $len1 = strlen($line);
        $pos = strpos($line, "\r\n");
        if ($pos !== false) {
            $len = 2;
        } else {
            $pos = strpos($line, "\r");
            $len = 1;
        }

        $line = substr($line, 0, $pos);
        fseek($fh, ($len1 - $pos - $len) * -1, SEEK_CUR);
        return $line;
    }

    /**
     * Return the first non null argument.
     * This method accepts a variable number of arguments.
     *
     * @return mixed The first non null argument
     */
    public static function coalesce()
    {
        $args = func_get_args();
        if (!is_array($args) || count($args) == 0) {
            return;
        }

        for ($i = 0, $iMax = count($args); $i < $iMax; ++$i) {
            if (!is_null($args[$i])) {
                return $args[$i];
            }
        }
    }

    /**
     * Retrieve the value of the specified key in a properties container,
     * or a default value if the key does not exist in the container
     * @see also get_parameter_value()
     *
     * @param mixed $params array | ArrayAccess object The container to be interrogated
     * @param string $key The wanted key
     * @param mixed $dflt The default value
     * @return mixed The value of the named element in the container, or the default
     */
    public static function get_param($params, $key, $dflt = null)
    {
        if (isset($params[$key])) {
            $tmp = $params[$key];
            if (is_string($tmp)) {
                return trim($tmp);
            }
            return $tmp; // might be null
        }
        return $dflt; // might be null
    }

    /**
     * Retrieve the value of the specified key in a properties container,
     * or a default value if the key does not exist in the container or its
     * value is empty
     *
     * @param mixed $params array | ArrayAccess object The container to be interrogated
     * @param string $key The wanted key
     * @param mixed $dflt The default value
     * @return mixed The value of the named element in the container, or the default
     */
    public static function get_empty_param($params, $key, $dflt = null)
    {
        if (isset($params[$key]) && !empty($params[$key])) {
            $tmp = $params[$key];
            if (is_string($tmp)) {
                $tmp = trim($tmp);
            }
            if( !empty($tmp) ) return $tmp;
        }
        return $dflt; // might be null
    }

    /**
     * Given a src specification attempt to resolve it into a filename on the server
     *
     * algorithm:
     *  1.  Check for an absolute filename
     *  2.  Test if the string starts with the uploads url
     *      - replace with uploads path
     *      - check if file exists
     *  3.  Test if the string starts with the root url
     *      - replace with root path
     *      - check if file exists
     *  4.  If string starts with /
     *      - prepend root path
     *      - check if file exists
     *  5.  assume string is relative to uploads path
     *      - checkk if file exists
     *  6.  Test if string starts with the ssl url
     *      - replace with root path
     *      - check if file exists
     *
     * @param string $src the source
     * @return string The filename (if possible).
     */
    public static function src_to_file($src)
    {
        $src = urldecode((string)$src);
        //$config = cmsms()->GetConfig();
        $config = config::GetInstance();

        $srcfile = null;
        if (is_file($src)) {
            $srcfile = $src; // caller provided the complete path to the file.
        }

        if (!$srcfile && startswith($src, $config['uploads_url'])) {
            $tmp = str_replace($config['uploads_url'], $config['uploads_path'], $src);
            if (file_exists($tmp)) {
                $srcfile = $tmp;
            }
        }
        if (!$srcfile && startswith($src, $config['root_url'])) {
            $tmp = str_replace($config['root_url'], $config['root_path'], $src);
            if (file_exists($tmp)) {
                $srcfile = $tmp;
            }
        }
        if (!$srcfile && startswith($src, '/')) {
            $tmp = cms_join_path($config['root_path'], $src);
            if (file_exists($tmp)) {
                $srcfile = $tmp;
            }
        }
        if (!$srcfile) {
            $tmp = cms_join_path($config['uploads_path'], $src);
            if (file_exists($tmp)) {
                $srcfile = $tmp;
            }
        }
        if (!$srcfile && isset($config['ssl_url']) && startswith($src, $config['ssl_url'])) {
            $tmp = str_replace($config['ssl_url'], $config['root_path'], $src);
            if (file_exists($tmp)) {
                $srcfile = $tmp;
            }
        }

        return $srcfile;
    }

    /**
     * Test if the current request is for a secure connection
     *
     * @return bool
     */
    public static function ssl_request()
    {
        if (!isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
            return false;
        }
        return true;
    }

    /**
     * Convert a filename to a URL.
     * If an absolute path is specified tests are done to compare the input to the image uploads path, the uploads path or the root path of the system.
     * If a relative URL path  is passed a file relative to the root url is assumed.
     *
     * @param string $file the filename to convert to a URL
     * @param bool $force_ssl Force the output url to use HTTPS
     * @return string or null
     */
    public static function file_to_url($file, $force_ssl = false)
    {
        $url = null;
        if (!is_file($file)) {
            return $url;
        }

        $config = config::GetInstance();
        if (startswith($file, $config['image_uploads_path'])) {
            $url = str_replace($config['image_uploads_path'], $config['image_uploads_url'], $file);
        } elseif (startswith($file, $config['uploads_path'])) {
            if (self::ssl_request() || $force_ssl) {
                $url = str_replace($config['uploads_path'], $config['ssl_uploads_url'], $file);
            } else {
                $url = str_replace($config['uploads_path'], $config['uploads_url'], $file);
            }
        } elseif (startswith($file, $config['root_path'])) {
            if (self::ssl_request() || $force_ssl) {
                $url = str_replace($config['root_path'], $config['ssl_url'], $file);
            } else {
                $url = str_replace($config['root_path'], $config['root_url'], $file);
            }
        }

        return $url;
    }

    /**
     * An experimental method that attempts to determine if there is enough available PHP memory for a given operation.
     *
     * @param int $needed_memory The estimated amount of memory required
     * @param float $fudge The fudge factor (multiplier) used to buffer available memory.
     * @return bool
     */
    public static function have_enough_memory($needed_memory, $fudge = 2.0)
    {
        $needed_memory = abs((int)$needed_memory);
        $fudge = min(10, max(1, abs((float)$fudge)));
        if ($needed_memory == 0) {
            return true;
        }
        $needed_memory *= $fudge;

        $diff = self::get_available_memory() - $needed_memory;
        if ($diff > 0) {
            return true;
        }
        return false;
    }

    /**
     * An experimental method to determine the amount of available PHP memory remaining
     *
     * @return int
     */
    public static function get_available_memory()
    {
        $MB = 1024 * 1024;
        $memory_limit = ini_get('memory_limit');
        $memory_limit = intval($memory_limit);
        $memory_limit = max(1, $memory_limit);
        $memory_limit *= $MB;

        return $memory_limit - memory_get_usage();
    }

    /**
     * Pretty up, sanitize, and clean user entered html code.
     *
     * @param string $html
     * @return string
     */
    public static function clean_input_html($html)
    {
        require_once __DIR__.'/htmLawed.php';
        return htmLawed($html, ['safe' => 1, 'keep_bad' => 0, 'deny_attribute' => 'style, id, on*, data*']);
    }

    /**
     * Convert a string representing a float value into a float
     *
     * @deprecated
     * @param string $floatString the input string
     * @param string $thousands_sep The thousands separator
     * @param string $decimal_pt The decimal point
     */
    public static function parse_float($floatString, $thousands_sep = '', $decimal_pt = '')
    {
        $LocaleInfo = localeconv();
        if (!$thousands_sep) {
            $thousands_sep = $LocaleInfo['mon_thousands_sep'];
        }
        if (!$thousands_sep) {
            $thousands_sep = $LocaleInfo['thousands_sep'];
        }
        if (!$thousands_sep) {
            $thousands_sep = ',';
        }
        if (!$decimal_pt) {
            $decimal_pt = $LocaleInfo['mon_thousands_sep'];
        }
        if (!$decimal_pt) {
            $decimal_pt = $LocaleInfo['decimal_point'];
        }
        if (!$decimal_pt) {
            $decimal_pt = '.';
        }
        $floatString = str_replace($thousands_sep, '', $floatString);
        $floatString = str_replace($decimal_pt, '.', $floatString);
        return (float) floatval($floatString);
    }

    /**
     * A utility function to encrypt parameters for passing through different URL's.
     *
     * This method accepts a parameter array, encrypts it, and returns a parameter array with a single element: _d.
     *
     * @param array $params an associative array
     * @return array
     */
    public static function encrypt_params(array $params)
    {
        $key = CMS_VERSION.__FILE__;
        $out = [];
        $out['_d'] = base64_encode(xt_encrypt::encrypt($key, serialize($params)));
        return $out;
    }

    /**
     * Decrypt previously encrypted parameters.
     *
     * This method accepts a parameter array (the output from its companion method) and decrypts the input.
     *
     * @param array $params an encrypted associative array with at least one element: _d.
     * @return array
     */
    public static function decrypt_params(array $params)
    {
        $key = CMS_VERSION.__FILE__;
        if (!isset($params['_d'])) {
            return $params;
        }

        $tmp = xt_encrypt::decrypt($key, base64_decode($params['_d']));
        $tmp = unserialize($tmp);
        unset($params['_d']);
        $tmp = array_merge($params, $tmp);
        return $tmp;
    }

    /**
     * Assist doing certain tasks only once per day.
     * This method will convert the key into a preference, and then check the value of that preference
     * if it is more than 24 hours since the last time this method was called for this preference
     * then the value of the preference is updated to the current time and FALSE is returned.
     * Otherwise TRUE is returned.
     *
     * @param string $key
     * @return bool
     */
    public static function done_today($key)
    {
        $key = md5(__METHOD__.$key);
        $val = cms_siteprefs::get($key);
        if (time() - $val < 24 * 3600) {
            return true;
        }
        cms_siteprefs::set($key, time());
        return false;
    }

    /**
     * Swap to variables.
     * Probably should not be used where cloning may be required.
     *
     * @param mixed $a
     * @param mixed $b
     */
    public static function swap(&$a, &$b)
    {
        $tmp = $a;
        $a = $b;
        $b = $tmp;
    }

    /**
     * Dump an exception to the error log.
     *
     * @param Exception $e
     */
    public static function log_exception(Exception $e)
    {
        $out = '-- EXCEPTION DUMP --'."\n";
        $out .= 'TYPE: '.get_class($e)."\n";
        $out .= 'MESSAGE: '.$e->getMessage()."\n";
        $out .= 'FILE: '.$e->getFile().':'.$e->GetLine()."\n";
        $out .= "TRACE:\n";
        $out .= $e->getTraceAsString();
        debug_to_log($out, '-- '.__METHOD__.' --');
    }

    /**
     * Create a unique guid
     *
     * @return string
     */
    public static function create_guid()
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        }
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * Given a thumbnail, return the path to the thumbnail if it exists.
     *
     * @param string $filename
     * @return string|null
     */
    public static function find_image_thumbnail($filename)
    {
        $tf = self::get_thumbnail_name($filename);
        if ($tf && is_file($tf)) {
            return $tf;
        }
        return '';
    }

    /**
     * A convenience function to generate a standardized thumbnail for an image.
     *
     * @param $filename The complete path to the image file
     * @return string|null The path to the thumbnail file.
     */
    public static function create_image_thumbnail($filename)
    {
        $tf = self::get_thumbnail_name($filename);
        if ($tf) {
            xt_image::transform_image($filename, $tf, 100);
            return $tf;
        }
        return '';
    }

    /**
     * Generate an URL that obfuscates a filename, but allows download.
     *
     * @param int $page_id A page id to use for displaying the file.
     * @param string $filename The complete pathname
     * @param bool $download  Whether the file should be forced to download, or whether the browser can handle it
     * @return string A url that allows view or download of the image, but obfuscates the file name and path.
     */
    public static function get_obfuscated_file_url($page_id, $filename, $download = false)
    {
        $page_id = (int) $page_id;
        $filename = trim($filename);
        $download = (bool) $download;
        if ($page_id < 1 || !$filename || !is_file($filename)) {
            return;
        }
        $mod = self::$_instance->mod;
        $url = $mod->create_url(
            'cntnt01',
            'getfile',
            $page_id,
            self::encrypt_params(['file' => $filename, 'download' => $download])
        ) . '&showtemplate=false';
        return $url;
    }

    /**
     * Create HTML elements for a form that contain CSRF keys.
     * this method also stores data in the session for use by the valid_form_csrf() method.
     *
     * @see xt_utils::valid_form_csrf
     * @return string HTML form elements as a string.
     */
    public static function create_csrf_inputs()
    {
        // use this when creating the form.
        $name = self::create_guid();
        $name = str_replace('-', '', $name);
        $token = self::create_guid();
        $_SESSION['xt_csrf_'.$name] = $token;

        $fmt = '<input type="hidden" name="%s" value="%s"/>';
        $out = sprintf($fmt, 'xt_csrf_name', $name);
        $out .= sprintf($fmt, 'xt_csrf_token', $token);
        return $out;
    }

    /**
     * Given variables available in $_POST generated by the create_csrf_inputs() method
     * validate their values with the data stored in the session.
     *
     * This method will always return true if 'ignore_xt_csrf' is enabled in the config.php
     *
     * @see xt_utils::create_csrf_inputs()
     * @return true|null True if the CSRF data in $_POST matches that in the session.  Null/fallse otherwise.
     */
    public static function valid_form_csrf()
    {
        //$config = cms_utils::get_config();
        $config = config::GetInstance();

        if ($config['ignore_xt_csrf']) { //this is the only usage of non-CMSMS config data in SmartyExt
            return true;
        }

        $name = get_parameter_value($_POST, 'xt_csrf_name');
        if (!$name) {
            return false;
        }

        $sess_token = null;
        $key = 'xt_csrf_'.$name;

        if (isset($_SESSION[$key])) {
            $sess_token = $_SESSION[$key];
            unset($_SESSION[$key]);
        }

        $token = get_parameter_value($_POST, 'xt_csrf_token');
        if (!$token || !$sess_token) {
            return false;
        }

        if ($token !== $sess_token) {
            return false;
        }

        return true;
    }

    /**
     * Convert simple shallow objets from one class to another.
     *
     * based on www.geeksforgeeks.org article:
     * "Type Casting and Conversion of an Object to an Object of other class"
     *
     * @param $object
     * @param $final_class
     *
     * @return mixed
     */
    public static function convert_obj_class($object, $final_class)
    {
        return unserialize(
            sprintf(
                'O:%d:"%s"%s',
                strlen($final_class),
                $final_class,
                strstr(strstr(serialize($object), '"'), ':')
            )
        );
    }

    /**
     * Get a handle to an alternate database connection that supports better error handling.
     *
     * @return Database
     */
    private static function _get_db()
    {
        static $_obj = null;
        if (!$_obj) {
            $_error_handler = function(Database $conn, $errtype, $error_number, $error_message) {
                throw new CmsSQLErrorException($error_message . ' -- ' . $conn->ErrorMsg(), $error_number);
            };
            $_obj = db_compatibility::init(cms_config::get_instance());
            $_obj->SetErrorHandler($_error_handler);
        }

        return $_obj;
    }

    /**
     * Given a complete path and filename get a proposed filename for the thumbnail.
     *
     * @param string $filename A complete path to an image file
     * @return or null
     */
    private static function get_thumbnail_name($filename)
    {
        if (!$filename || !is_file($filename)) {
            return null;
        }
        $dn = dirname($filename);
        $bn = basename($filename);
        if (!$dn || !is_dir($dn)) {
            return null;
        }

        if (startswith($bn, 'thumb_')) {
            return $filename;
        }
        $sep = DIRECTORY_SEPARATOR;
        return "$dn{$sep}thumb_{$bn}";
    }
} // class
