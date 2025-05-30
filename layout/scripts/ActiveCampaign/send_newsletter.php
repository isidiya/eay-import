<?php
require_once "vendor/autoload.php";
require_once "config.php";

$subject   = config::subject . " ". date("Y-m-d H:i:s");
$url       = config::newsletter_url;
$fromname  = config::from_name;
$fromemail = config::from_email;

$mailing_lists = explode(",", config::mailing_lists);

//$content = file_get_contents($url);
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

$content = file_get_contents($url, false, stream_context_create($arrContextOptions));

$ac = new ActiveCampaign(config::active_campaign_url, config::active_campaign_api_key);

if (!(int)$ac->credentials_test()) {
    error_response("Access denied: Invalid credentials (URL and/or API key)");
}

/*
 * ADD NEW EMAIL MESSAGE (FOR A CAMPAIGN).
 */

$message = [
    "format"    => "mime",
    "subject"   => $subject,
    "fromemail" => $fromemail,
    "fromname"  => $fromname,
    "html"      => $content,
];

foreach ($mailing_lists as $mailing_list_id) {
    $message["p[{$mailing_list_id}]"] = $mailing_list_id;
}

$message_add = $ac->api("message/add", $message);

if (!(int)$message_add->success) {
    // request failed
    error_response("Adding email message failed. Error returned: " . $message_add->error);
}

// successful request
$message_id = (int)$message_add->id;
// echo "<p>Message added successfully (ID {$message_id})!</p>";

/*
 * CREATE NEW CAMPAIGN (USING THE EMAIL MESSAGE CREATED ABOVE).
 */

$campaign = [
    "type"             => "single",
    "name"             => $subject, // internal name (message subject above is what contacts see)
    "sdate"            => "2013-07-01 00:00:00",
    "status"           => 1,
    "public"           => 1,
    "tracklinks"       => "all",
    "trackreads"       => 1,
    "htmlunsub"        => 1,
    "m[{$message_id}]" => 100, // 100 percent of subscribers
];


foreach ($mailing_lists as $mailing_list_id) {
    $campaign["p[{$mailing_list_id}]"] = $mailing_list_id;
}

$campaign_create = $ac->api("campaign/create", $campaign);

if (!(int)$campaign_create->success) {
    // request failed
    error_response("Creating campaign failed. Error returned: " . $campaign_create->error);
}

// successful request
$campaign_id = (int)$campaign_create->id;
success_response("Campaign created and sent! (ID {$campaign_id})!");


function success_response($message, $code = 200)
{
    $response = ['success' => 1, 'code' => $code, 'message' => $message];
    echo json_encode($response);
    exit;
}

function error_response($message, $code = 500)
{
    $response = ['success' => 0, 'code' => $code, 'message' => $message];
    echo json_encode($response);
    exit;
}

