<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * Description of OpenM_Book_Group_Content_UserDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER
 */
class OpenM_Book_Group_Content_UserDAO extends OpenM_Book_DAO {

    const OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME = "OpenM_BOOK_GROUP_CONTENT_USER";
    const GROUP_ID = "group_id";
    const USER_ID = "user_id";

    public function create($groupId, $userId) {
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                    self::GROUP_ID => intval($groupId),
                    self::USER_ID => intval($userId)
        )));

        $return = new HashtableString();
        return $return->put(self::GROUP_ID, $groupId)
                        ->put(self::USER_ID, $userId);
    }

    public function delete($groupId, $userId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                    self::USER_ID => intval($userId),
                    self::GROUP_ID => intval($groupId)
        )));
    }

    public function deleteFromGroup($groupId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                    self::GROUP_ID => intval($groupId)
        )));
    }

    public function deleteFromUser($userId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                    self::USER_ID => intval($userId)
        )));
    }

    public function get($groupId, $userId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                            self::USER_ID => intval($userId),
                            self::GROUP_ID => intval($groupId)
        )));
    }

    public function isUserInGroups($user_uid_or_id, $groupIdList, $andValidated = true, $groupOnly = false, $communityOnly = false) {
        if (is_int($user_uid_or_id))
            return $this->_isUserInGroups(null, $user_uid_or_id, $groupIdList, $andValidated, $groupOnly, $communityOnly);
        else
            return $this->_isUserInGroups($user_uid_or_id, null, $groupIdList, $andValidated, $groupOnly, $communityOnly);
    }

    private function _isUserInGroups($uid, $userId, $groupIdList, $andValidated = true, $groupOnly = false, $communityOnly = false) {
        if (sizeof($groupIdList) == 0)
            return false;

        $in = "";
        foreach ($groupIdList as $value)
            $in .= $value . ", ";

        $in = substr($in, 0, -2);

        if ($userId == null) {
            $userId = "(" . OpenM_DB::select($this->getTABLE(OpenM_Book_UserDAO::OpenM_Book_User_Table_Name), array(
                        OpenM_Book_UserDAO::UID => "$uid",
                        OpenM_Book_UserDAO::ACTIVATED => OpenM_Book_UserDAO::ACTIVE
                            ), array(
                        OpenM_Book_UserDAO::ID
                    ))
                    . ")";
        }

        $groupIds = OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                    self::USER_ID => intval($userId)
                        ), array(
                    self::GROUP_ID
        ));

        $communityIds = OpenM_DB::select($this->getTABLE(OpenM_Book_Community_Content_UserDAO::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), array(
                    OpenM_Book_Community_Content_UserDAO::USER_ID => intval($userId)
                        ), array(
                    OpenM_Book_Community_Content_UserDAO::COMMUNITY_ID
                        )
        );

        $communityIds .= (($andValidated) ? (" AND "
                        . OpenM_Book_Community_Content_UserDAO::IS_VALIDATED . "=" . OpenM_Book_Community_Content_UserDAO::VALIDATED) : "");

        if (!$groupOnly && !$communityOnly)
            $scope = $groupIds . " UNION " . $communityIds;
        else if ($groupOnly)
            $scope = $groupIds;
        else
            $scope = $communityIds;

        $groupId2s = OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME))
                . " WHERE " . OpenM_Book_Group_Content_GroupDAO::GROUP_ID
                . " IN (" . $scope . ")";

        $result = self::$db->request(self::$db->limit("SELECT * FROM"
                        . " (" . $groupId2s . ") g "
                        . "WHERE g." . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID . " IN ($in)"
                        . " OR "
                        . "g." . OpenM_Book_Group_Content_GroupDAO::GROUP_ID . " IN ($in)"
                        , 1));

        if (self::$db->fetch_array($result) !== false)
            return true;
        else
            return false;
    }

    public function getFromGroup($groupId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                            self::GROUP_ID => intval($groupId)
                        )), self::USER_ID);
    }

    public function getUsersFromGroup($groupId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(OpenM_Book_UserDAO::OpenM_Book_User_Table_Name))
                        . " WHERE " . OpenM_Book_UserDAO::ID . " IN (" .
                        OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                            self::GROUP_ID => intval($groupId)
                                ), array(self::USER_ID)) . ")"
                        . " AND " . OpenM_Book_UserDAO::ACTIVATED . "=" . OpenM_Book_UserDAO::ACTIVE, self::USER_ID
        );
    }

    public function getFromUser($userId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                            self::USER_ID => intval($userId)
                        )), self::GROUP_ID);
    }

    public function getFromUID($uid, $generic = true, $notGeneric = true) {
        if ($generic && $notGeneric)
            $request = "(" . $this->getCommunitiesFromUID($uid) . ") UNION (" . $this->getGroupsFromUID($uid) . ")";
        else if ($generic)
            $request = $this->getCommunitiesFromUID($uid);
        else if ($notGeneric)
            $request = $this->getGroupsFromUID($uid);
        else
            return null;

        return self::$db->request_HashtableString($request, OpenM_Book_GroupDAO::ID);
    }

    private function getCommunitiesFromUID($uid) {
        return OpenM_DB::select($this->getTABLE(OpenM_Book_GroupDAO::OpenM_Book_Group_TABLE_NAME))
                . " WHERE " . OpenM_Book_GroupDAO::ID . " IN ("
                . OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), null, array(self::GROUP_ID))
                . " WHERE " . self::USER_ID . " = ( "
                . OpenM_DB::select($this->getTABLE(OpenM_Book_UserDAO::OpenM_Book_User_Table_Name), array(
                    OpenM_Book_UserDAO::UID => "$uid",
                    OpenM_Book_UserDAO::ACTIVATED . "=" . OpenM_Book_UserDAO::ACTIVE
                        ), array(OpenM_Book_UserDAO::ID)
                )
                . " ) ) AND " . OpenM_Book_GroupDAO::TYPE . " = " . OpenM_Book_GroupDAO::TYPE_COMMUNITY;
    }

    private function getGroupsFromUID($uid) {
        return OpenM_DB::select($this->getTABLE(OpenM_Book_GroupDAO::OpenM_Book_Group_TABLE_NAME))
                . " WHERE " . OpenM_Book_GroupDAO::ID . " IN ("
                . OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME), null, array(OpenM_Book_Group_Content_GroupDAO::GROUP_ID))
                . " WHERE " . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID . " = ("
                . OpenM_DB::select($this->getTABLE(OpenM_Book_UserDAO::OpenM_Book_User_Table_Name), array(
                    OpenM_Book_UserDAO::UID => "$uid",
                    OpenM_Book_UserDAO::ACTIVATED . "=" . OpenM_Book_UserDAO::ACTIVE
                        ), array(OpenM_Book_UserDAO::PERSONAL_GROUPS)
                )
                . "))";
    }

}

?>