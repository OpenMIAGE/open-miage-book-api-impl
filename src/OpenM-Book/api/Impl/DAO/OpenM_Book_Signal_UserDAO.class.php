<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gaël SAUNIER
 */
class OpenM_Book_Signal_UserDAO extends OpenM_Book_DAO {

    const OpenM_BOOK_SIGNAL_USER_Table_Name = "OpenM_BOOK_SIGNAL_USER";
    const OpenM_BOOK_SIGNAL_USER_IN_GROUP_Table_Name = "OpenM_BOOK_SIGNAL_USER_IN_GROUP";
    const USER_ID = "user_id";
    const SIGNALED_BY = "signaled_by";
    const GROUP_ID = "group_id";
    const MESSAGE = "message";
    const TIME = "time";

    /**
     *
     * @return HashtableString
     */
    public function create($userId, $signaledBy, $message, $groupId = null) {
        $time = time();
        $array = array(
            self::USER_ID => intval("$userId"),
            self::SIGNALED_BY => intval("$signaledBy"),
            self::MESSAGE => self::$db->escape($message),
            self::TIME => $time
        );
        if ($groupId != null)
            $array[self::GROUP_ID] = intval("$groupId");
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OpenM_BOOK_SIGNAL_USER_Table_Name), $array));
        $return = new HashtableString();
        return $return->put(self::USER_ID, $userId)
                        ->put(self::GROUP_ID, $groupId)
                        ->put(self::SIGNALED_BY, $signaledBy)
                        ->put(self::MESSAGE, $message)
                        ->put(self::TIME, $time);
    }

}

?>