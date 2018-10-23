<?php
/**
 *   (C) Copyright 1997-2013 hSenid International (pvt) Limited.
 *   All Rights Reserved.
 *
 *   These materials are unpublished, proprietary, confidential source code of
 *   hSenid International (pvt) Limited and constitute a TRADE SECRET of hSenid
 *   International (pvt) Limited.
 *
 *   hSenid International (pvt) Limited retains all title to and intellectual
 *   property rights in these materials.
 */

include_once '../../lib/ussd/MoUssdReceiver.php';
include_once '../../lib/ussd/MtUssdSender.php';
include_once '../log.php';
ini_set('error_log', 'ussd-app-error.log');

$receiver = new MoUssdReceiver(); // Create the Receiver object

$receiverSessionId = $receiver->getSessionId();
session_id($receiverSessionId); //Use received session id to create a unique session
session_start();

$content = $receiver->getMessage(); // get the message content
$address = $receiver->getAddress(); // get the sender's address
$requestId = $receiver->getRequestID(); // get the request ID
$applicationId = $receiver->getApplicationId(); // get application ID
$encoding = $receiver->getEncoding(); // get the encoding value
$version = $receiver->getVersion(); // get the version
$sessionId = $receiver->getSessionId(); // get the session ID;
$ussdOperation = $receiver->getUssdOperation(); // get the ussd operation

logFile("[ content=$content, address=$address, requestId=$requestId, applicationId=$applicationId, encoding=$encoding, version=$version, sessionId=$sessionId, ussdOperation=$ussdOperation ]");

//your logic goes here......
$responseMsg = array(
    "main" => "Welcome To KieYMember Service Platform.Pls select Your Option:
                    1.Farm Emergency
                    2.Animal Health
                    3.Service Request
                    000.Exit",
    "Farm-Emergency" => "Farm Emergency
                    1.Veld Fire
                    2.Theft
                    3.Emergency Medical Response
                    4.Call Sector Support
                    999.Back",
    "Animal-Health" => "Animal Health
                    1.Herd health security plan
                    2.Disease Diagnosis
                    3.Vaccination-Immunization
                    999.Back",
    "Service-Request" => "Service Request
                    1.Trade
                    2.Feed Supplement
                    3.Logistics Transport
                    999.Back",
    "Veld-Fire" => "Local Veld Support Will call and verify severity. Thank You!
                    999.Back",
    "Theft" => "Farmers Allerted and sector support will dispatch support.
                    999.Back",
    "Emergency-Medical-Response" => "Sector Support will contact and confirm TOA.
                    999.Back",
    "Call-Sector-Support" => "Sector Support will contact you. Thank You. Thank you.
                    999.Back",
    "Herd-health-security-plan" => "Your sector will be notified in 15-days for training & plan set-up.
                    999.Back",
    "Disease-Diagnosis" => "Sector Support will contact you. Thank You.
                    999.Back",
    "Vaccination-Immunization" => "Sector Support will contact you. Thank you.
                    999.Back",
    "Trade" => "To Buy, Sell, Swop - Dial *120*543*
                    999.Back",
        "Feed-Supplement" => "Sector Support will contact you. Thank You. 
                    999.Back",
    "Logistics-Transport" => "Sector Support will contact you. Thank You.
                    999.Back"
);

logFile("Previous Menu is := " . $_SESSION['menu-Opt']); //Get previous menu number
if (($receiver->getUssdOperation()) == "mo-init") { //Send the main menu
    loadUssdSender($sessionId, $responseMsg["main"]);
    if (!(isset($_SESSION['menu-Opt']))) {
        $_SESSION['menu-Opt'] = "main"; //Initialize main menu
    }

}
if (($receiver->getUssdOperation()) == "mo-cont") {
    $menuName = null;

    switch ($_SESSION['menu-Opt']) {
        case "main":
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "Farm-Emergency";
                    break;
                case "2":
                    $menuName = "Animal-Health";
                    break;
                case "3":
                    $menuName = "Service-Request";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName; //Assign session menu name
            break;
        case "Farm-Emergency":
            $_SESSION['menu-Opt'] = "Farm-Emergency-hist"; //Set to company menu back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "Veld-Fire";
                    break;
                case "2":
                    $menuName = "Theft";
                    break;
                case "3":
                    $menuName = "Emergency-Medical-Response";
                    break;
                case "4":
                    $menuName = "Call-Sector-Support";
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            break;
        case "Animal-Health":
            $_SESSION['menu-Opt'] = "Animal-Health-hist"; //Set to product menu back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "Herd-health-security-plan";
                    break;
                case "2":
                    $menuName = "Disease-Diagnosis";
                    break;
                case "3":
                    $menuName = "Vaccination-Immunization";
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            break;
        case "Service-Request":
            $_SESSION['menu-Opt'] = "Service-Request-hist"; //Set to career menu back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "Trade";
                    break;
                case "2":
                    $menuName = "Feed-Supplement";
                    break;
                case "3":
                    $menuName = "Logistics-Transport";
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            break;
        case "Farm-Emergency-hist" || "Animal-Health-hist" || "Service-Request-hist":
            switch ($_SESSION['menu-Opt']) { //Execute menu back sessions
                case "Farm-Emergency-hist":
                    $menuName = "Farm-Emergency";
                    break;
                case "Animal-Health-hist":
                    $menuName = "Animal-Health";
                    break;
                case "Service-Request-hist":
                    $menuName = "Service-Request";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName; //Assign previous session menu name
            break;
    }

    if ($receiver->getMessage() == "000") {
        $responseExitMsg = "Exit Program!";
        $response = loadUssdSender($sessionId, $responseExitMsg);
        session_destroy();
    } else {
        logFile("Selected response message := " . $responseMsg[$menuName]);
        $response = loadUssdSender($sessionId, $responseMsg[$menuName]);
    }

}
/*
    Get the session id and Response message as parameter
    Create sender object and send ussd with appropriate parameters
**/

function loadUssdSender($sessionId, $responseMessage)
{
    $password = "password";
    $destinationAddress = "tel:94771122336";
    if ($responseMessage == "000") {
        $ussdOperation = "mt-fin";
    } else {
        $ussdOperation = "mt-cont";
    }
    $chargingAmount = "5";
    $applicationId = "APP_000001";
    $encoding = "440";
    $version = "1.0";

    try {
        // Create the sender object server url

//        $sender = new MtUssdSender("http://localhost:7000/ussd/send/");   // Application ussd-mt sending http url
        $sender = new MtUssdSender("https://localhost:7443/ussd/send/"); // Application ussd-mt sending https url
        $response = $sender->ussd($applicationId, $password, $version, $responseMessage,
            $sessionId, $ussdOperation, $destinationAddress, $encoding, $chargingAmount);
        return $response;
    } catch (UssdException $ex) {
        //throws when failed sending or receiving the ussd
        error_log("USSD ERROR: {$ex->getStatusCode()} | {$ex->getStatusMessage()}");
        return null;
    }
}

?>