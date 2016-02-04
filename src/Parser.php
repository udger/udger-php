<?php
/**
 * UdgerParser - Local parser class
 *
 * PHP version 5
 *
 * @package    UdgerParser
 * @author     The Udger.com Team (info@udger.com)
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link       http://udger.com/products/local_parser
 */

namespace Udger;

use Psr\Log\LoggerInterface;

/**
 * udger.com Local Parser Class.
 * @package UdgerParser
 */
class Parser implements ParserInterface
{
    /**
     * How often to update the UAS database.
     * @type integer
     */
    protected $updateInterval = 86400; // in seconds - 86400 is 1 day
    
    /**
     * True to activate automatic db updates
     * @type boolean
     */
    protected $autoUpdate = true;

    /**
     * Whether debug output is enabled.
     * @type boolean
     */
    protected $debug = false;

    /**
     * Default timeout for network requests.
     * @type integer
     */
    protected $timeout = 60; // in seconds
    
    /**
     * api URL.
     * @type string
     */
    protected static $api_url = 'https://api.udger.com';
    
    /**
     * base URL udger data.
     * @type string
     */
    protected static $base_url = 'https://data.udger.com';
    
    /**
     * version file name
     * @type string
     */
    protected static $ver_filename = 'version';
    
    /**
     * data file name
     * @type string
     */
    protected static $data_filename = '/udgerdb_v3.dat';  
    
    /**
     * md5 hash file name
     * @type string
     */
    protected static $md5_filename = '/udgerdb_v3_dat.md5';  
    
    /**
     * resources URL.
     * @type string
     */
    protected static $resources_url = 'https://udger.com/resources/ua-list';
    
    /**
     * Path to store data file downloads to.
     * @type string
     */
    protected $data_dir = null;

    /**
     * Personal access key.
     * @type string
     */
    protected $access_key = null;
            
    /**
     * IP address for parse.
     * @type string
     */
    protected $ip = null;        
    
    /**
     * useragent string for parse.
     * @type string
     */
    protected $ua = null; 
    /**
     * db link.
     * @type object
     */
    protected $dbdat = null;
       
    /**
     * 
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;        
    }

    /**
     * check your subscription
     * 
     * @return array
     */
    public function account()    
    {   
        $this->debug("account: START");
        if(!$this->access_key) {
            $this->debug("account: access key not set, return");
            return array('flag'      => 2, 
                         'errortext' => 'access key not set');
        } 
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query(array('accesskey' => $this->access_key)),
                'timeout' => $this->timeout
            ),
        );
        $context  = stream_context_create($options);
        $this->debug("account: send query to ".self::$api_url."account");
        $result = @file_get_contents(self::$api_url."account", false, $context);	
        if(!$result) {
            $this->debug("account: connection error");
            return array('flag'      => 3, 
                         'errortext' => 'connection error');
        }
        $this->debug("account: END");
        return json_decode($result, true);        
    }    
        
    /**
     * Set the useragent string
     * @param string
     * @return bool
     */
    public function setUA($ua)
    {
        $this->debug('setting: set useragent string to ' . $ua);        
        $this->ua = $ua;
        return true;
    }
    
    /**
     * Set the IP address
     * @param string
     * @return bool
     */
    public function setIP($ip)
    {
        $this->debug('setting: set IP address to ' . $ip);        
        $this->ip = $ip;
        return true;
    }
    /**
     * Parse the useragent string and/or IP
     * @return array
     */
    public function parse()
    {
        $this->setDBdat();       
       
         
        // validate
        if (!$this->dbdat) {
            $this->debug('db: data file not found, download the data manually from http://data.udger.com/');
            return array('flag'      => 3, 
                         'errortext' => 'data file not found');
        }
        
        
        //ret values
        $ret = array( 'user_agent' =>
                array('ua_string'                     => '',
                      'ua_class'                      => '', 
                      'ua_class_code'                 => '', 
                      'ua'                            => '', 
                      'ua_version'                    => '', 
                      'ua_version_major'              => '', 
                      'ua_uptodate_current_version'   => '', 
                      'ua_family'                     => '', 
                      'ua_family_code'                => '', 
                      'ua_family_homepage'            => '', 
                      'ua_family_vendor'              => '', 
                      'ua_family_vendor_code'         => '', 
                      'ua_family_vendor_homepage'     => '', 
                      'ua_family_icon'                => '', 
                      'ua_family_icon_big'            => '',
                      'ua_family_info_url'            => '',
                      'ua_engine'                     => '', 
                      'os'                            => '', 
                      'os_code'                       => '', 
                      'os_homepage'                   => '', 
                      'os_icon'                       => '', 
                      'os_icon_big'                   => '',
                      'os_info_url'                   => '', 
                      'os_family'                     => '', 
                      'os_family_code'                => '', 
                      'os_family_vendor'              => '', 
                      'os_family_vendor_code'         => '', 
                      'os_family_vendor_homepage'     => '', 
                      'device_class'                  => '', 
                      'device_class_code'             => '', 
                      'device_class_icon'             => '', 
                      'device_class_icon_big'         => '', 
                      'device_class_info_url'         => '',
                      'crawler_last_seen'             => '',
                      'crawler_category'              => '', 
                      'crawler_category_code'         => '', 
                      'crawler_respect_robotstxt'     => ''
                       ),
              'ip_address' =>
                array('ip'                            => '', 
                      'ip_ver'                        => '', 
                      'ip_classification'             => '', 
                      'ip_classification_code'        => '',
                      'ip_hostname'                   => '', 
                      'ip_last_seen'                  => '', 
                      'ip_country'                    => '', 
                      'ip_country_code'               => '', 
                      'ip_city'                       => '', 
                      'crawler_name'                  => '',
                      'crawler_ver'                   => '',
                      'crawler_ver_major'             => '',
                      'crawler_family'                => '', 
                      'crawler_family_code'           => '', 
                      'crawler_family_homepage'       => '', 
                      'crawler_family_vendor'         => '', 
                      'crawler_family_vendor_code'    => '', 
                      'crawler_family_vendor_homepage'=> '',
                      'crawler_family_icon'           => '', 
                      'crawler_family_info_url'       => '', 
                      'crawler_last_seen'             => '',
                      'crawler_category'              => '', 
                      'crawler_category_code'         => '', 
                      'crawler_respect_robotstxt'     => ''
                       )
        );
        
        if($this->ua) {
            $this->debug("parse useragent string: START (useragent: ".$this->ua.")");
            $client_id      =0;
            $client_class_id=-1;
            $os_id          =0;
            $deviceclass_id =0;
            $ret['user_agent']['ua_string'] = $this->ua;
        
            // crawler            
            $q = $this->dbdat->query("SELECT name,ver,ver_major,last_seen,respect_robotstxt,family,family_code,family_homepage,family_icon,vendor,vendor_code,vendor_homepage,crawler_classification,crawler_classification_code
                                          FROM udger_crawler_list
                                          JOIN udger_crawler_class ON udger_crawler_class.id=udger_crawler_list.class_id
                                          WHERE ua_string='".$this->ua."'");

            if($r=$q->fetchArray(SQLITE3_ASSOC)) {
                $this->debug("parse useragent string: crawler found");
                
                $client_class_id=99;
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
                $ret['user_agent']['ua_family_info_url'] = "https://udger.com/resources/ua-list/bot-detail?bot=".$r['family'];            
                $ret['user_agent']['crawler_last_seen'] = $r['last_seen'];
                $ret['user_agent']['crawler_category'] = $r['crawler_classification'];
                $ret['user_agent']['crawler_category_code'] = $r['crawler_classification_code'];
                $ret['user_agent']['crawler_respect_robotstxt'] = $r['respect_robotstxt'];

            }
            else {
                // client
                $q = $this->dbdat->query("SELECT class_id,client_id,regstring,name,name_code,homepage,icon,icon_big,engine,vendor,vendor_code,vendor_homepage,uptodate_current_version,client_classification,client_classification_code
                                              FROM udger_client_regex
                                              JOIN udger_client_list ON udger_client_list.id=udger_client_regex.client_id
                                              JOIN udger_client_class ON udger_client_class.id=udger_client_list.class_id
                                              ORDER BY sequence ASC");
                while ($r=$q->fetchArray(SQLITE3_ASSOC)) {
                    if (@preg_match($r["regstring"],$this->ua,$result)) { 
                        $this->debug("parse useragent string: client found");
                        $client_id=$r['client_id'];
                        $client_class_id=$r['class_id'];
                        $ret['user_agent']['ua_class'] = $r['client_classification'];
                        $ret['user_agent']['ua_class_code'] = $r['client_classification_code'];
                        $ret['user_agent']['ua'] = $r['name']." ".$result[1];                    
                        $ret['user_agent']['ua_version'] = $result[1];
                        $ver_major = explode(".", $result[1]);
                        $ret['user_agent']['ua_version_major'] = $ver_major[0];
                        $ret['user_agent']['ua_uptodate_current_version'] = $r['uptodate_current_version'];
                        $ret['user_agent']['ua_family'] = $r['name'];
                        $ret['user_agent']['ua_family_code'] = $r['name_code'];
                        $ret['user_agent']['ua_family_homepage'] = $r['homepage'];
                        $ret['user_agent']['ua_family_vendor'] = $r['vendor'];
                        $ret['user_agent']['ua_family_vendor_code'] = $r['vendor_code'];
                        $ret['user_agent']['ua_family_vendor_homepage'] = $r['vendor_homepage'];
                        $ret['user_agent']['ua_family_icon'] = $r['icon'];
                        $ret['user_agent']['ua_family_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['ua_family_info_url'] = "https://udger.com/resources/ua-list/browser-detail?browser=".$r['name'];
                        $ret['user_agent']['ua_engine'] = $r['engine'];
                        break;
                    }  
                }
                // os
                $q = $this->dbdat->query("SELECT os_id,regstring,family,family_code,name,name_code,homepage,icon,icon_big,vendor,vendor_code,vendor_homepage
                                              FROM udger_os_regex
                                              JOIN udger_os_list ON udger_os_list.id=udger_os_regex.os_id
                                              ORDER BY sequence ASC");
                while ($r=$q->fetchArray(SQLITE3_ASSOC)) {
                    if (@preg_match($r["regstring"],$this->ua,$result)) {
                        $this->debug("parse useragent string: os found");
                        $os_id=$r['os_id'];
                        $ret['user_agent']['os'] = $r['name'];
                        $ret['user_agent']['os_code'] = $r['name_code'];
                        $ret['user_agent']['os_homepage'] = $r['homepage'];
                        $ret['user_agent']['os_icon'] = $r['icon'];
                        $ret['user_agent']['os_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['os_info_url'] = "https://udger.com/resources/ua-list/os-detail?os=".$r['name'];
                        $ret['user_agent']['os_family'] = $r['family'];
                        $ret['user_agent']['os_family_code'] = $r['family_code'];
                        $ret['user_agent']['os_family_vendor'] = $r['vendor'];
                        $ret['user_agent']['os_family_vendor_code'] = $r['vendor_code'];
                        $ret['user_agent']['os_family_vendor_homepage'] = $r['vendor_homepage'];
                        break;
                    }
                }
                // client_os_relation
                if($os_id==0 AND $client_id!=0){
                    $q = $this->dbdat->query("SELECT os_id,family,family_code,name,name_code,homepage,icon,icon_big,vendor,vendor_code,vendor_homepage
                                                  FROM udger_client_os_relation
                                                  JOIN udger_os_list ON udger_os_list.id=udger_client_os_relation.os_id
                                                  WHERE client_id=".$client_id." ");
                    if($r=$q->fetchArray(SQLITE3_ASSOC)) {
                        $this->debug("parse useragent string: client os relation found");
                        $os_id=$r['os_id'];
                        $ret['user_agent']['os'] = $r['name'];
                        $ret['user_agent']['os_code'] = $r['name_code'];
                        $ret['user_agent']['os_homepage'] = $r['homepage'];
                        $ret['user_agent']['os_icon'] = $r['icon'];
                        $ret['user_agent']['os_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['os_info_url'] = "https://udger.com/resources/ua-list/os-detail?os=".$r['name'];
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
                
                while ($r=$q->fetchArray(SQLITE3_ASSOC)) {
                    if (@preg_match($r["regstring"],$this->ua,$result)) {
                        $this->debug("parse useragent string: device found by regex");
                        $deviceclass_id=$r['deviceclass_id'];                    
                        $ret['user_agent']['device_class'] = $r['name'];
                        $ret['user_agent']['device_class_code'] = $r['name_code'];
                        $ret['user_agent']['device_class_icon'] = $r['icon'];
                        $ret['user_agent']['device_class_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['device_class_info_url'] = "https://udger.com/resources/ua-list/device-detail?device=".$r['name'];
                        break;
                    }
                }
                if($deviceclass_id==0 AND $client_class_id!=-1){
                    $q=$this->dbdat->query("SELECT deviceclass_id,name,name_code,icon,icon_big 
                                                  FROM udger_deviceclass_list
                                                  JOIN udger_client_class ON udger_client_class.deviceclass_id=udger_deviceclass_list.id
                                                  WHERE udger_client_class.id=".$client_class_id." ");
                    if($r=$q->fetchArray(SQLITE3_ASSOC)) {
                        $this->debug("parse useragent string: device found by deviceclass");
                        $deviceclass_id=$r['deviceclass_id'];                    
                        $ret['user_agent']['device_class'] = $r['name'];
                        $ret['user_agent']['device_class_code'] = $r['name_code'];
                        $ret['user_agent']['device_class_icon'] = $r['icon'];
                        $ret['user_agent']['device_class_icon_big'] = $r['icon_big'];
                        $ret['user_agent']['device_class_info_url'] = "https://udger.com/resources/ua-list/device-detail?device=".$r['name'];
                    }
                }
            }            
            
            $this->debug("parse useragent string: END, unset useragent string");
            $this->ua = '';
                
        }
        
        if($this->ip) {
            $this->debug("parse IP address: START (useragent: ".$this->ip.")");
            $ret['ip_address']['ip'] = $this->ip;
            $ipver=$this->validIP($this->ip);        
            if($ipver != 0) {
                $ret['ip_address']['ip_ver'] = $ipver;
                $q=$this->dbdat->query("SELECT ip_last_seen,ip_hostname,ip_country,ip_city,ip_country_code,ip_classification,ip_classification_code,
                                          name,ver,ver_major,last_seen,respect_robotstxt,family,family_code,family_homepage,family_icon,vendor,vendor_code,vendor_homepage,crawler_classification,crawler_classification_code
                                          FROM udger_ip_list
                                          JOIN udger_ip_class ON udger_ip_class.id=udger_ip_list.class_id
                                          LEFT JOIN udger_crawler_list ON udger_crawler_list.id=udger_ip_list.crawler_id
                                          LEFT JOIN udger_crawler_class ON udger_crawler_class.id=udger_crawler_list.class_id
                                          WHERE ip_md5='".md5($this->ip)."'");
                if ($r=$q->fetchArray(SQLITE3_ASSOC)) {                 
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
                    if($r['ip_classification_code'] == 'crawler') {
                        $ret['ip_address']['crawler_family_info_url'] = "https://udger.com/resources/ua-list/bot-detail?bot=".$r['family'];
                    }
                    $ret['ip_address']['crawler_last_seen'] = $r['last_seen'];
                    $ret['ip_address']['crawler_category'] = $r['crawler_classification'];
                    $ret['ip_address']['crawler_category_code'] = $r['crawler_classification_code'];
                    $ret['ip_address']['crawler_respect_robotstxt'] = $r['respect_robotstxt'];
                }
                else {            
                    $ret['ip_address']['ip_classification'] = 'Unrecognized';
                    $ret['ip_address']['ip_classification_code'] = 'unrecognized';               
                }
            }
            
            $this->debug("parse IP address: END, unset IP address");
            $this->ua = '';
        }
        return $ret;
    }    

    /**
     * Open DB file 
     */
    protected function setDBdat()
    {
        if (!$this->dbdat) {
           $this->debug("db: open file: ".$this->data_dir."/udgerdb_v3.dat");
           if(!empty($this->access_key) && $this->autoUpdate === true) {
                $this->checkDBdat();
           }elseif($this->autoUpdate === false){
               $this->debug('db: Auto update is disabled, use existing db'); 
           }
           if (file_exists($this->data_dir . '/udgerdb_v3.dat')) {
               $this->dbdat = new \SQLite3($this->data_dir . '/udgerdb_v3.dat');
           }
        }
    }
    
    /**
     * Check installed DB version
     * Trigger .dat file download if a newer version is available
     * 
     * @return boolean
     */
    protected function checkDBdat()
    {
        if (file_exists($this->data_dir . "/udgerdb_v3.dat")) {
            // check version
            $this->dbdat = new \SQLite3($this->data_dir . '/udgerdb_v3.dat');
            $q = @$this->dbdat->query("SELECT lastupdate,version FROM udger_db_info where key=1");
            if ($q) {
                $r = $q->fetchArray(SQLITE3_ASSOC);
                $this->dbdat->close();
                $time = time();
                $this->debug("db: lastupdate time:" . $r['lastupdate'] . ", curent time: " . $time . ", update interval: " . $this->updateInterval);

                if (($r['lastupdate'] + $this->updateInterval) < $time) {
                    $this->debug('Data is maybe outdated (local version is ' . $r['version'] . '), check new data from server');
                    return $this->downloadData($r['version']);
                } else {
                    $this->debug('db: data is current and will be used (local version is ' . $r['version'] . ')');
                    return true;
                }
            } else {
                $this->debug('db: data is corrupted, download data');
                return $this->downloadData();
            }
        } else {
            $this->debug('db: data dir is empty, download data');
            return $this->downloadData();
        }
    }

    /**
     * 
     * @return string
     */
    protected function getVersionFileUrl()
    {
        return sprintf("%s/%s/%s", self::$base_url, $this->access_key, self::$ver_filename);
    }
    
    /**
     * 
     * @return string
     */
    protected function getDataFileUrl()
    {
        return sprintf("%s/%s/%s", self::$base_url, $this->access_key, self::$data_filename);
    }
    
    /**
     * 
     * @return string
     */
    protected function getChecksumFileUrl()
    {
        return sprintf("%s/%s/%s", self::$base_url, $this->access_key, self::$md5_filename);
    }
    
    /**
     * 
     * @return string
     */
    protected function getDatFilePath()
    {
        return sprintf("%s/%s", $this->data_dir, 'udgerdb.dat');
    }

    /**
     * Download new data.
     * @param string $version local data version
     * @return boolean
     */
    protected function downloadData($version = "")
    {     
         $status = false;
        
        // support for fopen is needed
        if (!ini_get('allow_url_fopen')) {
            $this->debug('update: php fopen unavailable, download the data manually from http://data.udger.com/');
            return $status;
        }

        // Check the version on the server
        $ContentsRet = $this->getContents(self::$base_url.$this->access_key.self::$ver_filename, $this->timeout);
        if($ContentsRet[0] == 'HTTP/1.1 404 Not Found') {
            $this->debug('update: HTTP/1.1 404 Not Found - probably wrong access key');
            return $status;
        }
        else {
            $ver = $ContentsRet[1];
            if (preg_match('/^[0-9]{8}-[0-9]{2}$/', $ver)) { //Should be a date and version string like '20130529-01'
                if (isset($version)) {
                    if ($ver <= $version) { //Version on server is same as or older than what we already have
                        $this->debug('update: download skipped, existing data file is current (server version is '.$ver.', local version is '.$version.').');
                        return true;
                    }
                }
            }
            else {
                $this->debug('update: version string format mismatch.');
                $ver = '0';
                return false;
            }

            // Download the data file       
            $ContentsRet = $this->getContents(self::$base_url.$this->access_key.self::$data_filename, $this->timeout);
            $dat = $ContentsRet[1];
            if (!empty($dat)) {
                // Download the hash file
                $ContentsRet = $this->getContents(self::$base_url.$this->access_key.self::$md5_filename, $this->timeout);
                $md5hash = $ContentsRet[1];
                if (!empty($md5hash)) {
                    // Validate the hash, if okay store the new data file
                    if (md5($dat) == $md5hash) {
                        $written = @file_put_contents($this->data_dir . '/udgerdb_v3.dat', $dat, LOCK_EX);
                        if ($written === false) {
                            $this->debug('update: failed to write data file to ' . $this->data_dir . '/udgerdb_v3.dat');
                        } 
                        else {
                            $status = true;
                        }
                    } 
                    else {
                        $this->debug('update: data file hash mismatch.');
                    }
                } 
                else {
                    $this->debug('update: failed to fetch hash file.');
                }
            } 
            else {
                $this->debug('update: failed to fetch data file.');
            }
            if (file_exists($this->data_dir."/udgerdb_v3.dat")) {
                $this->dbdat = new \SQLite3($this->data_dir . '/udgerdb_v3.dat');
                @$this->dbdat->query("UPDATE udger_db_info SET lastupdate=".time()." WHERE key=1");
                $this->dbdat->close();
            }
            return $status;
        }

        if (file_exists($this->data_dir . "/udgerdb.dat")) {
            $this->dbdat = new \SQLite3($this->getDatFilePath());
            @$this->dbdat->query("UPDATE _info_ SET lastupdate=" . time() . " WHERE key=1");
            $this->dbdat->close();
        }
        return true;
    }

    /**
     * Get the contents of a URL with a defined timeout.
     * @param string $url
     * @param int $timeout
     * @return array
     */
    protected function getContents($url, $timeout = 120)
    {
        $options = array(
            'timeout' => $timeout,
            'headers' => array('Accept-Encoding' => 'gzip'),
            'decode_content' => true
        );
        if (is_resource($fp)) {
            $data = stream_get_contents($fp);
            $res = stream_get_meta_data($fp);
            if (array_key_exists('wrapper_data', $res)) {
                foreach ($res['wrapper_data'] as $d) {
                    if ($d == 'Content-Encoding: gzip') { //Data was compressed
                        $data = gzinflate(substr($data, 10, -8)); //Uncompress data
                        $this->debug('update: successfully uncompressed data');
                        break;
                    }
                }
            }
            fclose($fp);
            if (empty($data)) {
                if ($this->debug) {
                    if ($res['timed_out']) {
                        $this->debug('update: fetching URL failed due to timeout: ' . $url);
                    } 
                    else {
                        $this->debug('update: fetching URL failed: ' . $url);
                    }
                }
                $data = '';
            } else {
                $this->debug(
                    'update: fetching URL with fopen succeeded: ' . $url . '. ' . strlen($data) . ' bytes in ' . (microtime(
                            true
                        ) - $starttime) . ' sec.'
                );
            }
        } 
        else {
            $this->debug('update: opening URL failed: '. $url.' - Error: '.@$http_response_header[0]);
        }
        return array(@$http_response_header[0], $data);
    }
    
    /**
     * Update agents database
     * @return boolean
     */
    public function updateData()
    {
        return $this->checkDBdat();
    }
    /**
     * Set the data directory.
     * @param string
     * @return bool
     */
    public function setDataDir($data_dir)
    {
        $this->debug('setting: set cache dir to ' . $data_dir);
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
     * Set the account access key.
     * @param string
     * @return bool
     */
    public function setAccessKey($access_key)
    {
        $this->debug('setting: set accesskey to ' . $access_key);        
        $this->access_key = $access_key;
        return true;
    }
    
    /**
     * Set auto update: true to activate updates
     * 
     * @param string
     * @return bool
     */
    public function setAutoUpdate($value)
    {
        if (is_bool($value) === true){
            $this->autoUpdate = $value;
            return true;
        }
        return false;
    }
    
    /**
     * Output a time-stamped debug message if debugging is enabled
     * @param string $msg
     */
    protected function debug($msg)
    {
        if ($this->debug) {
            $htmlNL = '';
            if(isset($_SERVER['SERVER_SOFTWARE']))
               $htmlNL = '<br />';
            $micro = date('Y-m-d\TH:i:s') . substr(microtime(), 1, 9);
            $d = new \DateTime($micro);
            echo date_format($d, 'Y-m-d H:i:s.u') . "\t$msg $htmlNL\n";
            flush();
        }
    }    
    /**
     * Validate IP addresss
     * @param string $ip
     * @return integer
     */
    protected function validIP($ip) {
    if (substr_count($ip,":") < 1) {
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
           $this->debug("parse IP address: IP ver 4)");
           return 4;
        }
        else {
            $this->debug("parse IP address: IP not valid)");
            return 0;
        }
    }
    else {
         if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
           $this->debug("parse IP address: IP ver 6)");
           return 6;
        }
        else {
            $this->debug("parse IP address: IP not valid)");
            return 0;
        }
    }
}
   
}
