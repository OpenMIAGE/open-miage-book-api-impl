<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * Description of OpenM_Book_Value_Group
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author nico
 */
class OpenM_Book_User_Property_Value_VisibilityDAO extends OpenM_Book_DAO {

    const OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY_TABLE_NAME = "OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY";

    //les champs
    const GROUP_VISIBILITY_ID = "group_id";
    const VALUE_ID = "value_id";

    public function create($valueId, $groupVisibilityId) {
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY_TABLE_NAME), array(
                    self::GROUP_VISIBILITY_ID => intval($groupVisibilityId),
                    self::VALUE_ID => intval($valueId)
                )));
    }

    public function update($valueId, $groupVisibilityId) {
        self::$db->request(OpenM_DB::update($this->getTABLE(self::OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY_TABLE_NAME), array(
                    self::GROUP_VISIBILITY_ID => intval($groupVisibilityId)
                        ), array(
                    self::VALUE_ID => $valueId
                )));
    }

    public function delete($valueId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY_TABLE_NAME), array(
                    self::VALUE_ID => intval($valueId)
                )));
    }


    /**
     * 
     * @param type $userId
     * @param type $valueID
     * @return ArrayList
     */
    public function get($userId,$valueID){
       
        $sql = "SELECT gv." . self::VALUE_ID . ", gv." . self::GROUP_VISIBILITY_ID . ", g." . OpenM_Book_GroupDAO::NAME
                . " FROM " . $this->getTABLE(OpenM_Book_GroupDAO::OpenM_Book_Group_TABLE_NAME) . " g , " . $this->getTABLE(self::OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY_TABLE_NAME) . " gv," . $this->getTABLE(OpenM_Book_User_Property_ValueDAO::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME) . " v "
                . " WHERE gv." . self::VALUE_ID . "=v." . OpenM_Book_User_Property_ValueDAO::ID
                . " AND gv." . self::GROUP_VISIBILITY_ID . "=g." . OpenM_Book_GroupDAO::ID
                . " AND v." . OpenM_Book_User_Property_ValueDAO::USER_ID . "=" . $userId
                . " AND gv.".self::VALUE_ID."=$valueID"
                . " Order by gv." . self::VALUE_ID;
        return self::$db->request_ArrayList($sql);  
    }
    
}

?>
