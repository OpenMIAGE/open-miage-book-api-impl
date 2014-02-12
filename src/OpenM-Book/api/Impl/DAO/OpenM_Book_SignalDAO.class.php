<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gaël SAUNIER
 */
class OpenM_Book_SignalDAO extends OpenM_Book_DAO {

    const OpenM_BOOK_SIGNAL_Table_Name = "OpenM_BOOK_SIGNAL";
    const USER_ID = "user_id";
    const TYPE = "type";
    const URI = "uri";
    const MESSAGE = "message";
    const TIME = "time";

    /**
     *
     * @return HashtableString
     */
    public function create($userId, $uri, $message, $type) {
        $time = time();
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OpenM_BOOK_SIGNAL_Table_Name), array(
                    self::TYPE => $type,
                    self::USER_ID => intval("$userId"),
                    self::URI => $uri,
                    self::MESSAGE => self::$db->escape($message),
                    self::TIME => $time
                )));
        $return = new HashtableString();
        return $return->put(self::USER_ID, $userId)
                        ->put(self::TYPE, $type)
                        ->put(self::URI, $uri)
                        ->put(self::MESSAGE, $message)
                        ->put(self::TIME, $time);
    }

    /**
     * @return HashtableString
     * @todo 
     */
    public function getFromTypeAndGroups($type, $groups) {
        
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_SIGNAL_Table_Name), array(
                            
                        )));
    }

}

?>
