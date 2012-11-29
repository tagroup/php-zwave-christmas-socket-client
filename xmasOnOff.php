<?php
/**
 * User: thomasloughlin
 * Date: 11/28/12
 * Time: 7:59 PM
 *
 * This script runs every 10 minutes.  Other ways to cut down on the number of calls such as restricting the run time
 * to 17-18 (5-6pm) and again at 1:05am.
 *
 **/
date_default_timezone_set('America/Chicago');
require('zwaveSocketClient.php');

$sunset=date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, 30.3233, -95.4714,90.8333);
//correct for daylight savings time
if(date('I'))
{
    $sunset+=(60*60);
}

//echo date('m/d/Y h:i', $sunset);
/******************************
 *
 *  if the time is 1:00 - 1:10 the light go off
 *
 ******************************/

if(date('G') == 1 && date('i') < 10)
{
    $zwave= new zwaveSocketClient('192.168.20.172',6004);
//Turn Christmas Lights Off
    $zwave->toggleBinaryGroup('Christmas',0);
    mail('info@theanellgroup.com','Christmas Lights are Off','Go to sleep', 'From: info@theanellgroup.com');
    echo "\nChristmas Lights Off at " . mktime() ."\n";
}

/********************************
 *
 * if the time is within 10 minutes of sunset, the lights go on
 *
 ********************************/

if(date('G')==date('G',$sunset) && (date('i') >= date('i',$sunset) && date('i') < date('i',$sunset + 600)))
{
    $zwave= new zwaveSocketClient('192.168.20.172',6004);
//Turn Christmas Lights Off
    $zwave->toggleBinaryGroup('Christmas',255);
    mail('info@theanellgroup.com','Christmas Lights are On','Have fun on your bike ride', 'From: info@theanellgroup.com');
    echo "\nChristmas Lights On at " . mktime() ."\n";
}

