<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gaël SAUNIER
 */
class OpenM_Book_AdminDAO extends OpenM_Book_DAO {

    const OpenM_BOOK_ADMIN_Table_Name = "OpenM_BOOK_ADMIN";
    const UID = "uid";
    const ADD_TIME = "add_time";

    /**
     * 
     * @param String $uid
     * @return HashtableString
     */
    public function create($uid) {
        $time = time();
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OpenM_BOOK_ADMIN_Table_Name), array(
                    self::UID => $uid,
                    self::ADD_TIME => intval($time)
                )));
        $return = new HashtableString();
        return $return->put(self::UID, $uid)
                        ->put(self::ADD_TIME, $time);
    }

    /**
     *  
     * @param String $uid
     * @return HashtableString
     */
    public function get($uid) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_ADMIN_Table_Name), array(
                            self::UID => $uid
                        )));
    }

    /**
     *  
     * @param String $uid
     * @return HashtableString
     */
    public function getCount() {
        $return = self::$db->request_fetch_array("SELECT count(*) as count FROM " . $this->getTABLE(self::OpenM_BOOK_ADMIN_Table_Name));
        return $return["count"];
    }

    /**
     * 
     * @return HashtableString
     */
    public function getAll() {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(OpenM_Book_UserDAO::OpenM_Book_User_Table_Name))
                        . " WHERE " . OpenM_Book_UserDAO::UID . " IN ("
                        . OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_ADMIN_Table_Name), array(), array(self::UID)) . ")"
                        , self::UID);
    }

    /**
     *  
     * @param String $uid
     * @return HashtableString
     */
    public function remove($uid) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OpenM_BOOK_ADMIN_Table_Name), array(
                    self::UID => $uid
                )));
    }

    public function containsOneAtLeast() {
        return (self::$db->request_fetch_HashtableString(self::$db->limit(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_ADMIN_Table_Name)), 1)) != null);
    }

}

?>