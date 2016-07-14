<?php
/**
 * UdgerParser - Local parser class
 * 
 * @package    UdgerParser
 * @author     The Udger.com Team (info@udger.com)
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link       http://udger.com/products/local_parser
 */

namespace Udger;

use Psr\Log\LoggerInterface;

/**
 * udger.com Local Parser Class
 * 
 * @package UdgerParser
 */
class Parser implements ParserInterface
{
    
    /**
     * Default timeout for network requests
     * 
     * @type integer
     */
    protected $timeout = 60; // in seconds

    /**
     * Api URL
     * 
     * @type string
     */
    protected $api_url = 'http://api.udger.com/v3';

    /**
     * Path to the data file
     * 
     * @type string
     */
    protected $data_dir;

    /**
     * Personal access key
     * 
     * @type string
     */
    protected $access_key;

    /**
     * IP address for parse
     * 
     * @type string
     */
    protected $ip;

    /**
     * Useragent string for parse
     * 
     * @type string
     */
    protected $ua;

    /**
     * DB link
     * 
     * @type object
     */
    protected $dbdat;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Check your subscription
     * 
     * @return array
     */
    public function account()
    {
        $this->logger->debug("account: start");

        if (empty($this->access_key)) {
            throw new \Exception("access key not set");
        }

        $accountUrl = sprintf("%s/%s", $this->api_url, "account");
        $client = new \GuzzleHttp\Client();

        $result = $client->post($accountUrl, array(
            'multipart' => array(
                array(
                    'name' => 'accesskey',
                    'contents' => $this->access_key
                )
            ),
            'timeout' => $this->timeout
        ));

        $contents = $result->getBody()->getContents();
        $data = json_decode($contents, true);

        // check for non zero staus codes
        if (isset($data['flag']) && $data['flag'] > 0) {
            throw new \Exception($data['errortext']);
        }

        return $data;
    }

    /**
     * Set the useragent string
     * 
     * @param string
     * @return bool
     */
    public function setUA($ua)
    {
        $this->logger->debug('setting: set useragent string to ' . $ua);
        $this->ua = $ua;
        return true;
    }

    /**
     * Set the IP address
     * 
     * @param string
     * @return bool
     */
    public function setIP($ip)
    {
        $this->logger->debug('setting: set IP address to ' . $ip);
        $this->ip = $ip;
        return true;
    }

    /**
     * Parse the useragent string and/or IP
     * 
     * @return array
     */
    public function parse()
    {
        $this->setDBdat();

        // validate
        if (is_null($this->dbdat) === true) {
            $this->logger->debug('db: data file not found, download the data manually from http://data.udger.com/');
            return array('flag' => 3,
                'errortext' => 'data file not found');
        }

        //ret values
        $ret = array('user_agent' =>
            array('ua_string' => '',
                'ua_class' => '',
                'ua_class_code' => '',
                'ua' => '',
                'ua_version' => '',
                'ua_version_major' => '',
                'ua_uptodate_current_version' => '',
                'ua_family' => '',
                'ua_family_code' => '',
                'ua_family_homepage' => '',
                'ua_family_vendor' => '',
                'ua_family_vendor_code' => '',
                'ua_family_vendor_homepage' => '',
                'ua_family_icon' => '',
                'ua_family_icon_big' => '',
                'ua_family_info_url' => '',
                'ua_engine' => '',
                'os' => '',
                'os_code' => '',
                'os_homepage' => '',
                'os_icon' => '',
                'os_icon_big' => '',
                'os_info_url' => '',
                'os_family' => '',
                'os_family_code' => '',
                'os_family_vendor' => '',
                'os_family_vendor_code' => '',
                'os_family_vendor_homepage' => '',
                'device_class' => '',
                'device_class_code' => '',
                'device_class_icon' => '',
                'device_class_icon_big' => '',
                'device_class_info_url' => '',
                'crawler_last_seen' => '',
                'crawler_category' => '',
                'crawler_category_code' => '',
                'crawler_respect_robotstxt' => ''
            ),
            'ip_address' =>
            array('ip' => '',
                'ip_ver' => '',
                'ip_classification' => '',
                'ip_classification_code' => '',
                'ip_hostname' => '',
                'ip_last_seen' => '',
                'ip_country' => '',
                'ip_country_code' => '',
                'ip_city' => '',
                'crawler_name' => '',
                'crawler_ver' => '',
                'crawler_ver_major' => '',
                'crawler_family' => '',
                'crawler_family_code' => '',
                'crawler_family_homepage' => '',
                'crawler_family_vendor' => '',
                'crawler_family_vendor_code' => '',
                'crawler_family_vendor_homepage' => '',
                'crawler_family_icon' => '',
                'crawler_family_info_url' => '',
                'crawler_last_seen' => '',
                'crawler_category' => '',
                'crawler_category_code' => '',
                'crawler_respect_robotstxt' => '',
                'datacenter_name' => '',
                'datacenter_name_code' => '',
                'datacenter_homepage' => ''
            )
        );

        if (!empty($this->ua)) {
            $this->logger->debug("parse useragent string: START (useragent: " . $this->ua . ")");
            $client_id = 0;
            $client_class_id = -1;
            $os_id = 0;
            $deviceclass_id = 0;
            $ret['user_agent']['ua_string'] = $this->ua;
            $ret['user_agent']['ua_class'] = 'Unrecognized';
            $ret['user_agent']['ua_class_code'] = 'unrecognized';

            // crawler            
            $q = $this->dbdat->query("SELECT udger_crawler_list.id as botid,name,ver,ver_major,last_seen,respect_robotstxt,family,family_code,family_homepage,family_icon,vendor,vendor_code,vendor_homepage,crawler_classification,crawler_classification_code
                                          FROM udger_crawler_list
                                          LEFT JOIN udger_crawler_class ON udger_crawler_class.id=udger_crawler_list.class_id
                                          WHERE ua_string='" . $this->ua . "'");

            if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                $this->logger->debug("parse useragent string: crawler found");

                $client_class_id = 99;
                $ret['user_agent']['ua_class'] = 'Crawler';
                $ret['user_agent']['ua_class_code'] = 'crawler';
                $ret['user_agent']['ua'] = $r['name'];
                $ret['user_agent']['ua_version'] = $r['ver'];
                $ret['user_agent']['ua_version_major'] = $r['ver_major'];
                $ret['user_agent']['ua_family'] = $r['family'];
                $ret['user_agent']['ua_family_code'] = $r['family_code'];
                $ret['user_agent']['ua_family_homepage'] = $r['family_homepage'];
                $ret['user_agent']['ua_family_vendor'] = $r['vendor'];
                $ret['user_agent']['ua_family_vendor_code'] = $r['vendor_code'];
                $ret['user_agent']['ua_family_vendor_homepage'] = $r['vendor_homepage'];
                $ret['user_agent']['ua_family_icon'] = $r['family_icon'];
                $ret['user_agent']['ua_family_info_url'] = "https://udger.com/resources/ua-list/bot-detail?bot=" . $r['family'] . "#id" . $r['botid'];
                $ret['user_agent']['crawler_last_seen'] = $r['last_seen'];
                $ret['user_agent']['crawler_category'] = $r['crawler_classification'];
                $ret['user_agent']['crawler_category_code'] = $r['crawler_classification_code'];
                $ret['user_agent']['crawler_respect_robotstxt'] = $r['respect_robotstxt'];
            } else {
                // client
                $q = $this->dbdat->query("SELECT class_id,client_id,regstring,name,name_code,homepage,icon,icon_big,engine,vendor,vendor_code,vendor_homepage,uptodate_current_version,client_classification,client_classification_code
                                              FROM udger_client_regex
                                              JOIN udger_client_list ON udger_client_list.id=udger_client_regex.client_id
                                              JOIN udger_client_class ON udger_client_class.id=udger_client_list.class_id
                                              ORDER BY sequence ASC");
                while ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                    if (@preg_match($r["regstring"], $this->ua, $result)) {
                        $this->logger->debug("parse useragent string: client found");
                        $client_id = $r['client_id'];
                        $client_class_id = $r['class_id'];
                        $ret['user_agent']['ua_class'] = $r['client_classification'];
                        $ret['user_agent']['ua_class_code'] = $r['client_classification_code'];
                        if (isset($result[1])) {
                            $ret['user_agent']['ua'] = $r['name'] . " " . $result[1];
                            $ret['user_agent']['ua_version'] = $result[1];
                            $ver_major = explode(".", $result[1]);
                            $ret['user_agent']['ua_version_major'] = $ver_major[0];
                        } else {
                            $ret['user_agent']['ua'] = $r['name'];
                            $ret['user_agent']['ua_version'] = '';
                            $ret['user_agent']['ua_version_major'] = '';
                        }
                        $ret['user_agent']['ua_uptodate_current_version'] = $r['uptodate_current_version'];
                        $ret['user_agent']['ua_family'] = $r['name'];
                        $ret['user_agent']['ua_family_code'] = $r['name_code'];
                        $ret['user_agent']['ua_family_homepage'] = $r['homepage'];
                        $ret['user_agent']['ua_family_vendor'] = $r['vendor'];
                        $ret['user_agent']['ua_family_vendor_code'] = $r['vendor_code'];
                        $ret['user_agent']['ua_family_vendor_homepage'] = $r['vendor_homepage'];
                        $ret['user_agent']['ua_family_icon'] = $r['icon'];
                        $ret['user_agent']['ua_family_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['ua_family_info_url'] = "https://udger.com/resources/ua-list/browser-detail?browser=" . $r['name'];
                        $ret['user_agent']['ua_engine'] = $r['engine'];
                        break;
                    }
                }
                // os
                $q = $this->dbdat->query("SELECT os_id,regstring,family,family_code,name,name_code,homepage,icon,icon_big,vendor,vendor_code,vendor_homepage
                                              FROM udger_os_regex
                                              JOIN udger_os_list ON udger_os_list.id=udger_os_regex.os_id
                                              ORDER BY sequence ASC");
                while ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                    if (@preg_match($r["regstring"], $this->ua, $result)) {
                        $this->logger->debug("parse useragent string: os found");
                        $os_id = $r['os_id'];
                        $ret['user_agent']['os'] = $r['name'];
                        $ret['user_agent']['os_code'] = $r['name_code'];
                        $ret['user_agent']['os_homepage'] = $r['homepage'];
                        $ret['user_agent']['os_icon'] = $r['icon'];
                        $ret['user_agent']['os_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['os_info_url'] = "https://udger.com/resources/ua-list/os-detail?os=" . $r['name'];
                        $ret['user_agent']['os_family'] = $r['family'];
                        $ret['user_agent']['os_family_code'] = $r['family_code'];
                        $ret['user_agent']['os_family_vendor'] = $r['vendor'];
                        $ret['user_agent']['os_family_vendor_code'] = $r['vendor_code'];
                        $ret['user_agent']['os_family_vendor_homepage'] = $r['vendor_homepage'];
                        break;
                    }
                }
                // client_os_relation
                if ($os_id == 0 AND $client_id != 0) {
                    $q = $this->dbdat->query("SELECT os_id,family,family_code,name,name_code,homepage,icon,icon_big,vendor,vendor_code,vendor_homepage
                                                  FROM udger_client_os_relation
                                                  JOIN udger_os_list ON udger_os_list.id=udger_client_os_relation.os_id
                                                  WHERE client_id=" . $client_id . " ");
                    if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                        $this->logger->debug("parse useragent string: client os relation found");
                        $os_id = $r['os_id'];
                        $ret['user_agent']['os'] = $r['name'];
                        $ret['user_agent']['os_code'] = $r['name_code'];
                        $ret['user_agent']['os_homepage'] = $r['homepage'];
                        $ret['user_agent']['os_icon'] = $r['icon'];
                        $ret['user_agent']['os_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['os_info_url'] = "https://udger.com/resources/ua-list/os-detail?os=" . $r['name'];
                        $ret['user_agent']['os_family'] = $r['family'];
                        $ret['user_agent']['os_family_code'] = $r['family_code'];
                        $ret['user_agent']['os_family_vendor'] = $r['vendor'];
                        $ret['user_agent']['os_family_vendor_code'] = $r['vendor_code'];
                        $ret['user_agent']['os_family_vendor_homepage'] = $r['vendor_homepage'];
                    }
                }
                //device
                $q = $this->dbdat->query("SELECT deviceclass_id,regstring,name,name_code,icon,icon_big
                                              FROM udger_deviceclass_regex
                                              JOIN udger_deviceclass_list ON udger_deviceclass_list.id=udger_deviceclass_regex.deviceclass_id
                                              ORDER BY sequence ASC");

                while ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                    if (@preg_match($r["regstring"], $this->ua, $result)) {
                        $this->logger->debug("parse useragent string: device found by regex");
                        $deviceclass_id = $r['deviceclass_id'];
                        $ret['user_agent']['device_class'] = $r['name'];
                        $ret['user_agent']['device_class_code'] = $r['name_code'];
                        $ret['user_agent']['device_class_icon'] = $r['icon'];
                        $ret['user_agent']['device_class_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['device_class_info_url'] = "https://udger.com/resources/ua-list/device-detail?device=" . $r['name'];
                        break;
                    }
                }
                if ($deviceclass_id == 0 AND $client_class_id != -1) {
                    $q = $this->dbdat->query("SELECT deviceclass_id,name,name_code,icon,icon_big 
                                                  FROM udger_deviceclass_list
                                                  JOIN udger_client_class ON udger_client_class.deviceclass_id=udger_deviceclass_list.id
                                                  WHERE udger_client_class.id=" . $client_class_id . " ");
                    if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                        $this->logger->debug("parse useragent string: device found by deviceclass");
                        $deviceclass_id = $r['deviceclass_id'];
                        $ret['user_agent']['device_class'] = $r['name'];
                        $ret['user_agent']['device_class_code'] = $r['name_code'];
                        $ret['user_agent']['device_class_icon'] = $r['icon'];
                        $ret['user_agent']['device_class_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['device_class_info_url'] = "https://udger.com/resources/ua-list/device-detail?device=" . $r['name'];
                    }
                }
            }

            $this->logger->debug("parse useragent string: END, unset useragent string");
            $this->ua = '';
        }

        if (!empty($this->ip)) {
            $this->logger->debug("parse IP address: START (IP: " . $this->ip . ")");
            $ret['ip_address']['ip'] = $this->ip;
            $ipver = $this->validIP($this->ip);
            if ($ipver != 0) {
                if ($ipver == 6) {
                    $this->ip = inet_ntop(inet_pton($this->ip));
                    $this->logger->debug("compress IP address is:" . $this->ip);
                }

                $ret['ip_address']['ip_ver'] = $ipver;
                $q = $this->dbdat->query("SELECT udger_crawler_list.id as botid,ip_last_seen,ip_hostname,ip_country,ip_city,ip_country_code,ip_classification,ip_classification_code,
                                          name,ver,ver_major,last_seen,respect_robotstxt,family,family_code,family_homepage,family_icon,vendor,vendor_code,vendor_homepage,crawler_classification,crawler_classification_code
                                          FROM udger_ip_list
                                          JOIN udger_ip_class ON udger_ip_class.id=udger_ip_list.class_id
                                          LEFT JOIN udger_crawler_list ON udger_crawler_list.id=udger_ip_list.crawler_id
                                          LEFT JOIN udger_crawler_class ON udger_crawler_class.id=udger_crawler_list.class_id
                                          WHERE ip='" . $this->ip . "' ORDER BY sequence");
                if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                    $ret['ip_address']['ip_classification'] = $r['ip_classification'];
                    $ret['ip_address']['ip_classification_code'] = $r['ip_classification_code'];
                    $ret['ip_address']['ip_last_seen'] = $r['ip_last_seen'];
                    $ret['ip_address']['ip_hostname'] = $r['ip_hostname'];
                    $ret['ip_address']['ip_country'] = $r['ip_country'];
                    $ret['ip_address']['ip_country_code'] = $r['ip_country_code'];
                    $ret['ip_address']['ip_city'] = $r['ip_city'];

                    $ret['ip_address']['crawler_name'] = $r['name'];
                    $ret['ip_address']['crawler_ver'] = $r['ver'];
                    $ret['ip_address']['crawler_ver_major'] = $r['ver_major'];
                    $ret['ip_address']['crawler_family'] = $r['family'];
                    $ret['ip_address']['crawler_family_code'] = $r['family_code'];
                    $ret['ip_address']['crawler_family_homepage'] = $r['family_homepage'];
                    $ret['ip_address']['crawler_family_vendor'] = $r['vendor'];
                    $ret['ip_address']['crawler_family_vendor_code'] = $r['vendor_code'];
                    $ret['ip_address']['crawler_family_vendor_homepage'] = $r['vendor_homepage'];
                    $ret['ip_address']['crawler_family_icon'] = $r['family_icon'];
                    if ($r['ip_classification_code'] == 'crawler') {
                        $ret['ip_address']['crawler_family_info_url'] = "https://udger.com/resources/ua-list/bot-detail?bot=" . $r['family'] . "#id" . $r['botid'];
                    }
                    $ret['ip_address']['crawler_last_seen'] = $r['last_seen'];
                    $ret['ip_address']['crawler_category'] = $r['crawler_classification'];
                    $ret['ip_address']['crawler_category_code'] = $r['crawler_classification_code'];
                    $ret['ip_address']['crawler_respect_robotstxt'] = $r['respect_robotstxt'];
                } else {
                    $ret['ip_address']['ip_classification'] = 'Unrecognized';
                    $ret['ip_address']['ip_classification_code'] = 'unrecognized';
                }
                if ($ret['ip_address']['ip_ver'] == '4') {
                    $q = $this->dbdat->query("select name,name_code,homepage 
                                       FROM udger_datacenter_range
                                       JOIN udger_datacenter_list ON udger_datacenter_range.datacenter_id=udger_datacenter_list.id
                                       where iplong_from <= " . sprintf('%u', ip2long($ret['ip_address']['ip'])) . " AND iplong_to >= " . sprintf('%u', ip2long($ret['ip_address']['ip'])) . " ");
                    if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                        $ret['ip_address']['datacenter_name'] = $r['name'];
                        $ret['ip_address']['datacenter_name_code'] = $r['name_code'];
                        $ret['ip_address']['datacenter_homepage'] = $r['homepage'];
                    }
                }
            }

            $this->logger->debug("parse IP address: END, unset IP address");
            $this->ua = '';
        }
        return $ret;
    }

    /**
     * Open DB file 
     */
    protected function setDBdat()
    {
        if (is_null($this->dbdat)) {
            $path = $this->data_dir . "/udgerdb_v3.dat";
            $this->logger->debug("db: open file: $path");
            if (file_exists($path)) {
                $this->dbdat = new \SQLite3($path);
            } else {
                throw new \Exception("Data file not found: $path");
            }
        }
    }

    /**
     * Set the data directory
     * 
     * @param string
     * @return bool
     */
    public function setDataDir($data_dir)
    {
        $this->logger->debug('setting: set cache dir to ' . $data_dir);
        if (!file_exists($data_dir)) {
            @mkdir($data_dir, 0777, true);
        }

        if (!is_writable($data_dir) || !is_dir($data_dir)) {
            $this->logger->debug('Data dir(' . $data_dir . ') is not a directory or not writable');
            return false;
        }

        $data_dir = realpath($data_dir);
        $this->data_dir = $data_dir;
        return true;
    }

    /**
     * Set the account access key
     * 
     * @param string
     * @return bool
     */
    public function setAccessKey($access_key)
    {
        $this->logger->debug('setting: set accesskey to ' . $access_key);
        $this->access_key = $access_key;
        return true;
    }

    /**
     * Validate IP addresss
     * 
     * @param string $ip
     * @return integer
     */
    protected function validIP($ip)
    {
        if (substr_count($ip, ":") < 1) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $this->logger->debug("parse IP address: IP ver 4)");
                return 4;
            } else {
                $this->logger->debug("parse IP address: IP not valid)");
                return 0;
            }
        } else {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $this->logger->debug("parse IP address: IP ver 6");
                return 6;
            } else {
                $this->logger->debug("parse IP address: IP not valid)");
                return 0;
            }
        }
    }
}
