<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * Description of OpenM_Book_Group_Content_UserDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER
 */
class OpenM_Book_Community_Content_UserDAO extends OpenM_Book_DAO {

    const OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME = "OpenM_BOOK_COMMUNITY_CONTENT_USER";
    const COMMUNITY_ID = "group_id";
    const USER_ID = "user_id";
    const IS_VALIDATED = "isValidated";
    const CREATION_TIME = "creation_time";
    const VALIDATION_TIME = "validation_time";
    const VALIDATED = 1;
    const NOT_VALIDATED = 0;
    const NB_ACCEPTED = "nb_accepted";

    public function create($communityId, $userId, $isValid = false) {
        $time = time();

        $array = array(
            self::COMMUNITY_ID => intval($communityId),
            self::USER_ID => intval($userId),
            self::IS_VALIDATED => (($isValid) ? 1 : 0),
            self::CREATION_TIME => $time
        );

        if ($isValid)
            $array[self::VALIDATION_TIME] = $time;

        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), $array));

        $return = new HashtableString();
        return $return->put(self::COMMUNITY_ID, $communityId)
                        ->put(self::USER_ID, $userId)
                        ->put(self::CREATION_TIME, $time)
                        ->put(self::IS_VALIDATED, $isValid)
                        ->put(self::VALIDATION_TIME, ($isValid) ? $time : null);
    }

    public function update($communityId, $userId, $isValid = false) {
        $time = time();

        $array = array(
            self::IS_VALIDATED => (($isValid) ? 1 : 0)
        );

        if ($isValid)
            $array[self::VALIDATION_TIME] = $time;

        self::$db->request(OpenM_DB::update($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), $array, array(
                    self::COMMUNITY_ID => $communityId,
                    self::USER_ID => $userId
        )));
    }

    public function delete($communityId, $userId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), array(
                    self::USER_ID => intval($userId),
                    self::COMMUNITY_ID => intval($communityId)
        )));
    }

    public function deleteFromCommunity($communityId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval($communityId)
        )));
    }

    public function deleteFromUser($userId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), array(
                    self::USER_ID => intval($userId)
        )));
    }

    public function get($groupId, $userId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), array(
                            self::USER_ID => intval($userId),
                            self::COMMUNITY_ID => intval($groupId)
        )));
    }

    public function countOfUsers($communityId, $valid = true) {
        $communities = OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(
                    OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID => intval($communityId)
                        ), array(
                    OpenM_Book_Group_Content_GroupDAO::GROUP_ID
        ));
        $count = self::$db->request_fetch_array("SELECT count(*) as count FROM "
                . $this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME)
                . " WHERE (" . self::COMMUNITY_ID . " IN ($communities) OR " . self::COMMUNITY_ID . "=$communityId)"
                . " AND " . self::IS_VALIDATED . "=" . (($valid) ? (self::VALIDATED) : (self::NOT_VALIDATED))
        );
        return intval($count["count"]);
    }

    public function getFromCommunity($groupId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), array(
                            self::COMMUNITY_ID => intval($groupId)
                        )), self::USER_ID);
    }

    public function getFromUser($userId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), array(
                            self::USER_ID => intval($userId)
                        )), self::COMMUNITY_ID);
    }

    public function getUsers($myId, $communityId, $start, $maxNbResult, $valid = true, $userId = null) {
        $communityId = intval($communityId);
        $communities = OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(
                    OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID => $communityId
                        ), array(
                    OpenM_Book_Group_Content_GroupDAO::GROUP_ID
        ));
        $usersIds = "SELECT cc.* " . (!$valid ? ", g." . OpenM_Book_GroupDAO::NAME : "") . " FROM "
                . $this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME) . " cc"
                . ((!$valid) ? (", " . $this->getTABLE(OpenM_Book_GroupDAO::OpenM_BOOK_GROUP_TABLE_NAME) . " g ") : "")
                . " WHERE (cc." . self::COMMUNITY_ID . " IN ($communities) OR cc." . self::COMMUNITY_ID . "=$communityId)"
                . " AND cc." . self::IS_VALIDATED . "=" . (($valid) ? (self::VALIDATED) : (self::NOT_VALIDATED))
                . ((!$valid) ? (" AND cc." . self::COMMUNITY_ID . "=g." . OpenM_Book_GroupDAO::ID) : "");
        $users_head = "SELECT u." . OpenM_Book_UserDAO::ID . ", u."
                . OpenM_Book_UserDAO::FIRST_NAME . ", u." . OpenM_Book_UserDAO::LAST_NAME
                . ", c." . self::COMMUNITY_ID . ((!$valid) ? (", c." . OpenM_Book_GroupDAO::NAME ) : "");
        $users = $users_head;
        if ($userId !== null)
            $users .= ", (SELECT count(*) "
                    . "FROM " . $this->getTABLE(OpenM_Book_Community_Content_User_ValidationDAO::OPENM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION_TABLE_NAME) . " t"
                    . " WHERE t." . OpenM_Book_Community_Content_User_ValidationDAO::USER_ID . "=u." . OpenM_Book_UserDAO::ID
                    . " AND t." . OpenM_Book_Community_Content_User_ValidationDAO::GROUP_ID . "=c." . self::COMMUNITY_ID
                    . " AND t." . OpenM_Book_Community_Content_User_ValidationDAO::VALIDATED_BY . "=$userId"
                    . ") as " . self::NB_ACCEPTED;
        
        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        
        $users.= " FROM " . $this->getTABLE(OpenM_Book_UserDAO::OpenM_Book_User_Table_Name) . " u, "
                . " ($usersIds) c, "
                . $this->getTABLE(OpenM_Book_Community_VisibilityDAO::OPENM_BOOK_COMMUNITY_VISIBILITY_TABLE_NAME) . " cv"
                . " WHERE u." . OpenM_Book_UserDAO::ID . "=c." . self::USER_ID
                . " AND u." . OpenM_Book_UserDAO::ACTIVATED . "=" . OpenM_Book_UserDAO::ACTIVE
                . " AND u." . OpenM_Book_UserDAO::ID . "=cv." . OpenM_Book_Community_VisibilityDAO::USER_ID
                . " AND c." . self::COMMUNITY_ID . "=cv." . OpenM_Book_Community_VisibilityDAO::COMMUNITY_ID
                . " AND (SELECT count(*) FROM "
                . $this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME)
                . " WHERE " . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID . "=cv." . OpenM_Book_Community_VisibilityDAO::VISIBILITY_ID
                . " AND (" . OpenM_Book_Group_Content_GroupDAO::GROUP_ID . " IN ("
                . $groupContentGroupDAO->inGroupsFromUserId($myId)
                . ")"
                . "))>0"
                . ($valid ? (" GROUP BY " . OpenM_Book_UserDAO::ID) : "");
        $orderBy = " ORDER BY u." . OpenM_Book_UserDAO::FIRST_NAME . ", u." . OpenM_Book_UserDAO::LAST_NAME;
        $users .= $orderBy;
        if (!$valid)
            $users = "($users) UNION "
                    . "(" . $users_head . (($userId !== null) ? (", 0 " . self::NB_ACCEPTED) : "")
                    . " FROM " . $this->getTABLE(OpenM_Book_UserDAO::OpenM_Book_User_Table_Name) . " u, "
                    . $this->getTABLE(OpenM_Book_GroupDAO::OpenM_BOOK_GROUP_TABLE_NAME) . " c, "
                    . $this->getTABLE(OpenM_Book_Community_Content_UserDAO::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME) . " cu"
                    . " WHERE u." . OpenM_Book_UserDAO::ID . "=$myId"
                    . " AND u." . OpenM_Book_UserDAO::ID . "=cu." . OpenM_Book_Community_Content_UserDAO::USER_ID
                    . " AND c." . OpenM_Book_GroupDAO::ID . "=cu." . OpenM_Book_Community_Content_UserDAO::COMMUNITY_ID
                    . " AND cu." . OpenM_Book_Community_Content_UserDAO::IS_VALIDATED . "=" . OpenM_Book_Community_Content_UserDAO::NOT_VALIDATED
                    . " AND (cu." . OpenM_Book_Community_Content_UserDAO::COMMUNITY_ID . " IN ($communities)"
                    . " OR cu." . OpenM_Book_Community_Content_UserDAO::COMMUNITY_ID . "=$communityId)"
                    . "$orderBy)";
        return self::$db->request_ArrayList(self::$db->limit($users, $maxNbResult, $start));
    }

}

?>