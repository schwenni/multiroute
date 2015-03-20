<?php

/**
 * @author Michael Schweneker 
 * @copyright C+K Service GmbH
 * @version 1.0
 */

$optimize = "distance";
$roundtrip = false;
$tollfree = false;
$avoid_highway = false;
$uid = "Sample Tour name";
$start = true;
$show_only = false;
$optimize_endpoint = false;
$time_window = false;
$time_window_calculation_time = 0;
$waypoints = array();

$waypoints[] = array(
    "uid" => "99",
    "address" => array(
        "street" => 'Lichtenrader Damm 170',
        "postcode" => '12305',
        "locality" => 'Berlin',
        "country" => 'DE'),
    "time" => array("departure" => '2015-03-19T07:00:00+01:00')
);

$waypoints[] = array(
    "uid" => "100",
    "address" => array(
        "street" => 'Kaiserstrasse 24',
        "postcode" => '12209',
        "locality" => 'Berlin',
        "country" => 'DE'),
    "time" => array(
        "duration_of_stay" => 20,
        "time_frame_isfixed" => true,
        "timeframe_start" => '2015-03-19T08:00:00+01:00'
    )
);
$waypoints[] = array(
    "uid" => "101",
    "address" => array(
        "street" => 'Lorenzstrasse 64',
        "postcode" => '12209',
        "locality" => 'Berlin',
        "country" => 'DE'),
    "time" => array(
        "duration_of_stay" => 20
    )
);
$waypoints[] = array(
    "uid" => "102",
    "address" => array(
        "street" => 'Geitnerweg 7 C',
        "postcode" => '12209',
        "locality" => 'Berlin',
        "country" => 'DE'),
    "time" => array(
        "duration_of_stay" => 20
       
    )
);
$i = 0;

$multiroute = new multiroute();
$multiroute->clear_waypoints();
$multiroute->add_waypoints($waypoints);

if (isset($_REQUEST["modus"])) {
    $modus = $_REQUEST["modus"];
} else {
    $modus = "";
}
if ($modus == 'calc' || $modus == "") {

    echo($modus);
    if ($multiroute->startjob($optimize, $roundtrip, $tollfree, $avoid_highway, $uid, $start, $show_only, $optimize_endpoint, $time_window, $time_window_calculation_time)) {
        $tid = $multiroute->tid;
        echo ("startjob:" . $tid . " -> show result with url + ?modus=show" );
        $_SESSION["tid"] = $tid;
    } else {
        // error 
        echo ("error in startjob:" . $multiroute->return_code . "<br>" . $multiroute->error_description);
    }
} else {

    echo "sending Json Code:" . "<br>";
    echo($multiroute->show_json($optimize, $roundtrip, $tollfree, $avoid_highway, $uid, $start, $show_only, $optimize_endpoint, $time_window, $time_window_calculation_time));

    echo "result from webservicee:" . "<br>";
    // echo ($multiroute->getstatus($_SESSION["tid"]));
    if ($multiroute->getresult($_SESSION["tid"])) {
        echo ($multiroute->json_response);
    } else {
        // Fehler 
        echo ("error in getresult:" . $multiroute->status . "<br>" . $multiroute->error_text);
    }

    // show map 
    // check download path in class!
    // echo ($multiroute->getmap($_SESSION["tid"]));
}
?>
