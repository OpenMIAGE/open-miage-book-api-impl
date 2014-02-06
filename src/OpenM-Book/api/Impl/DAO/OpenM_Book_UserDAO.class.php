<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * 
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Nicolas Rouzeaud & Gaël SAUNIER
 */
class OpenM_Book_UserDAO extends OpenM_Book_DAO {

    const OpenM_Book_User_Table_Name = "OpenM_BOOK_USER";
    const ID = "user_id";
    const CREATION_TIME = "creation_time";
    const UID = "uid";
    const UPDATE_TIME = "update_time";
    const PERSONAL_GROUPS = "personal_groups";
    const FIRST_NAME = "first_name";
    const LAST_NAME = "last_name";
    const PHOTO = "photo";
    const BIRTHDAY = "birthday";
    const BIRTHDAY_YEAR_DISPLAYED = "birthday_year_displayed";
    const BIRTHDAY_VISIBILITY = "birthday_visibility";
    const ACTIVATED = "activated";
    const MAIL = "mail";
    const ACTIVE = 1;

    /**
     * 
     * @param String $userUID
     * @param String $firstName
     * @param String $lastName
     * @param String $personal_groupId
     * @return HashtableString
     */
    public function create($userUID, $firstName, $lastName, $birthday, $mail, $personal_groupId, $birthday_visibility_groupId, $activated = true) {
        $time = time();
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OpenM_Book_User_Table_Name), array(
                    self::UID => $userUID,
                    self::CREATION_TIME => $time,
                    self::UPDATE_TIME => $time,
                    self::PERSONAL_GROUPS => intval("$personal_groupId"),
                    self::FIRST_NAME => $firstName,
                    self::LAST_NAME => $lastName,
                    self::BIRTHDAY => intval("$birthday"),
                    self::BIRTHDAY_VISIBILITY => intval("$birthday_visibility_groupId"),
                    self::MAIL => $mail,
                    self::ACTIVATED => ($activated) ? 1 : 0
        )));
        return $this->getFromUID($userUID);
    }

    /**
     * retourne l'user par son userId (identifiant SSO)
     * @param String $userUID
     * @return HashtableString
     */
    public function getFromUID($userUID) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_Book_User_Table_Name), array(
                            self::UID => $userUID
        )));
    }

    /**
     * retourne l'user par son numéreau incrémentiel 
     * @param String $userNum
     * @return HashtableString
     */
    public function get($userId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_Book_User_Table_Name), array(
                            self::ID => $userId
        )));
    }

    /**
     * met à jour le champ update_time de l'user
     * @param String $userId
     * @param int $time
     * 
     */
    public function updateTime($userId, $time = NULL) {
        if (is_null($time))
            $time = time();
        self::$db->request(OpenM_DB::update($this->getTABLE(self::OpenM_Book_User_Table_Name), array(
                    self::UPDATE_TIME => $time
                        ), array(
                    self::ID => $userId
        )));
        return TRUE;
    }

    public function update($userId, $key, $value) {
        $array = array();
        $array[self::UPDATE_TIME] = time();
        if (!is_numeric($value))
            $value = self::$db->escape($value);
        $array["$key"] = $value;
        self::$db->request(OpenM_DB::update($this->getTABLE(self::OpenM_Book_User_Table_Name), $array, array(
                    self::ID => $userId
        )));
    }

}

?>
