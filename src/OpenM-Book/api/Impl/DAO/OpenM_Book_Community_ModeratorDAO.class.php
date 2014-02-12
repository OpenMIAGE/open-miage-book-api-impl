<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER
 */
class OpenM_Book_Community_ModeratorDAO extends OpenM_Book_DAO {
    //nom de la table

    const OPENM_BOOK_COMMUNITY_MODERATOR_TABLE_NAME = "OpenM_BOOK_COMMUNITY_MODERATOR";

    //nom des champs
    const COMMUNITY_ID = "group_id";
    const MODERATOR_ID = "group_id_moderator";

    /**
     * 
     * @param type $communityId
     * @param type $groupModeratorId
     * @return boolean
     * @throws OpenM_DBException
     */
    public function create($communityId, $groupModeratorId) {
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OPENM_BOOK_COMMUNITY_MODERATOR_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval("$communityId"),
                    self::MODERATOR_ID => intval("$groupModeratorId")
                )));

        $return = new HashtableString();
        return $return->put(self::MODERATOR_ID, $groupModeratorId)
                        ->put(self::COMMUNITY_ID, $communityId);
    }

    public function delete($communityId, $groupModeratorId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_MODERATOR_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval("$communityId"),
                    self::MODERATOR_ID => intval("$groupModeratorId")
                )));
    }

    public function deleteFromCommunity($communityId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_MODERATOR_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval("$communityId")
                )));
    }

    public function deleteFromModerator($groupModeratorId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_MODERATOR_TABLE_NAME), array(
                    self::MODERATOR_ID => intval("$groupModeratorId")
                )));
    }

    public function get($communityId, $groupModeratorId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_MODERATOR_TABLE_NAME), array(
                            self::COMMUNITY_ID => intval("$communityId"),
                            self::MODERATOR_ID => intval("$groupModeratorId")
                        )));
    }

    public function getFromCommunity($communityId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_MODERATOR_TABLE_NAME), array(
                            self::COMMUNITY_ID => intval("$communityId")
                        )));
    }

    public function getFromModerator($groupModeratorId) {
        self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_MODERATOR_TABLE_NAME), array(
                    self::MODERATOR_ID => intval("$groupModeratorId")
                )));
    }

    public function isUserModerator($userId, $communityId) {
        $scope = OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_UserDAO::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), array(
                    OpenM_Book_Group_Content_UserDAO::USER_ID => intval("$userId")
                        ), array(
                    OpenM_Book_Group_Content_UserDAO::GROUP_ID
                ));

        $groupIds = OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME))
                . " WHERE " . OpenM_Book_Group_Content_GroupDAO::GROUP_ID
                . " IN (" . $scope . ")";

        $moderatorId = "(" . OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_MODERATOR_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval("$communityId")
                        ), array(
                    self::MODERATOR_ID
                )) . ")";

        $result = self::$db->request(self::$db->limit("SELECT * FROM"
                        . " (" . $groupIds . ") g "
                        . "WHERE g." . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID . "=$moderatorId"
                        . " OR "
                        . "g." . OpenM_Book_Group_Content_GroupDAO::GROUP_ID . "=$moderatorId"
                        , 1));

        if (self::$db->fetch_array($result) !== false)
            return true;
        else
            return false;
    }

}

?>