<?php

Import::php("OpenM-Book.api.OpenM_Groups");
Import::php("OpenM-Book.api.Impl.OpenM_BookCommonsImpl");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl
 * @license http://www.apache.org/licenses/LICENSE-2.0 Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @link http://www.open-miage.org
 * @author Gaël Saunier
 */
class OpenM_GroupsImpl extends OpenM_BookCommonsImpl implements OpenM_Groups {

    public function addGroupIntoGroup($groupId, $groupIdTarget) {
        if (!OpenM_Book_Tool::isGroupIdValid($groupId))
            return $this->error("groupId must be an integer");
        if (!OpenM_Book_Tool::isGroupIdValid($groupIdTarget))
            return $this->error("groupIdTarget must be an integer");
        if ($groupId == $groupIdTarget)
            return $this->error("groupIdTarget must be different with groupId");

        if (!$this->isUserRegistered())
            return $this->error;
        else
            $user = $this->user;

        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        if (!$groupContentGroupDAO->isDescendant($groupIdTarget, $user->get(OpenM_Book_UserDAO::PERSONAL_GROUPS)))
            return $this->error(self::RETURN_ERROR_MESSAGE_NOT_YOUR_PERSONAL_GROUP_VALUE);

        if ($groupContentGroupDAO->isDescendant($groupIdTarget, $groupId))
            return $this->error(self::RETURN_ERROR_MESSAGE_FORBIDDEN_OPERATION_INFINIT_CYCLE_ERROR_VALUE);

        $groupContentGroupDAO->create($groupIdTarget, $groupId);
        return $this->ok();
    }

    public function addUserIntoGroup($userId, $groupId) {
        if (!OpenM_Book_Tool::isUserIdValid($userId))
            return $this->error("userId must be an integer");
        if (!OpenM_Book_Tool::isGroupIdValid($groupId))
            return $this->error("groupId must be an integer");

        if (!$this->isUserRegistered())
            return $this->error;

        $userDAO = new OpenM_Book_UserDAO();
        if ($userDAO->getFromId($userId) == null)
            return $this->error(self::RETURN_ERROR_MESSAGE_USER_NOT_FOUND_VALUE);

        $groupContentUser = new OpenM_Book_Group_Content_UserDAO();
        $groupContentUser->create($groupId, $userId, true);

        return $this->ok();
    }

    public function createGroup($groupName) {
        if (!$this->isGroupNameValid($groupName))
            return $this->error("groupName must be valid");

        if (!$this->isUserRegistered())
            return $this->error;
        else
            $user = $this->user;

        $groupDAO = new OpenM_Book_GroupDAO();
        $group = $groupDAO->create($groupName);
        $searchDAO = new OpenM_Book_SearchDAO();
        $searchDAO->index($groupName, $group->get(OpenM_Book_GroupDAO::ID), OpenM_Book_SearchDAO::TYPE_PERSONAL_GROUP, $user->get(OpenM_Book_UserDAO::ID));
        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        $groupContentGroupDAO->create($user->get(OpenM_Book_UserDAO::PERSONAL_GROUPS), $group->get(OpenM_Book_GroupDAO::ID));
        return $this->ok()
                        ->put(self::RETURN_GROUP_ID_PARAMETER, $group->get(OpenM_Book_GroupDAO::ID))
                        ->put(self::RETURN_GROUP_NAME_PARAMETER, $group->get(OpenM_Book_GroupDAO::NAME))
                        ->put(self::RETURN_GROUP_TYPE_PARAMETER, $group->get(OpenM_Book_GroupDAO::TYPE));
    }

    /**
     * TODO : getCommunity of another user
     */
    public function getCommunities($userId = null, $withAncestors = null) {
        if (!$this->isUserRegistered())
            return $this->error;
        if ($withAncestors != null && $withAncestors == self::TRUE_PARAMETER_VALUE)
            $withAncestors = true;
        else
            $withAncestors = false;

        if ($userId !== "" && $userId !== null && $userId != $this->user->get(OpenM_Book_UserDAO::ID))
            return $this->ok();

        $uid = $this->getManager()->getID();
        OpenM_Log::debug("search my communities in DAO", __CLASS__, __METHOD__, __LINE__);
        $groupContentUserDAO = new OpenM_Book_Group_Content_UserDAO();
        $communities = $groupContentUserDAO->getFromUID($uid, true, false);
        OpenM_Log::debug("translate communities to return format", __CLASS__, __METHOD__, __LINE__);
        $r = $this->getGroups($communities, false);
        $return = $this->ok()->put(self::RETURN_GROUP_LIST_PARAMETER, $r);
        OpenM_Log::debug("check if withAncestor is activated", __CLASS__, __METHOD__, __LINE__);
        if ($withAncestors) {
            OpenM_Log::debug("withAncestor is activated", __CLASS__, __METHOD__, __LINE__);
            $ancestors = $groupContentUserDAO->getMyCommunitiesAncestors($this->user->get(OpenM_Book_UserDAO::ID)->toInt());
            $e = $ancestors->keys();
            $a = new HashtableString();
            $return->put(self::RETURN_COMMUNITY_ANCESTORS_LIST, $a);
            while ($e->hasNext()) {
                $line = $ancestors->get($e->next());
                $l = new HashtableString();
                $l->put(self::RETURN_COMMUNITY_ID_PARAMETER, $line->get(OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID)->toInt())
                        ->put(self::RETURN_COMMUNITY_NAME_PARAMETER, $line->get(OpenM_Book_GroupDAO::NAME));
                $a->put($line->get(OpenM_Book_Group_Content_GroupDAO::GROUP_ID)->toInt(), $l);
            }
        }
        return $return;
    }

    public function getMyCommunitiesAndGroups() {

        if (!$this->isUserRegistered())
            return $this->error;

        $userId = $this->getManager()->getID();
        $groupContentUserDAO = new OpenM_Book_Group_Content_UserDAO();
        return $this->ok()->put(self::RETURN_GROUP_LIST_PARAMETER, $this->getGroups($groupContentUserDAO->getFromUID($userId)));
    }

    public function getMyGroups() {
        if (!$this->isUserRegistered())
            return $this->error;

        $uid = $this->getManager()->getID();
        OpenM_Log::debug("search my groups in DAO", __CLASS__, __METHOD__, __LINE__);
        $groupContentUserDAO = new OpenM_Book_Group_Content_UserDAO();
        $groups = $groupContentUserDAO->getFromUID($uid, false, true);
        OpenM_Log::debug("translate groups to return format", __CLASS__, __METHOD__, __LINE__);
        $formatedGroups = $this->getGroups($groups);
        return $this->ok()->put(self::RETURN_GROUP_LIST_PARAMETER, $formatedGroups);
    }

    private function getGroups(HashtableString $groupList, $displayType = true) {
        $e = $groupList->enum();
        $return = new HashtableString();
        $i = 0;
        while ($e->hasNext()) {
            $group = $e->next();
            $g = new HashtableString();
            $g->put(self::RETURN_GROUP_ID_PARAMETER, $group->get(OpenM_Book_GroupDAO::ID)->toInt())
                    ->put(self::RETURN_GROUP_NAME_PARAMETER, $group->get(OpenM_Book_GroupDAO::NAME));

            if ($displayType)
                $g->put(self::RETURN_GROUP_TYPE_PARAMETER, $group->get(OpenM_Book_GroupDAO::TYPE)->toInt());

            $return->put($i, $g);
            $i++;
        }
        return $return;
    }

    private function getUsers(HashtableString $users) {
        $e = $users->enum();
        $return = new HashtableString();
        while ($e->hasNext()) {
            $group = $e->next();
            $g = new HashtableString();
            $g->put(self::RETURN_USER_ID_PARAMETER, $group->get(OpenM_Book_UserDAO::ID))
                    ->put(self::RETURN_GROUP_NAME_PARAMETER, $group->get(OpenM_Book_UserDAO::FIRST_NAME) . " " . $group->get(OpenM_Book_UserDAO::LAST_NAME));
            $return->put($group->get(OpenM_Book_UserDAO::ID), $g);
        }
        return $return;
    }

    public function isUserInGroups($groupIdJSONList) {
        $userId = $this->getManager()->getID();
        $array = json_decode($groupIdJSONList);
        $arrayChecked = array();
        foreach ($array as $value) {
            if (!is_numeric($value))
                return $this->error("groupIdJSONList must contain only numeric");
            $arrayChecked[] = $value;
        }
        $userGroupDAO = new OpenM_Book_Group_Content_UserDAO();
        if ($userGroupDAO->isUserInGroups($userId, $arrayChecked))
            return $this->ok()->put(self::RETURN_USER_IN_GROUP_PARAMETER, self::RETURN_USER_IN_GROUP_TRUE_VALUE);
        else
            return $this->ok()->put(self::RETURN_USER_IN_GROUP_PARAMETER, self::RETURN_USER_IN_GROUP_FALSE_VALUE);
    }

    public function removeGroup($groupId) {
        if (!OpenM_Book_Tool::isGroupIdValid($groupId))
            return $this->error("groupId must be an integer");

        if (!$this->isUserRegistered())
            return $this->error;
        else
            $user = $this->user;

        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        if (!$groupContentGroupDAO->isDescendant($groupId, $user->get(OpenM_Book_UserDAO::PERSONAL_GROUPS)))
            return $this->error(self::RETURN_ERROR_MESSAGE_NOT_YOUR_PERSONAL_GROUP_VALUE);

        $groupDAO = new OpenM_Book_GroupDAO();
        $groupDAO->delete($groupId);
        return $this->ok();
    }

    public function removeGroupFromGroup($groupId, $groupParentId) {
        if (!OpenM_Book_Tool::isGroupIdValid($groupId))
            return $this->error("groupId must be an integer");
        if (!OpenM_Book_Tool::isGroupIdValid($groupParentId))
            return $this->error("groupParentId must be an integer");
        if ($groupId == $groupParentId)
            return $this->error("groupParentId must be different with groupId");

        if (!$this->isUserRegistered())
            return $this->error;
        else
            $user = $this->user;

        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        if (!$groupContentGroupDAO->isDescendant($groupParentId, $user->get(OpenM_Book_UserDAO::PERSONAL_GROUPS)))
            return $this->error(self::RETURN_ERROR_MESSAGE_NOT_YOUR_PERSONAL_GROUP_VALUE);
        $groupContentGroupDAO->delete($groupParentId, $groupId);

        return $this->ok();
    }

    public function removeUserFromGroup($userId, $groupId) {
        if (!OpenM_Book_Tool::isGroupIdValid($groupId))
            return $this->error("groupId must be an integer");
        if (!OpenM_Book_Tool::isUserIdValid($userId))
            return $this->error("userId must be an integer");

        if (!$this->isUserRegistered())
            return $this->error;

        $userDAO = new OpenM_Book_UserDAO();
        if ($userDAO->getFromId($userId) == null)
            return $this->error(self::RETURN_ERROR_MESSAGE_USER_NOT_FOUND_VALUE);

        $groupContentUser = new OpenM_Book_Group_Content_UserDAO();
        $groupContentUser->delete($groupId, $userId);

        return $this->ok();
    }

    public function renameGroup($groupId, $groupName) {
        if (!OpenM_Book_Tool::isGroupIdValid($groupId))
            return $this->error("groupId must be in a valid format (" . self::GROUP_ID_PARAMETER_PATERN . ")");
        if (!$this->isGroupNameValid($groupName))
            return $this->error("groupName must be valid");

        if (!$this->isUserRegistered())
            return $this->error;
        else
            $user = $this->user;

        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        if (!$groupContentGroupDAO->isDescendant($groupId, $user->get(OpenM_Book_UserDAO::PERSONAL_GROUPS)))
            return $this->error(self::RETURN_ERROR_MESSAGE_NOT_YOUR_PERSONAL_GROUP_VALUE);

        $groupDAO = new OpenM_Book_GroupDAO();
        $group = $groupDAO->get($groupId);
        if ($group == null)
            return $this->error(self::RETURN_ERROR_MESSAGE_GROUP_NOT_FOUND_VALUE);
        $searchDAO = new OpenM_Book_SearchDAO();
        $searchDAO->unIndex($group->get(OpenM_Book_GroupDAO::NAME), $group->get(OpenM_Book_GroupDAO::ID), OpenM_Book_SearchDAO::TYPE_PERSONAL_GROUP, $user->get(OpenM_Book_UserDAO::ID));
        $groupDAO->update($groupId, $groupName);
        $searchDAO->index($groupName, $group->get(OpenM_Book_GroupDAO::ID), OpenM_Book_SearchDAO::TYPE_PERSONAL_GROUP, $user->get(OpenM_Book_UserDAO::ID));
        return $this->ok();
    }

    public function search($terms, $maxNumberResult = null, $userOnly = null) {
        if (!String::isString($terms))
            return $this->error("terms must be a string");
        if (!String::isStringOrNull($maxNumberResult) && !is_numeric($maxNumberResult))
            return $this->error("maxNumberResult must be a numeric");
        if ($userOnly == null)
            $userOnly = false;
        if (!is_bool($userOnly) && !String::isString($userOnly) && $userOnly != self::FALSE_PARAMETER_VALUE && $userOnly != self::TRUE_PARAMETER_VALUE)
            return $this->error("userOnly must be a boolean");
        if (!is_bool($userOnly)) {
            if ($userOnly == self::TRUE_PARAMETER_VALUE)
                $userOnly = true;
            else
                $userOnly = false;
        }

        if ($maxNumberResult instanceof String)
            $maxNumberResult = $maxNumberResult->toInt();
        else if (is_string($maxNumberResult))
            $maxNumberResult = intval($maxNumberResult);

        if (!$this->isUserRegistered())
            return $this->error;

        $searchDAO = new OpenM_Book_SearchDAO();
        $result = $searchDAO->search($terms, $maxNumberResult, !$userOnly, !$userOnly);
        $e = $result->enum();
        $resultList = new HashtableString();
        while ($e->hasNext()) {
            $r = $e->next();
            $row = new HashtableString();
            $type = ($r->get(OpenM_Book_SearchDAO::TYPE)->toInt() == OpenM_Book_SearchDAO::TYPE_USER ? self::RETURN_RESULT_TYPE_USER_VALUE : self::RETURN_RESULT_TYPE_GROUP_VALUE);
            $row->put(self::RETURN_RESULT_ID_PARAMETER, $r->get(OpenM_Book_SearchDAO::ID))
                    ->put(self::RETURN_RESULT_NAME_PARAMETER, $r->get(OpenM_Book_SearchDAO::STRING))
                    ->put(self::RETURN_RESULT_TYPE_PARAMETER, $type);
            $resultList->put($type . $r->get(OpenM_Book_SearchDAO::ID), $row);
        }
        return $this->ok()->put(self::RETURN_RESULT_LIST_PARAMETER, $resultList);
    }

    public function getGroupsFromGroup($groupId) {
        if (!OpenM_Book_Tool::isGroupIdValid($groupId))
            return $this->error("groupId must be an integer");

        if (!$this->isUserRegistered())
            return $this->error;
        else
            $user = $this->user;

        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        if (!$groupContentGroupDAO->isDescendant($groupId, $user->get(OpenM_Book_UserDAO::PERSONAL_GROUPS)))
            return $this->error(self::RETURN_ERROR_MESSAGE_NOT_YOUR_PERSONAL_GROUP_VALUE);

        $groups = $groupContentGroupDAO->getChilds($groupId);
        if ($groups == null) {
            $list = new HashtableString();
            return $this->ok()->put(self::RETURN_GROUP_LIST_PARAMETER, $list);
        }
        return $this->ok()->put(self::RETURN_GROUP_LIST_PARAMETER, $this->getGroups($groups));
    }

    public function getUsersFromGroup($groupId) {
        if (!OpenM_Book_Tool::isGroupIdValid($groupId))
            return $this->error("groupId must be an integer");

        if (!$this->isUserRegistered())
            return $this->error;
        else
            $user = $this->user;

        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        if (!$groupContentGroupDAO->isDescendant($groupId, $user->get(OpenM_Book_UserDAO::PERSONAL_GROUPS)))
            return $this->error(self::RETURN_ERROR_MESSAGE_NOT_YOUR_PERSONAL_GROUP_VALUE);

        $groupContentUserDAO = new OpenM_Book_Group_Content_UserDAO();
        $users = $groupContentUserDAO->getUsersFromGroup($groupId);
        if ($users == null) {
            $list = new HashtableString();
            return $this->ok()->put(self::RETURN_USER_LIST_PARAMETER, $list);
        }
        return $this->ok()->put(self::RETURN_USER_LIST_PARAMETER, $this->getUsers($users));
    }

    private function isGroupNameValid($name) {
        return true;
    }

}

?>