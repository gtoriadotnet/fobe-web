<?php

use Alphaland\Client\ClientSettingsApplications;
use Alphaland\Web\WebContextManager;

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: -1");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') 
{
    die("{}");
}

if (!isset($_GET['key']))
{
    die("{}");
}

$key = cleanInput($_GET['key']);


if (!ClientSettingsApplications::ApplicationExists($key))
{
    // echo back empty json
    die("{}");
}

if (!ClientSettingsApplications::ApplicationCanBeFetchedFromClientSettingsService($key))
{
    // echo back empty json
    die("{}");
}

$requiresIpWhitelist = ClientSettingsApplications::ApplicationRequiresIpWhitelist($key);
$requiresRccAuth = ClientSettingsApplications::ApplicationRequiresRccServiceAuthentication($key);

if ($requiresIpWhitelist && !WebContextManager::IsCurrentIpAddressWhitelisted())
{
    http_response_code(403);
    die("{}");
}

if ($requiresRccAuth && !WebContextManager::VerifyAccessKeyHeader())
{
    http_response_code(403);
    die("{}");
}

$settings = ClientSettingsApplications::FetchCombinedApplicationDependencies($key);

if (empty($settings))
{
    die("{}");
}

echo json_encode($settings);