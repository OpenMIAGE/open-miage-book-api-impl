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

$me = null;
OpenM_Log::info("INFO: REGISTRATION status", __CLASS__, __METHOD__, __LINE__);
echo "REGISTRATION status:<br>";
try {
    echo "check if registered:<br>";
    OpenM_Log::info("bookUserClient->getUserProperties()", __CLASS__, __METHOD__, __LINE__);
    $me = $bookUserClient->getUserProperties();
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
    try {
        echo "Try to register (2 years old):<br>";
        $bookUserClient->registerMe("Prénom test", "Nom test", Date::now()->less(Delay::years(2))->getTime());
    } catch (Exception $e) {
        OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
        echo "ERROR: " . $e->getMessage() . "<br>";
    }
    try {
        echo "Try to register (22 years old):<br>";
        $bookUserClient->registerMe("Prénom test", "Nom test", Date::now()->less(Delay::years(22))->getTime());
        $me = $bookClient->getUserProperties();
    } catch (Exception $e) {
        OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
        echo "ERROR: " . $e->getMessage() . "<br>";
    }
}

echoH($me, "Me");

try {
    OpenM_Log::info("bookClient->getCommunity", __CLASS__, __METHOD__, __LINE__);
    $communities = $bookClient->getCommunity();
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

echoH($communities, "Community");

echo "get Tree :<br>";
try {
    OpenM_Log::info("bookAdminClient->getTree", __CLASS__, __METHOD__, __LINE__);
    $tree = $bookAdminClient->getTree();
    $treeRemove = $tree->copy();
    echoH($tree, "Tree");
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

if ($tree instanceof HashtableString) {
    echo "add branch :<br>";
    try {
        OpenM_Log::info("bookAdminClient->addBranch(" . $tree->get(OpenM_Book_Admin::RETURN_BRANCH_PARAMETER)->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . ")", __CLASS__, __METHOD__, __LINE__);
        $child = $bookAdminClient->addBranch($tree->get(OpenM_Book_Admin::RETURN_BRANCH_PARAMETER)->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER), "Child Test");
        echoH($child, "new branch");
    } catch (Exception $e) {
        OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
        echo "ERROR: " . $e->getMessage() . "<br>";
    }


    if ($child instanceof HashtableString) {

        $c = $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER);
        echo "get Tree (" . $c . "):<br>";

        try {
            OpenM_Log::info("bookAdminClient->getTree($c)", __CLASS__, __METHOD__, __LINE__);
            $tree = $bookAdminClient->getTree($c);
            echoH($tree, "Tree");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }


        echo "add branch (" . $c . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->addBranch($c)", __CLASS__, __METHOD__, __LINE__);
            $child = $bookAdminClient->addBranch($c, "Sub Child Test");
            echoH($child, "branch");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }


        echo "get Tree (" . $c . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->getTree($c)", __CLASS__, __METHOD__, __LINE__);
            $tree = $bookAdminClient->getTree($c);
            echoH($tree, "Tree");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }


        echo "get Section Properties (" . $c . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->getBranchProperties($c)", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->getBranchProperties($c);
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }


        echo "set Section Properties (" . $c . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->setBranchProperty($c)", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->setBranchProperty($c, OpenM_Book_Admin::CONTAINS_ONLY_ONE_COMMUNITY_PARAMETER, OpenM_Book_Admin::TRUE_PARAMETER_VALUE);
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }

        echo "get Section Properties (" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->getBranchProperties(" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . ")", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->getBranchProperties($child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER));
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }


        echo "set Section Properties (" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . ") :<br>";
        try {
            OpenM_Log::info("bookAdminClient->setBranchProperty(" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . ")", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->setBranchProperty($child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER), OpenM_Book_Admin::CONTAINS_ONLY_ONE_COMMUNITY_PARAMETER, OpenM_Book_Admin::TRUE_PARAMETER_VALUE);
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }

        echo "get Section Properties (" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->getBranchProperties(" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . ")", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->getBranchProperties($child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER));
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }

        echo "set Section Properties (" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->setBranchProperty(" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . ")", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->setBranchProperty($child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER), OpenM_Book_Admin::CONTAINS_ONLY_ONE_COMMUNITY_PARAMETER, OpenM_Book_Admin::FALSE_PARAMETER_VALUE);
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }

        echo "set Section Properties (" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->setBranchProperty(" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . ")", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->setBranchProperty($child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER), OpenM_Book_Admin::COMMUNITY_NAME_REGEXP_PARAMETER, "[a-z]+");
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }

        echo "get Section Properties (" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->getBranchProperties(" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . ")", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->getBranchProperties($child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER));
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }

        echo "get Community (root):<br>";
        try {
            OpenM_Log::info("bookClient->getCommunity()", __CLASS__, __METHOD__, __LINE__);
            $childs = $bookClient->getCommunity();
            echoH($childs, "Community");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }


        if ($childs instanceof HashtableString) {

            echo "add Community in root KO:<br>";
            try {
                OpenM_Log::info("bookClient->addCommunity()", __CLASS__, __METHOD__, __LINE__);
                $childs = $bookClient->addCommunity("test community in root KO", $childs->get(OpenM_Book::RETURN_COMMUNITY_ID_PARAMETER));
                echoH($childs, "Community");
            } catch (Exception $e) {
                OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
                echo "ERROR: " . $e->getMessage() . "<br>";
            }
        }

        echo "set Section Properties (" . $c . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->setBranchProperty($c)", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->setBranchProperty($c, OpenM_Book_Admin::CONTAINS_ONLY_ONE_COMMUNITY_PARAMETER, OpenM_Book_Admin::FALSE_PARAMETER_VALUE);
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }


        if ($childs instanceof HashtableString) {

            echo "add Community in root OK:<br>";
            try {
                OpenM_Log::info("bookClient->addCommunity()", __CLASS__, __METHOD__, __LINE__);
                $childs = $bookClient->addCommunity("test community in root OK", $childs->get(OpenM_Book::RETURN_COMMUNITY_ID_PARAMETER));
                echoH($childs, "Community");
            } catch (Exception $e) {
                OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
                echo "ERROR: " . $e->getMessage() . "<br>";
            }
        }

        if ($childs instanceof HashtableString) {

            echo "add Community level 2 test name KO:<br>";
            try {
                OpenM_Log::info("bookClient->addCommunity()", __CLASS__, __METHOD__, __LINE__);
                $childs = $bookClient->addCommunity("test communityname KO", $childs->get(OpenM_Book::RETURN_COMMUNITY_ID_PARAMETER));
                echoH($childs, "Community");
            } catch (Exception $e) {
                OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
                echo "ERROR: " . $e->getMessage() . "<br>";
            }
        }


        if ($childs instanceof HashtableString) {

            echo "add Community level 2 test name OK:<br>";
            try {
                OpenM_Log::info("bookClient->addCommunity()", __CLASS__, __METHOD__, __LINE__);
                $c2 = $childs->get(OpenM_Book::RETURN_COMMUNITY_ID_PARAMETER);
                $childs = $bookClient->addCommunity("testcommunitynameok", $childs->get(OpenM_Book::RETURN_COMMUNITY_ID_PARAMETER));
                echoH($childs, "Community");
            } catch (Exception $e) {
                OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
                echo "ERROR: " . $e->getMessage() . "<br>";
            }
        }

        if ($childs instanceof HashtableString) {

            echo "add Community level 2 test name KO:<br>";
            try {
                OpenM_Log::info("bookClient->addCommunity()", __CLASS__, __METHOD__, __LINE__);
                $childs = $bookClient->addCommunity("testcommunitynameKO", $c2);
                echoH($childs, "Community");
            } catch (Exception $e) {
                OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
                echo "ERROR: " . $e->getMessage() . "<br>";
            }
        }


        echo "set Section Properties (" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . "):<br>";
        try {
            OpenM_Log::info("bookAdminClient->setBranchProperty(" . $child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER) . ")", __CLASS__, __METHOD__, __LINE__);
            $properties = $bookAdminClient->setBranchProperty($child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER), OpenM_Book_Admin::COMMUNITY_NAME_REGEXP_PARAMETER, "[a-zA-Z]+");
            echoH($properties, "Section Properties");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }

        if ($childs instanceof HashtableString) {

            echo "add Community level 2 test name OK:<br>";
            try {
                OpenM_Log::info("bookClient->addCommunity()", __CLASS__, __METHOD__, __LINE__);
                $childs = $bookClient->addCommunity("testcommunitynameOK", $c2);
                echoH($childs, "Community");
            } catch (Exception $e) {
                OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
                echo "ERROR: " . $e->getMessage() . "<br>";
            }
        }


        if ($childs instanceof HashtableString) {

            echo "add Community level 2 test name KO:<br>";
            try {
                OpenM_Log::info("bookClient->addCommunity()", __CLASS__, __METHOD__, __LINE__);
                $childs = $bookClient->addCommunity("testcommunityname KO", $c2);
                echoH($childs, "Community");
            } catch (Exception $e) {
                OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
                echo "ERROR: " . $e->getMessage() . "<br>";
            }
        }


        echo "get Community (root):<br>";
        try {
            OpenM_Log::info("bookClient->getCommunity()", __CLASS__, __METHOD__, __LINE__);
            $childs = $bookClient->getCommunity();
            echoH($childs, "Community");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }


        echo "get Community ($c2):<br>";
        try {
            OpenM_Log::info("bookClient->getCommunity($c2)", __CLASS__, __METHOD__, __LINE__);
            $childs = $bookClient->getCommunity($c2);
            echoH($childs, "Community");
        } catch (Exception $e) {
            OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
            echo "ERROR: " . $e->getMessage() . "<br>";
        }
    }
}

echo "get Admins :<br>";
try {
    OpenM_Log::info("bookAdminClient->getAdmins", __CLASS__, __METHOD__, __LINE__);
    $admins = $bookAdminClient->getAdmins();
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}
echoH($admins, "Admins");

echo "remove Admin :<br>";
try {
    OpenM_Log::info("bookAdminClient->removeAdmin()", __CLASS__, __METHOD__, __LINE__);
    $keys = $admins->get(OpenM_Book_Admin::RETURN_ADMIN_LIST_PARAMETER)->keys();
    $admins = $bookAdminClient->removeAdmin($keys->next());
} catch (Exception $e) {
    OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
    echo "ERROR: " . $e->getMessage() . "<br>";
}

function removeAllSection($secionId = null) {
    global $bookAdminClient;
    try {
        OpenM_Log::info("bookAdminClient->getTree($secionId)", __CLASS__, __METHOD__, __LINE__);
        $childs = $bookAdminClient->getTree($secionId);
        $childs = $childs->get(OpenM_Book_Admin::RETURN_BRANCH_CHILDS_PARAMETER);
        $e = $childs->keys();
        while ($e->hasNext()) {
            $key = $e->next();
            $child = $childs->get($key);
            removeAllSection($child->get(OpenM_Book_Admin::RETURN_BRANCH_ID_PARAMETER));
        }
        OpenM_Log::info("bookAdminClient->removeBranch($secionId)", __CLASS__, __METHOD__, __LINE__);
        echo "remove branch $secionId<br>";
        $bookAdminClient->removeBranch($secionId);
    } catch (Exception $e) {
        OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
        echo "ERROR: " . $e->getMessage() . "<br>";
    }
}

function removeAllCommunity($communityId = null) {
    global $bookClient, $bookModeratorClient;
    try {
        OpenM_Log::info("bookClient->getCommunity($communityId)", __CLASS__, __METHOD__, __LINE__);
        $childs = $bookClient->getCommunity($communityId);
        $childs = $childs->get(OpenM_Book::RETURN_COMMUNITY_CHILDS_PARAMETER);
        if ($childs != null) {
            $e = $childs->enum();
            while ($e->hasNext()) {
                $community = $e->next();
                removeAllCommunity($community->get(OpenM_Book::RETURN_COMMUNITY_ID_PARAMETER));
            }
        }
        OpenM_Log::info("bookModeratorClient->removeCommunity($communityId)", __CLASS__, __METHOD__, __LINE__);
        echo "remove community $communityId<br>";
        $bookModeratorClient->removeCommunity($communityId);
    } catch (Exception $e) {
        OpenM_Log::error("ERROR: " . $e->getMessage(), __CLASS__, __METHOD__, __LINE__);
        echo "ERROR: " . $e->getMessage() . "<br>";
    }
}

//removeAllCommunity();
//removeAllSection();
?>