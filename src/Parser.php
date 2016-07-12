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
    protected static $data_filename = 'udgerdb.dat';  
    
    /**
     * md5 hash file name
     * @type string
     */
    protected static $md5_filename = 'udgerdb_dat.md5';  
    
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
     * db link.
     * @type object
     */
    protected $dbdat = null;

    /**
     * Array of parsed UAS data.
     * @type bool
     */
    protected $parse_fragments = false;
    
    private $logger;

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
        $this->logger->debug("account: start");
        
        if(empty($this->access_key)) {
            throw new \Exception("access key not set");
        }
        
        $accountUrl = sprintf("%s/%s", self::$api_url, "account");
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
        if(isset($data['flag']) && $data['flag'] > 0){
            throw new \Exception($data['errortext']);
        }
        
        return $data;        
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
     * check if useragent string and/or IP address is bot
     * @param string $useragent user agent string
     * @param string $ip IP address v4 or v6
     * @return array
     */
    public function isBot($useragent = "", $ip = "")
    {         
        $this->setDBdat();
        
        $this->logger->debug("isBot: start");
        // validate
        if (empty($this->dbdat)) {
            $this->logger->debug('Data file not found, download the data manually from http://data.udger.com/');
            throw new \Exception('data file not found');
        }
        
        if (empty($useragent) && empty($ip)) {
            throw new \Exception('missing mandatory parameters');
        }
        
        if (!empty($ip) &&  (false === $this->validIP($ip))) {
            throw new \Exception('ip address is not valid');
        }
        
        // check
        $botInfo   = false;
        $botInfoUA = false;
        $botInfoIP = false;
        $harmony   = false;
        
        $botName   = '';
        $botURL    = '';
        
        if ($useragent) {
            $this->logger->debug("isBot: test useragent '".$useragent."'");
            $q = $this->dbdat->query("SELECT name,family FROM c_robots where md5='".md5($useragent)."'");
            if($r=$q->fetchArray(SQLITE3_ASSOC)) {
                $botInfo   = true;
                $botInfoUA = true;
                $botName   = $r["name"];
                $botfamily = $r["family"];
                $botURL    = self::$resources_url."/bot-detail?bot=".$r['family'];
            }
       }
       if ($ip) {
           $this->logger->debug("isBot: test IP address '".$ip."'");
           $q=$this->dbdat->query("SELECT name,family from c_robots AS C JOIN bot_ip as B ON C.id=B.robot and B.md5='".md5($ip)."' ");
           if($r=$q->fetchArray(SQLITE3_ASSOC)) {
               $botInfo   = true;
               $botInfoIP = true;
               if($botfamily == $r["family"]) {
                   $harmony = true;
               }
               $botName   = $r["name"];
               $botURL    = self::$resources_url."/bot-detail?bot=".$r['family'];
           }
       }
       
       $this->logger->debug("isBot: completed");
       return array('flag'          => 0, 
                    'is_bot'        => $botInfo, 
                    'bot_by_ua'     => $botInfoUA, 
                    'bot_by_ip'     => $botInfoIP, 
                    'harmony_ua_ip' => $harmony, 
                    'bot_name'      => $botName, 
                    'bot_udger_url' => $botURL);

    }
    
    /**
     * Parse the useragent string
     * @param string $useragent user agent string
     * @return array
     */
    public function parse($useragent)
    {
        $this->setDBdat();
        
        $this->logger->debug("parse: start (useragent:".$useragent.")");
         
        // validate
        if (empty($this->dbdat)) {
            $this->logger->debug('Data file not found, download the data manually from http://data.udger.com/');
            throw new \Exception("data file not found");
        }
        
        if (empty($useragent)) {
            $this->logger->debug('parse: Missing mandatory parameter');
            throw new \Exception("missing mandatory parameter");
        }
        
        //def values
        $info=array();
        $info["type"]             = "unknown";
        $info["ua_name"]          = "unknown";
        $info["ua_ver"]           = "";
        $info["ua_family"]        = "unknown";
        $info["ua_url"]           = "unknown";
        $info["ua_company"]       = "unknown";
        $info["ua_company_url"]   = "unknown";
        $info["ua_icon"]          = "unknown.png";
        $info["ua_engine"]        = "n/a";
        $info["ua_udger_url"]     = "";
        $info["os_name"]          = "unknown";
        $info["os_family"]        = "unknown";
        $info["os_url"]           = "unknown";
        $info["os_company"]       = "unknown";
        $info["os_company_url"]   = "unknown";
        $info["os_icon"]          = "unknown.png";
        $info["os_udger_url"]     = "";
        $info["device_name"]      = "Personal computer";
        $info["device_icon"]      = "desktop.png";
        $info["device_udger_url"] = self::$resources_url."/device-detail?device=Personal%20computer";
        
        $fragments=array();
        
        $uptodate=array();
        $uptodate["controlled"]   = false;
        $uptodate["is"]           = false;
        $uptodate["ver"]          = ""; 
        $uptodate["url"]          = ""; 
        
        $browser_id = null;
        
        // parse
        $this->logger->debug("parse: bot");
        $q = $this->dbdat->query("SELECT name,family,url,company,url_company,icon FROM c_robots where md5='".md5($useragent)."'");
        if($r=$q->fetchArray(SQLITE3_ASSOC)) {
                $this->logger->debug("parse: bot found");
            	
		$info["type"]             = "Robot";
                $info["ua_name"]          = $r["name"];
                //$info["ua_ver"]           = "";
		$info["ua_family"]        = $r["family"];
		$info["ua_url"]           = $r["url"];
		$info["ua_company"]       = $r["company"];
		$info["ua_company_url"]   = $r["url_company"];
		$info["ua_icon"]          = $r["icon"];
                $info["ua_udger_url"]     = self::$resources_url."/bot-detail?bot=".$r["family"];
                $info["device_name"]      = "Other";
                $info["device_icon"]      = "other.png";
                $info["device_udger_url"] = self::$resources_url."/device-detail?device=Other";
             
                return array('flag'      => 1, 
                             'info'      => $info,
                             'fragments' => $fragments,
                             'uptodate'  => $uptodate);
                
        }
        
        $this->logger->debug("parse: browser");
        foreach ($this->browserReg as $r) {
            if (preg_match($r["regstring"],$useragent,$result)) {
                $browser_id = $r["browser"];
                $this->logger->debug("parse: browser found (id: ".$browser_id.")");
                
                $q = $this->dbdat->query("SELECT type,name,engine,url,company,company_url,icon FROM c_browser WHERE id=".$browser_id." ");
                $r=$q->fetchArray(SQLITE3_ASSOC);
                $qType = $this->dbdat->query("SELECT name FROM c_browser_type WHERE type=".$r["type"]." ");
                $rType=$qType->fetchArray(SQLITE3_ASSOC);
               
		$ua_ver = isset($result[1]) ? $result[1] : "";
		$ua_name = $r["name"];
		if (!empty($ua_ver)){
			$ua_name .= " " . $ua_ver;		
		}

                $info["type"]             = $rType["name"];
                $info["ua_name"]          = $ua_name;
                $info["ua_ver"]           = $ua_ver;
		$info["ua_family"]        = $r["name"];
		$info["ua_url"]           = $r["url"];
		$info["ua_company"]       = $r["company"];
		$info["ua_company_url"]   = $r["company_url"];
		$info["ua_icon"]          = $r["icon"];
                $info["ua_engine"]        = $r["engine"];
                $info["ua_udger_url"]     = self::$resources_url."/browser-detail?browser=".$r["name"];
                
                break;
            }
        }   
        
        $this->logger->debug("parse: os");
        $os_id = 0;
        if(!is_null($browser_id)) {
            $q = $this->dbdat->query("SELECT os FROM c_browser_os where browser=".$browser_id."");
            if($r=$q->fetchArray(SQLITE3_ASSOC)) {
                $os_id = $r["os"];
                $this->logger->debug("parse: os found (id: ".$os_id.")");
            }
        }
        if(!$os_id) {
            foreach($this->osReg as $r) {
                if (preg_match($r["regstring"],$useragent,$result)) {
                    $os_id = $r["os"];
                    $this->logger->debug("parse: os found (id: ".$os_id.")");
                    break;
                }
            }   
        }
        if($os_id) {
            $q = $this->dbdat->query("SELECT name, family, url, company, company_url, icon FROM c_os where id=".$os_id."");
            $r=$q->fetchArray(SQLITE3_ASSOC);
            $info["os_name"]          = $r["name"];
            $info["os_family"]        = $r["family"];
            $info["os_url"]           = $r["url"];
            $info["os_company"]       = $r["company"];
            $info["os_company_url"]   = $r["company_url"];
            $info["os_icon"]          = $r["icon"];
            $info["os_udger_url"]     = self::$resources_url."/os-detail?os=".$r["name"];
        }
        
        
        $this->logger->debug("parse: device");
        $device_id = 0;
        foreach ($this->deviceReg as $r) {
            if (preg_match($r["regstring"],$useragent,$result)) {
                $device_id = $r["device"];
                $this->logger->debug("parse: device found (id: ".$device_id.")");
                break;
            }
        }   
        if($device_id) {
            $q = $this->dbdat->query("SELECT name,icon FROM c_device WHERE id=".$device_id." ");
            $r=$q->fetchArray(SQLITE3_ASSOC);
            
            $info["device_name"]      = $r["name"];
            $info["device_icon"]      = $r["icon"];
            $info["device_udger_url"] = self::$resources_url."/device-detail?device=".$r["name"];
            
        }
        else if($info["type"]=="Mobile Browser")
        {
            $this->logger->debug("parse: device set by ua type - Mobile Browser");
            $info["device_name"]      = "Smartphone";
            $info["device_icon"]      = "phone.png";
            $info["device_udger_url"] = self::$resources_url."/device-detail?device=Smartphone";
        }
        else if($info["type"]=="Library" || $info["type"]=="Validator" || $info["type"]=="Other" || $info["type"]=="Useragent Anonymizer")
        {
            $this->logger->debug("parse: device set by ua type");
            $info["device_name"]      = "Other";
            $info["device_icon"]      = "other.png";
            $info["device_udger_url"] = self::$resources_url."/device-detail?device=Other";
        }
        
        $this->logger->debug("parse: uptodate");
        if($browser_id) {
            $ver_major = explode(".", $info["ua_ver"]);
            $q = $this->dbdat->query("SELECT ver, url FROM c_browser_uptodate WHERE browser_id='".$browser_id."' AND (os_independent = 1 OR os_family = '".$info["os_family"]."')");
            if($r=$q->fetchArray(SQLITE3_ASSOC)) {
                $this->logger->debug("parse: uptodate controlled");
                $uptodate["controlled"]   = true;
                if($ver_major[0] >= $r['ver']) {
                    $uptodate["is"]       = true;
                }
                $uptodate["ver"]          = $r['ver']; 
                $uptodate["url"]          = $r['url'];             
            } 
        }
        
        if($this->parse_fragments) {
            $this->logger->debug("parse: fragments");
            $fragments = $this->parseFragments($useragent);            
        }
        else {
            $this->logger->debug("parse: fragments skiped");
        }
        
        $this->logger->debug("parse: completed");
        return array('flag'      => 1, 
                     'info'      => $info,
                     'fragments' => $fragments,
                     'uptodate'  => $uptodate);
    }
    
    /**
     * Parse fragments from useragent string
     * @param string $useragent user agent string
     * @return array
    */
    protected function parseFragments($useragent)
    {
        $fr = $this->getFragments($useragent);
        $ret = array('detail' => '');
        $this->logger->debug("parse: fragments parse");
        for ($fi=0; $fi<count($fr); $fi++) {
                $f=$fr[$fi];
		
                if ($f) { 
                        $ok=false;
                        foreach ($this->fragmentReg as $r) {
                                $pop="";
                                if (@preg_match($r["regstring"],$f,$vys)) {
                                                $pop=$r["note"];
                                                $i=1;
                                                if(count($vys) > 1) {
                                                    while ($vys[$i]) {
                                                            $pop=mb_ereg_replace("##".$i."##",$vys[$i],$pop);
                                                            $i+=1;
                                                            if($i + 1 > count($vys))
                                                                break;
                                                    }
                                                }
                                        
                                        if ($r["regstring2"]) {
                                                $fnext=@$fr[$fi+1];
                                                if (@preg_match($r["regstring2"],$fnext,$vys2)) {
                                                        $i=1;
                                                        if(count($vys2) > 1) {
                                                            while ($vys2[$i]) {
                                                                    $pop=mb_ereg_replace("##2".$i."##",$vys2[$i],$pop);
                                                                    $i+=1;
                                                                    if($i + 1 > count($vys2))
                                                                        break;
                                                            }
                                                        }
                                                        if ($r["regstring3"]) {
                                                                $fnext2=@$fr[$fi+2];
                                                                if (@preg_match($r["regstring3"],$fnext2,$vys3)) {
                                                                        $i=1;
                                                                        if(count($vys3) > 1) {
                                                                            while ($vys3[$i]) {
                                                                                    $pop=mb_ereg_replace("##3".$i."##",$vys3[$i],$pop);
                                                                                    $i+=1;
                                                                                    if($i + 1 > count($vys3))
                                                                                        break;
                                                                            }
                                                                        }

                                                                        if ($r["regstring4"]) {
                                                                                $fnext3=@$fr[$fi+3];
                                                                                if (@preg_match($r["regstring4"],$fnext3,$vys4)) {
                                                                                        $i=1;
                                                                                        if(count($vys4) > 1) {
                                                                                            while ($vys4[$i]) {
                                                                                                    $pop=mb_ereg_replace("##4".$i."##",$vys4[$i],$pop);
                                                                                                    $i+=1;
                                                                                                    if($i + 1 > count($vys4))
                                                                                                        break;
                                                                                            }
                                                                                        }
                                                                                        $fi=$fi+3;
                                                                                        $ret["detail"][$f." ".$fnext." ".$fnext2." ".$fnext3]=$pop;
                                                                                        $ok=true;
                                                                                }
                                                                        } 
                                                                        else {
                                                                            $fi=$fi+2;
                                                                            $ok=true;
                                                                            
                                                                                $ret["detail"][$f." ".$fnext." ".$fnext2]=$pop;
                                                                        }
                                                                }
                                                        } 
                                                        else {
                                                            $fi=$fi+1;
                                                            $ok=true;
                                                             
                                                                $ret["detail"][$f." ".$fnext]=$pop;
                                                        }
                                                }
                                        } 
                                        else {
                                            $ok=true;
                                             
                                                $ret["detail"][$f]=$pop;
                                        }
                                }
                        }
                        if ($ok === true) {
                                continue;
                        }
                }
                
        }   
        return $ret["detail"];
        
    }

    /**
     * Get fragments from useragent string
     * @param string $useragent user agent string
     * @return array
    */
    protected function getFragments($useragent)
    {
        $this->logger->debug("parse: get fragments");
        
        $section=array(1 => "", 2 => "", 3 => "");
        $bra=0;
        $sec=1;
        for ($i=0;$i<mb_strlen($useragent);$i++) {
            $tc=mb_substr($useragent,$i,1);
            switch ($sec) {
                case 1:
                    if ($tc=="(") {
                        $bra=1;
                        $sec=2;
                    } 
                    else {
                        $section[$sec].=$tc;
                    }
                break;
                case 2:
                    switch ($tc) {
                        case "(":
                            $bra+=1;
                            $section[$sec].=$tc;
                        break;
                        case ")":
                            $bra-=1;
                            if ($bra==0) {
                                $sec=3;
                            } 
                            else {
                                $section[$sec].=$tc;
                            }
                        break;
                        default:
                            $section[$sec].=$tc;
                        break;
                    }
                break;
                case 3:
                switch ($tc) {
                    case "(":
                        $bra+=1;
                    break;
                    case ")":
                        $bra-=1;
                    break;
                }
                if ($tc==" " and $bra==0) 
                    $tc="|";
                $section[$sec].=$tc;
                break;
            }
        }
        $fr=array();
        if ($section[1]) {
            $pom=explode(" ",$section[1]);
            foreach ($pom as $p) {
                if ($p) $fr[]=ltrim(rtrim($p));
            }
        }
        if ($section[2]) {
            $pom=explode(";",$section[2]);
            foreach ($pom as $p) {
                if ($p) $fr[]=ltrim(rtrim($p));
            }
        }
        if ($section[3]) {
            $pom=explode("|",$section[3]);
            foreach ($pom as $p) {
                    if ($p) $fr[]=ltrim(rtrim($p));
            }
        }
        
        return $fr;        
    }

    protected function fetchAll($query, $mode = SQLITE3_ASSOC) {
        $results = array();
        $q = $this->dbdat->query($query);
        while ($r=$q->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $r;
        }
        return $results;
    }

    /**
     * Open DB file 
     */
    protected function setDBdat()
    {
        if (!$this->dbdat) {
           $this->logger->debug("Open DB file: ".$this->data_dir."/udgerdb.dat");
           if(!empty($this->access_key) && $this->autoUpdate === true) {
                $this->checkDBdat();
           }elseif($this->autoUpdate === false){
               $this->logger->debug('Auto update is disabled, use existing db'); 
           }
           if (file_exists($this->getDatFilePath())) {
               $this->dbdat = new \SQLite3($this->getDatFilePath());

               $this->browserReg = $this->fetchAll("SELECT browser, regstring FROM reg_browser ORDER by sequence ASC");
               $this->osReg = $this->fetchAll("SELECT os, regstring FROM reg_os ORDER by sequence ASC");
               $this->deviceReg = $this->fetchAll("SELECT device, regstring FROM reg_device ORDER by sequence ASC");
               $this->fragmentReg = $this->fetchAll("SELECT note,regstring,regstring2,regstring3,regstring4 FROM reg_fragment ORDER BY sequence ASC");

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
        if (false === file_exists($this->data_dir . "/udgerdb.dat")) {
            $this->logger->debug('Data dir is empty, download data');
            return $this->downloadData();
        }
        
        // check version
        $this->dbdat = new \SQLite3($this->getDatFilePath());
        $q = @$this->dbdat->query("SELECT lastupdate,version FROM _info_ where key=1");
        if ($q) {
            $r = $q->fetchArray(SQLITE3_ASSOC);
            $this->dbdat->close();
            $time = time();
            $this->logger->debug("lastupdate time:" . $r['lastupdate'] . ", curent time: " . $time . ", update interval: " . $this->updateInterval);

            if (($r['lastupdate'] + $this->updateInterval) < $time) {
                $this->logger->debug('Data is maybe outdated (local version is ' . $r['version'] . '), check new data from server');
                return $this->downloadData($r['version']);
            } else {
                $this->logger->debug('Data is current and will be used (local version is ' . $r['version'] . ')');
                return true;
            }
        } else {
            $this->logger->debug('Data is corrupted, download data');
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
        if (!file_exists($this->data_dir)) {
            throw new \Exception('Data dir not found');
        }

        // Check the version on the server
        list($statusCode, $ver) = $this->getContents($this->getVersionFileUrl(), $this->timeout);

        if ($statusCode == 404) {
            throw new \Exception('Probably wrong access key');
        }
        
        if (preg_match('/^[0-9]{8}-[0-9]{2}$/', $ver)) { // Should be a date and version string like '20130529-01'
            if (isset($version)) {
                if ($ver <= $version) { // Version on server is same as or older than what we already have
                    $this->logger->debug('Download skipped, existing data file is current (server version is ' . $ver . ', local version is ' . $version . ').');
                    return true;
                }
            }
        } else {
            throw new \Exception('Probably wrong access key');
        }

        // Download the data file       
        list($statusCode, $dat) = $this->getContents($this->getDataFileUrl(), $this->timeout);

        if (empty($dat)) {
            throw new \Exception('Failed to fetch data file');
        }

        // Download the hash file
        list($statusCode, $md5hash) = $this->getContents($this->getChecksumFileUrl(), $this->timeout);

        if (empty($md5hash)) {
            throw new \Exception('Failed to fetch hash file');
        }

        // Validate the hash, if okay store the new data file
        if (md5($dat) != trim($md5hash)) {
            throw new \Exception('Data file hash mismatch');
        }

        if (false === @file_put_contents($this->getDatFilePath(), $dat, LOCK_EX)) {
            throw new \Exception('Failed to write data file to ' . $this->getDatFilePath());
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

        $client = new \GuzzleHttp\Client();
        $result = $client->get($url, $options);
        $return = array($result->getStatusCode(), $result->getBody()->getContents());
        
        return $return;
    }
    
    /**
     * Set the data directory.
     * @param string
     * @return bool
     */
    public function setDataDir($data_dir)
    {
        $this->logger->debug('Setting cache dir to ' . $data_dir);
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
        $this->logger->debug('Setting AccessKey to ' . $access_key);        
        $this->access_key = $access_key;
        return true;
    }
    
    /**
     * Enable/disable fragment parsing.
     * @param string
     * @return bool
     */
    public function setParseFragments($parse_fragments)
    {
        $this->logger->debug('Setting Parse Fragments to ' . $parse_fragments);        
        $this->parse_fragments = $parse_fragments;
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
     * Validate IP addresss
     * @param string $ip
     * @return bool
     */
    protected function validIP($ip) {
    if (substr_count($ip,":") < 1) {
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
           return true;
        }
        else {
          return false;
        }
    }
    else {
         if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
           return true;
        }
        else {
          return false;
        }
    }
}
   
}
