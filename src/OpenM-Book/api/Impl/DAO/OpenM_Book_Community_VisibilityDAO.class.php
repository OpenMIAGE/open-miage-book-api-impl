<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER
 */
class OpenM_Book_Community_VisibilityDAO extends OpenM_Book_DAO {
    //nom de la table

    const OPENM_BOOK_COMMUNITY_VISIBILITY_TABLE_NAME = "OpenM_BOOK_COMMUNITY_VISIBILITY";

    //nom des champs
    const USER_ID = "user_id";
    const COMMUNITY_ID = "community_id";
    const VISIBILITY_ID = "visibility_id";

    public function create($userId, $communityId, $communityVisibilityId) {
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OPENM_BOOK_COMMUNITY_VISIBILITY_TABLE_NAME), array(
                    self::USER_ID => intval($userId),
                    self::COMMUNITY_ID => intval($communityId),
                    self::VISIBILITY_ID => intval($communityVisibilityId)
                )));

        $return = new HashtableString();
        return $return->put(self::COMMUNITY_ID, $communityId)
                        ->put(self::USER_ID, $userId)
                        ->put(self::VISIBILITY_ID, $communityVisibilityId);
    }

    public function delete($userId, $communityId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_VISIBILITY_TABLE_NAME), array(
                    self::USER_ID => intval($userId),
                    self::COMMUNITY_ID => intval($communityId)
                )));
    }

    public function deleteFromUser($userId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_VISIBILITY_TABLE_NAME), array(
                    self::USER_ID => intval($userId)
                )));
    }

    public function deleteFromGroup($groupId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_VISIBILITY_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval($groupId)
                )));
    }

    public function get($userId, $communityId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_VISIBILITY_TABLE_NAME), array(
                            self::USER_ID => intval($userId),
                            self::COMMUNITY_ID => intval($communityId)
                        )));
    }
}

?>