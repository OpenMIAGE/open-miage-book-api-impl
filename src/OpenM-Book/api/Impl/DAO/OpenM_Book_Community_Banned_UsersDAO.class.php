<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER
 */
class OpenM_Book_Community_Banned_UsersDAO extends OpenM_Book_DAO {

    const OPENM_BOOK_COMMUNITY_USER_BAN_TABLE_NAME = "OpenM_BOOK_COMMUNITY_BANNED_USERS";
    const COMMUNITY_ID = "community_id";
    const BANNED_GROUP_ID = "banned_group_id";

    /**
     * 
     * @param int $communityId
     * @param int $bannedGroupId
     * @return boolean
     * @throws OpenM_DBException
     */
    public function create($communityId, $bannedGroupId) {
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OPENM_BOOK_COMMUNITY_USER_BAN_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval("$communityId"),
                    self::BANNED_GROUP_ID => intval("$bannedGroupId")
                )));

        $return = new HashtableString();
        return $return->put(self::COMMUNITY_ID, $communityId)
                        ->put(self::BANNED_GROUP_ID, $bannedGroupId);
    }

    public function delete($communityID, $bannedGroupId) {
        $array = array(
            self::COMMUNITY_ID => intval("$communityID"),
        );
        if ($bannedGroupId != null)
            $array[self::BANNED_GROUP_ID] = $bannedGroupId;

        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_USER_BAN_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval("$communityID"),
                    self::BANNED_GROUP_ID =>  intval("$bannedGroupId")
                ))
        );
    }

    public function deleteFromCommunity($communityId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_USER_BAN_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval("$communityId")
                )));
    }

    public function getFromCommunity($communityId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_USER_BAN_TABLE_NAME), array(
                            self::COMMUNITY_ID => intval("$communityId")
                        )));
    }

    public function getFromBannedGroup($bannedGroupId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_USER_BAN_TABLE_NAME), array(
                            self::BANNED_GROUP_ID => intval("$bannedGroupId")
                        )));
    }

    public function isUserBanned($userId, $communityId) {
        $scope = OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_UserDAO::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                    OpenM_Book_Group_Content_UserDAO::USER_ID => intval("$userId")
                        ), array(
                    OpenM_Book_Group_Content_UserDAO::GROUP_ID
                ));

        $groupIds = OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME))
                . " WHERE " . OpenM_Book_Group_Content_GroupDAO::GROUP_ID
                . " IN (" . $scope . ")";

        $bannedGroupId = "(" . OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_USER_BAN_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval("$communityId")
                        ), array(
                    self::BANNED_GROUP_ID
                )) . ")";

        $result = self::$db->request(self::$db->limit("SELECT * FROM"
                        . " (" . $groupIds . ") g "
                        . "WHERE g." . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID . "=$bannedGroupId"
                        . " OR "
                        . "g." . OpenM_Book_Group_Content_GroupDAO::GROUP_ID . "=$bannedGroupId"
                        , 1));

        if (self::$db->fetch_array($result) !== false)
            return true;
        else
            return false;
    }

}

?>