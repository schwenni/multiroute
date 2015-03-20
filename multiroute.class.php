<?php

/**
 * Multiroute route optimizing service implementation Mehr Infos unter http://www.multiroute.de
 *
 * @author Michael Schweneker <schweneker@gmail.com>
 * @version 0.1
 * 
 *  */
/* This script was written by Michael Schweneker, 2014-2015.
 *
 * This script is free software: you can redistribute it and/or modify
 * it under the terms of the Lesser GNU General Public License (LGPL) as
 * published by the Free Software Foundation, either version 3 of the
 * License or (at your option) any later version.
 *
 * This script is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should received a copy of the GNU General Public License and the
 * Lesser GNU General Public License with this script. If not, see
 * <http://www.gnu.org/licenses/>. */

class multiroute {

    public $waypoints = "";
    public $json_response = "";
    public $status = "";
    public $return_code = 0;
    public $error_description = "";
    public $error_text = "";
    public $credit_expiration_date = "";
    public $tid = "";

    // TODO: User , api und picture path als public umschreiben un vorbelegen
    const MULTIROUTE_USER = "your_account@yourcompany.com";
    const MULTIROUTE_APIKEY = "sample_key_use_your_own";
    const MULTIROUTE_DOMAIN = "https://webservice.tourenplaner.biz";
    const MULTIROUTE_PICTURE_PATH = '/var/www/sample_webspace/public_html/temp/';

    public $postdata = "";

    /**
     * 
     * @param array $points
     */
    public function clear_waypoints() {
        $this->waypoints = array();
    }

    /**
     * 
     * @return array 
     */
    public function get_waypoints() {
        return $this->waypoints;
    }

    /**
     * 
     * @param type $waypoints
     */
    public function add_waypoints($waypoints) {
        $this->waypoints = $waypoints;
    }

    /**
     * delete waypoint 
     * @param type $uid  
     * @return boolean
     */
    public function delete_waypoint($uid = "") {
        $i = 0;
        foreach ($this->waypoints as $waypoint) {
            if ($waypoint["uid"] == $uid) {
                unset($this->waypoints[$i]);
                return true;
            }
            $i++;
        }
        return false;
    }

    /**
     * https://webservice.tourenplaner.biz/startjob
     * 
     * call webservice curl/json 
     * 
     * @param type $optimize  time oder distance
     * @param type $roundtrip true or false
     * @param type $tollfree true oder false
     * @param type $avoid_highway true oder false
     * @param string $uid  unique ID from your CRM
     * @param type $start start optimize in background  true oder false
     * @param type $show_only  true oder false
     * @param type $optimize_endpoint endpoint not fix  true oder false
     * @param type $time_window optize with time window true or false
     * @param type $time_window_calculation_time max time in seconds to use for time window calculating default=60 Sec.
     * @return string
     */
    public function startjob($optimize = "time", $roundtrip = false, $tollfree = false, $avoid_highway = false, $uid = "", $start = false, $show_only = false, $optimize_endpoint = false, $time_window = false, $time_window_calculation_time = 60) {

        $url = self::MULTIROUTE_DOMAIN . "/startjob";
        $status_code = "";

        $this->postdata = new stdClass();

        $this->postdata->tour = array(
            "optimize" => $optimize,
            "roundtrip" => $roundtrip,
            "tollfree" => $tollfree,
            "avoid_highway" => $avoid_highway,
            "uid" => $uid,
            "start" => $start,
            "show_only" => $show_only,
            "optimize_endpoint" => $optimize_endpoint,
            "time_window" => $time_window,
            "time_window_calculation_time" => $time_window_calculation_time
        );

        $this->postdata->waypoints = $this->get_waypoints();
        $json = json_encode($this->postdata);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::MULTIROUTE_DOMAIN . '/startjob');
        curl_setopt($ch, CURLOPT_USERPWD, self::MULTIROUTE_USER . ':' . self::MULTIROUTE_APIKEY);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->json_response = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->status = "error";
            $this->return_code = 5;
            $this->error_text = curl_error($ch);
            return false;
        }
        curl_close($ch);

        $response = json_decode($this->json_response, true);
        $this->status = "";
        $this->return_code = $response["return_code"];
        $this->error_description = $response["error_description"];
        $this->tid = $response["tid"];
        
        if ($this->return_code > 0) {
            return false;
        }
        return true;
    }

    /**
     * 
     * @return boolean
     */
    public function getstatus($tid = "") {

        $url = self::MULTIROUTE_DOMAIN . "/getstatus?tid=" . $tid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, self::MULTIROUTE_USER . ':' . self::MULTIROUTE_APIKEY);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $this->json_response = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->status = "error";
            $this->return_code = 6;
            $this->error_text = curl_error($ch);
            return false;
        }
        curl_close($ch);

        $response = json_decode($this->json_response, true);
        $this->status = "";
        $this->return_code = $response["return_code"];
        $this->error_description = $response["error_description"];

        if ($this->return_code > 0) {
            return false;
        }


        return true;
    }

    /**
     * 
     * @return boolean
     */
    public function getmap($tid = "", $width = 800, $height = 600, $filename = "replaceme.jpg", $path = self::MULTIROUTE_PICTURE_PATH) {

        $url = self::MULTIROUTE_DOMAIN . "/get_static_map?tid=" . $tid;
        $ch = curl_init();

        $file = $path . $filename;
        $fp = fopen($file, 'wb');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, self::MULTIROUTE_USER . ':' . self::MULTIROUTE_APIKEY);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        echo( curl_exec($ch));
        curl_close($ch);
        return "1";
    }

    /**
     * 
     * @return boolean
     */
    public function getresult($tid = "") {

        $url = self::MULTIROUTE_DOMAIN . "/getresult.json" . "?tid=" . $tid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, self::MULTIROUTE_USER . ':' . self::MULTIROUTE_APIKEY);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->json_response = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->return_code = 6;
            $this->status = "error";
            $this->error_text = curl_error($ch);
            return false;
        }
        curl_close($ch);

        $response = json_decode($this->json_response, true);
        
       
        $this->return_code = 0;
        $this->status = $response["tour"]["status"];
        $this->error_text = $response["tour"]["error_text"];

        if ($this->status == "error") {
            return false;
        }

        return true;
    }

    /**
     * Returns given Json Code only fro debugging... 
     * @param type $optimize
     * @param type $roundtrip
     * @param type $tollfree
     * @param type $avoid_highway
     * @param type $uid
     * @param type $start
     * @param type $show_only
     * @param type $optimize_endpoint
     * @param type $time_window
     * @param type $time_window_calculation_time
     * @return type
     */
    public function show_json($optimize, $roundtrip, $tollfree, $avoid_highway, $uid, $start, $show_only, $optimize_endpoint, $time_window, $time_window_calculation_time) {
        $this->postdata = new stdClass();
        
        $this->postdata->tour = array(
            "optimize" => $optimize,
            "roundtrip" => $roundtrip,
            "tollfree" => $tollfree,
            "avoid_highway" => $avoid_highway,
            "uid" => $uid,
            "start" => $start,
            "show_only" => $show_only,
            "optimize_endpoint" => $optimize_endpoint,
            "time_window" => $time_window,
            "time_window_calculation_time" => $time_window_calculation_time
        );

        $this->postdata->waypoints = $this->get_waypoints();
        $json = json_encode($this->postdata);
        return $json;
    }

    /**
     * not used now
     * @return boolean
     */
    public function optimize($tid) {

        $url = self::MULTIROUTE_DOMAIN . "/optimize?time_window=true&tid=" . $tid;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, self::MULTIROUTE_USER . ':' . self::MULTIROUTE_APIKEY);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        echo("Optimiert?=>" . curl_exec($ch));
        curl_close($ch);
        return "1";
    }

}

?>
