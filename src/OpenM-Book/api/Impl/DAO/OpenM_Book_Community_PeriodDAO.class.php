<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER
 */
class OpenM_Book_Community_PeriodDAO extends OpenM_Book_DAO {
    //nom de la table

    const OPENM_BOOK_COMMUNITY_PERIOD_TABLE_NAME = "OpenM_BOOK_COMMUNITY_PERIOD";
    const SEQUENCE_FILE_PATH = "OpenM_Book.DAO.sequence.period.file.path";

    //nom des champs
    const ID = "period_id";
    const GROUP_ID = "group_id";
    const USER_ID = "user_id";
    const START = "start";
    const END = "end";

    /**
     * 
     * @return HashtableString
     * @throws OpenM_DBException
     */
    public function create($groupId, $userId, $start, $end) {
        $id = $this->sequence->next();
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OPENM_BOOK_COMMUNITY_PERIOD_TABLE_NAME), array(
                    self::ID => $id,
                    self::GROUP_ID => intval("$groupId"),
                    self::USER_ID => intval("$userId"),
                    self::START => intval("$start"),
                    self::END => intval("$end")
                )));

        $return = new HashtableString();
        return $return->put(self::USER_ID, $userId)
                        ->put(self::GROUP_ID, $groupId)
                        ->put(self::START, $start)
                        ->put(self::END, $end)
                        ->put(self::ID, $id);
    }

    public function delete($periodId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_PERIOD_TABLE_NAME), array(
                    self::ID => intval("$periodId")
                )));
    }

    public function deleteFromCommunity($groupId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_PERIOD_TABLE_NAME), array(
                    self::GROUP_ID => intval("$groupId")
                )));
    }

    public function get($groupId, $userId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_PERIOD_TABLE_NAME), array(
                            self::GROUP_ID => intval("$groupId"),
                            self::USER_ID => intval("$userId")
                        )));
    }

    public function getSequencePropertyName() {
        return self::SEQUENCE_FILE_PATH;
    }

}

?>