<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gaël SAUNIER
 */
class OpenM_Book_Signal_GroupDAO extends OpenM_Book_DAO {

    const OpenM_BOOK_SIGNAL_GROUP_Table_Name = "OpenM_BOOK_SIGNAL_GROUP";
    const USER_ID = "group_id";
    const SIGNALED_BY = "signaled_by";
    const MESSAGE = "message";
    const TIME = "time";

    /**
     *
     * @return HashtableString
     */
    public function create($groupId, $signaledBy, $message) {
        $time = time();
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OpenM_BOOK_SIGNAL_GROUP_Table_Name), array(
                    self::SIGNALED_BY => intval($signaledBy),
                    self::USER_ID => intval($groupId),
                    self::MESSAGE => self::$db->escape($message),
                    self::TIME => intval($time)
                )));
        $return = new HashtableString();
        return $return->put(self::USER_ID, $groupId)
                        ->put(self::SIGNALED_BY, $signaledBy)
                        ->put(self::MESSAGE, $message)
                        ->put(self::TIME, $time);
    }

    /**
     * @return HashtableString
     * @todo 
     */
    public function getFromTypeAndGroups($type, $groups) {

        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_SIGNAL_GROUP_Table_Name), array(
                            self::USER_ID => $uid
                        )));
    }

}

?>
