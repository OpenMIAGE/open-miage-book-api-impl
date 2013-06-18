<?php

require_once 'src.php';
require_once 'lib.php';

Import::php("util.OpenM_Log");
OpenM_Log::init("./", OpenM_Log::DEBUG, "log", 2000);

define("OpenM_SERVICE_CONFIG_FILE_NAME", "../Config/config.properties");

Import::php("OpenM-SSO.client.OpenM_SSOClientPoolSessionManager");
Import::php("OpenM-Services.client.OpenM_ServiceSSOClientImpl");
Import::php("OpenM-Book.api.OpenM_Book_Admin");
Import::php("OpenM-Book.api.OpenM_Book");
Import::php("OpenM-Book.api.OpenM_Book_User");

$manager = OpenM_SSOClientPoolSessionManager::fromFile("config.client.properties");
$sso = $manager->get("sso");

$bookClient = new OpenM_ServiceSSOClientImpl($sso, "OpenM_Book");
$bookUserClient = new OpenM_ServiceSSOClientImpl($sso, "OpenM_Book_User");
$bookAdminClient = new OpenM_ServiceSSOClientImpl($sso, "OpenM_Book_Admin");
$bookModeratorClient = new OpenM_ServiceSSOClientImpl($sso, "OpenM_Book_Moderator");

function echoH($me, $lineMarkup) {
    if ($me instanceof HashtableString) {
        $e = $me->keys();
        while ($e->hasNext()) {
            $key = $e->next();
            $line = $me->get($key);
            echo " - $lineMarkup: $key";
            if (!($line instanceof HashtableString))
                echo "=$line<br>";
            else {
                echo ":<br>";
                $e2 = $line->keys();
                while ($e2->hasNext()) {
                    $key2 = $e2->next();
                    $line2 = $line->get($key2);
                    echo " --> $lineMarkup: $key2=";
                    if (!($line2 instanceof HashtableString))
                        echo "$line2<br>";
                    else
                        echo OpenM_MapConvertor::mapToJSON($line2) . "<br>";
                }
            }
        }
    }
}

try {
    $me = $bookUserClient->getUserProperties();
    echoH($me, "getUserProperties()");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->getUserProperties(null, OpenM_Service::TRUE_PARAMETER_VALUE);
    echoH($me, "getUserProperties()");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->getUserProperties(null, OpenM_Service::FALSE_PARAMETER_VALUE);
    echoH($me, "getUserProperties(null, false)");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->getUserProperties(69, OpenM_Service::TRUE_PARAMETER_VALUE);
    echoH($me, "getUserProperties(69, true)");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->getUserProperties(69, OpenM_Service::FALSE_PARAMETER_VALUE);
    echoH($me, "getUserProperties(69, false)");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}


try {
    $me = $bookUserClient->setPropertyValue(OpenM_Book_User::FIRST_NAME_PROPERTY_VALUE_ID, "bad.name");
    echoH($me, "setPropertyValue(" . OpenM_Book_User::FIRST_NAME_PROPERTY_VALUE_ID . ", 'bad.name')");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->setPropertyValue(OpenM_Book_User::FIRST_NAME_PROPERTY_VALUE_ID, "héhéhéhé strop cool");
    echoH($me, "setPropertyValue(" . OpenM_Book_User::FIRST_NAME_PROPERTY_VALUE_ID . ", 'héhéhéhé strop cool')");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->getUserProperties(null, OpenM_Service::TRUE_PARAMETER_VALUE);
    echoH($me, "getUserProperties()");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->setPropertyValue(OpenM_Book_User::LAST_NAME_PROPERTY_VALUE_ID, "lâst-name");
    echoH($me, "setPropertyValue(" . OpenM_Book_User::LAST_NAME_PROPERTY_VALUE_ID . ", 'lâst-name')");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->setPropertyValue(OpenM_Book_User::LAST_NAME_PROPERTY_VALUE_ID, "lâst name");
    echoH($me, "setPropertyValue(" . OpenM_Book_User::LAST_NAME_PROPERTY_VALUE_ID . ", 'lâst name')");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->getUserProperties(null, OpenM_Service::TRUE_PARAMETER_VALUE);
    echoH($me, "getUserProperties()");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->setPropertyValue(OpenM_Book_User::PHOTO_ID_PROPERTY_VALUE_ID, "djo");
    echoH($me, "setPropertyValue(" . OpenM_Book_User::PHOTO_ID_PROPERTY_VALUE_ID . ", 'djo')");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->setPropertyValue(OpenM_Book_User::PHOTO_ID_PROPERTY_VALUE_ID, 34);
    echoH($me, "setPropertyValue(" . OpenM_Book_User::PHOTO_ID_PROPERTY_VALUE_ID . ", 34)");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->getUserProperties(null, OpenM_Service::FALSE_PARAMETER_VALUE);
    echoH($me, "getUserProperties()");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}


try {
    $id = $me->get(OpenM_Book_User::RETURN_USER_PROPERTY_LIST_PARAMETER)->get(1)->get(OpenM_Book_User::RETURN_USER_PROPERTY_VALUE_ID_PARAMETER);
    $me = $bookUserClient->setPropertyValue(intval($id), 34);
    echoH($me, "setPropertyValue($id, 34)");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

try {
    $me = $bookUserClient->getUserProperties(null, OpenM_Service::FALSE_PARAMETER_VALUE);
    echoH($me, "getUserProperties()");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}
?>