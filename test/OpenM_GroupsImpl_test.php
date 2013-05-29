<?php

require_once 'src.php';
require_once 'lib.php';

Import::php("util.OpenM_Log");
OpenM_Log::init("./", OpenM_Log::DEBUG, "log", 2000);

Import::php("OpenM-SSO.client.OpenM_SSOClientPoolSessionManager");
$manager = OpenM_SSOClientPoolSessionManager::fromFile("config.client.properties");
$sso = $manager->get("sso");

Import::php("OpenM-Services.client.OpenM_ServiceSSOClientImpl");
Import::php("OpenM-Book.api.OpenM_Groups");
$groupClient = new OpenM_ServiceSSOClientImpl($sso, "OpenM_Groups");

$return = $groupClient->isUserInGroups(json_encode(array(35, 36)));
$e = $return->keys();
while ($e->hasNext()) {
    $key = $e->next();
    echo $key . " = " . $return->get($key) . "<br>";
}
echo "getMyPersonalGroups<br>";
$last = null;
try {
    $return = $groupClient->getMyPersonalGroups();
    $e = $return->get(OpenM_Groups::RETURN_GROUP_LIST_PARAMETER)->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        $group = $return->get(OpenM_Groups::RETURN_GROUP_LIST_PARAMETER)->get($key);
        $last = $group;
        $e2 = $group->keys();
        while ($e2->hasNext()) {
            $key = $e2->next();
            echo "- $key=>" . $group->get($key) . "<br>";
        }
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "getMyBookGroups<br>";
try {
    $return = $groupClient->getMyBookGroups();
    $e = $return->get(OpenM_Groups::RETURN_GROUP_LIST_PARAMETER)->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        $group = $return->get(OpenM_Groups::RETURN_GROUP_LIST_PARAMETER)->get($key);
        $e2 = $group->keys();
        while ($e2->hasNext()) {
            $key = $e2->next();
            echo "- $key=>" . $group->get($key) . "<br>";
        }
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "getMyGroups<br>";
try {
    $return = $groupClient->getMyGroups();
    $e = $return->get(OpenM_Groups::RETURN_GROUP_LIST_PARAMETER)->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        $group = $return->get(OpenM_Groups::RETURN_GROUP_LIST_PARAMETER)->get($key);
        $e2 = $group->keys();
        while ($e2->hasNext()) {
            $key = $e2->next();
            echo "- $key=>" . $group->get($key) . "<br>";
        }
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
$group1 = null;
$group2 = null;
for ($i = 0; $i < 20; $i++) {
    echo "createGroup $i<br>";
    try {
        $return = $groupClient->createGroup("my group test $i");
        $group1 = $group2;
        $group2 = $return->get(OpenM_Groups::RETURN_GROUP_ID_PARAMETER);
        if (!$return->containsKey(OpenM_Groups::RETURN_ERROR_PARAMETER)) {
            $e = $return->keys();
            while ($e->hasNext()) {
                $key = $e->next();
                echo "- $key=>" . $return->get($key) . "<br>";
            }
        }
    } catch (Exception $e) {
        echo $e->getTraceAsString() . "<br>";
    }
}
echo "renameGroup<br>";
try {
    $return = $groupClient->renameGroup($return->get(OpenM_Groups::RETURN_GROUP_ID_PARAMETER), "my group test 2");
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "renameGroup[ERROR]<br>";
try {
    $return = $groupClient->renameGroup(1999, "my group test 2");
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "removeGroup [ERROR]<br>";
try {
    $return = $groupClient->removeGroup(10000);
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "search<br>";
try {
    $return = $groupClient->search("m g", 18);
    $return = $return->get(OpenM_Groups::RETURN_RESULT_LIST_PARAMETER);
    $e = $return->keys();
    while ($e->hasNext()) {
        $row = $return->get($e->next());
        $e2 = $row->keys();
        while ($e2->hasNext()) {
            $key = $e2->next();
            echo "- $key=>" . $row->get($key) . "<br>";
        }
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "addGroupIntoGroup<br>";
try {
    $return = $groupClient->addGroupIntoGroup($group1, $group2);
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "addGroupIntoGroup [ERROR]<br>";
try {
    $return = $groupClient->addGroupIntoGroup(-1, $group2);
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}

echo "addGroupIntoGroup [ERROR2]<br>";
try {
    $return = $groupClient->addGroupIntoGroup($group1, 10000);
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "addGroupIntoGroup [ERROR3]<br>";
try {
    $return = $groupClient->addGroupIntoGroup(10000, -19);
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "removeGroupFromGroup [ERROR]<br>";
try {
    $return = $groupClient->removeGroupFromGroup(10000, $group2);
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "removeGroupFromGroup [ERROR2]<br>";
try {
    $return = $groupClient->removeGroupFromGroup($group2, $group1);
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
echo "removeGroupFromGroup [ERROR3]<br>";
try {
    $return = $groupClient->removeGroupFromGroup($group1, 10000);
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}

echo "removeGroupFromGroup<br>";
try {
    $return = $groupClient->removeGroupFromGroup($group1, $group2);
    $e = $return->keys();
    while ($e->hasNext()) {
        $key = $e->next();
        echo "- $key=>" . $return->get($key) . "<br>";
    }
} catch (Exception $e) {
    echo $e->getTraceAsString() . "<br>";
}
?>