<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * Description of OpenM_Book_User_PropertyDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author nico
 */
class OpenM_Book_User_PropertyDAO extends OpenM_Book_DAO {

    const OPENM_BOOK_USER_PROPERTY_TABLE_NAME = "OpenM_BOOK_USER_PROPERTY";

    //champs bdd
    const ID = "property_id";
    const NAME = "name";

    //propriétés présente 
    const PROPERTY_EMAIL = "email";
    const PROPERTY_PREFERED_NAME = "nom long";

    public function create($propertyName) {
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_TABLE_NAME), array(
                    self::NAME => $propertyName
                )));
        return $this->getByName($propertyName);
    }

    //escape OK
    public function getByName($propertyName) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_TABLE_NAME), array(
                            self::NAME => self::$db->escape($propertyName)
                        )));
    }

    public function getById($propertyId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_TABLE_NAME), array(
                            self::ID => intval($propertyId)
                        )));
    }

    public function getAll() {
        return self::$db->request_HashtableString(
                        OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_TABLE_NAME)), self::ID);
    }

    public function update($propertyId, $name) {
        self::$db->request(OpenM_DB::update($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_TABLE_NAME), array(
                    self::NAME => $name
                        ), array(
                    self::ID => $propertyId
                )));
        return TRUE;
    }

}

?>
