<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER
 */
class OpenM_Book_Community_Content_User_ValidationDAO extends OpenM_Book_DAO {

    const OPENM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION_TABLE_NAME = "OpenM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION";
    const GROUP_ID = "group_id";
    const USER_ID = "user_id";
    const VALIDATED_BY = "validated_by";
    const TIME = "time";
    const MESSAGE = "message";

    public function create($groupId, $userId, $validatedBy, $message) {
        $time = time();
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION_TABLE_NAME), array(
                    self::GROUP_ID => intval($groupId),
                    self::USER_ID => intval($userId),
                    self::VALIDATED_BY => intval($validatedBy),
                    self::MESSAGE => self::$db->escape($message),
                    self::TIME => intval($time)
                )));

        $return = new HashtableString();
        return $return->put(self::USER_ID, $userId)
                        ->put(self::TIME, $time)
                        ->put(self::VALIDATED_BY, $validatedBy)
                        ->put(self::MESSAGE, $message)
                        ->put(self::TIME, $time);
    }

    public function delete($groupId, $userId, $actionBy, $validation = true) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION_TABLE_NAME), array(
                    self::GROUP_ID => intval($groupId),
                    self::USER_ID => intval($userId),
                    self::VALIDATED_BY => intval($actionBy),
                    self::VALIDATION => (($validation) ? 1 : 0),
                )));
    }

    public function deleteFromUserAndGroup($groupId, $userId, $validation = true) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION_TABLE_NAME), array(
                    self::GROUP_ID => intval($groupId),
                    self::USER_ID => intval($userId),
                    self::VALIDATION => (($validation) ? 1 : 0),
                )));
    }

    public function getFromUserAndGroup($groupId, $userId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION_TABLE_NAME), array(
                            self::USER_ID => intval($userId),
                            self::GROUP_ID => intval($groupId)
                        )));
    }

    public function getFromGroup($groupId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION_TABLE_NAME), array(
                            self::GROUP_ID => intval($groupId)
                        )));
    }

    public function deleteFromCommunity($groupId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION_TABLE_NAME), array(
                    self::GROUP_ID => intval($groupId),
                )));
    }
}

?>