<?php

Import::php("OpenM-Book.api.OpenM_Groups");
Import::php("OpenM-Book.api.Impl.OpenM_BookCommonsImpl");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl  
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
        try {
            $groupContentUser->create($groupId, $userId, true);
        } catch (OpenM_DBException $e) {
            return $this->error(self::RETURN_ERROR_MESSAGE_USER_ALREADY_IN_GROUP_VALUE);
        }
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

    public function getMyCommunities() {
        if (!$this->isUserRegistered())
            return $this->error;

        $userId = $this->getManager()->getID();
        $groupContentUserDAO = new OpenM_Book_Group_Content_UserDAO();
        return $this->ok()->put(self::RETURN_GROUP_LIST_PARAMETER, $this->getGroups($groupContentUserDAO->getFromUID($userId, true, false)));
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

        $userId = $this->getManager()->getID();
        $groupContentUserDAO = new OpenM_Book_Group_Content_UserDAO();
        return $this->ok()->put(self::RETURN_GROUP_LIST_PARAMETER, $this->getGroups($groupContentUserDAO->getFromUID($userId, false, true)));
    }

    private function getGroups(HashtableString $groupList) {
        $e = $groupList->enum();
        $return = new HashtableString();
        while ($e->hasNext()) {
            $group = $e->next();
            $g = new HashtableString();
            $g->put(self::RETURN_GROUP_ID_PARAMETER, $group->get(OpenM_Book_GroupDAO::ID))
                    ->put(self::RETURN_GROUP_NAME_PARAMETER, $group->get(OpenM_Book_GroupDAO::NAME))
                    ->put(self::RETURN_GROUP_TYPE_PARAMETER, $group->get(OpenM_Book_GroupDAO::TYPE));
            $return->put($group->get(OpenM_Book_GroupDAO::ID), $g);
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

        try {
            $groupContentGroupDAO->delete($groupParentId, $groupId);
        } catch (OpenM_DBException $e) {
            return $this->error(self::RETURN_ERROR_MESSAGE_GROUP_NOT_FOUND_IN_GROUP_VALUE);
        }
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
        try {
            $groupContentUser->delete($groupId, $userId);
        } catch (OpenM_DBException $e) {
            return $this->error(self::RETURN_ERROR_MESSAGE_USER_NOT_FOUND_IN_GROUP_VALUE);
        }
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