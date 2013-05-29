<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * Description of OpenM_Book_User_Property_Value
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Nicolas ROUZEAUD & Gael SAUNIER
 */
class OpenM_Book_User_Property_ValueDAO extends OpenM_Book_DAO {

    const SEQUENCE_FILE_PATH = "OpenM_Book.DAO.sequence.value.file.path";
    const OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME = "OpenM_BOOK_USER_PROPERTY_VALUE";
    const ID = "value_id";
    const PROPERTY_ID = "property_id";
    const USER_ID = "user_id";
    const VALUE = "value";

    /**
     * 
     * @param int $propertyId
     * @param int $userId
     * @param string $value
     */
 /*   public function create($propertyId, $userId, $value) {
        $valueId = $this->sequence->next();
        self::$db->insert($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
            self::ID => intval($valueId),
            self::PROPERTY_ID => intval($propertyId),
            self::USER_ID => intval($userId),
            self::VALUE => $value
        ));
        $return = new HashtableString();
        return $return->put(self::ID, $valueId)
                        ->put(self::PROPERTY_ID, $propertyId)
                        ->put(self::USER_ID, $userId)
                        ->put(self::VALUE, $value);
    }
*/
    //escape OK
  /*  public function createByPropertyName($userUID, $propertyName, $propertyValue) {
        $newid = $this->sequence->next();
        $sql = "INSERT INTO " . $this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME)
                . "(" . self::ID . "," . self::PROPERTY_ID . "," . self::USER_ID . "," . self::VALUE . ")"
                . "VALUES ("
                . $newid
                . ",(SELECT " . OpenM_Book_User_PropertyDAO::ID . " FROM " . $this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME)
                . " WHERE " . OpenM_Book_User_PropertyDAO::NAME . "='" . self::$db->escape($propertyName) . "' )"
                . ",(SELECT " . OpenM_Book_UserDAO::ID . " FROM " . $this->getTABLE(OpenM_Book_UserDAO::OpenM_Book_User_Table_Name)
                . " WHERE " . OpenM_Book_UserDAO::UID . "='" . self::$db->escape($userUID) . "')"
                . ",'" . self::$db->escape($propertyValue) . "')";
        self::$db->request($sql);

        $retour = new HashtableString();
        return $retour->put(self::ID, $newid);
    }*/

    
    public function create($propertyId, $propertyValue, $userID ){
        $newid = $this->sequence->next();
        $sql = "INSERT INTO ".$this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME)." (".self::ID.",".self::PROPERTY_ID.",".self::USER_ID.",".self::VALUE.") VALUES ("
                ."$newid,"
                ."(SELECT ".OpenM_Book_User_PropertyDAO::ID." FROM ".$this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME)." WHERE  ".OpenM_Book_User_PropertyDAO::ID."=$propertyId  ),"
                ."$userID,"
                ."'".self::$db->escape($propertyValue)."')";
       
        try{
          self::$db->request($sql);
        }  catch (OpenM_DBException $e){
            $this->sequence->before();
            return null;
        }
        $retour = new HashtableString();
        return $retour->put(self::ID, $newid);  
    }
    

    public function update($valueID, $userId, $value) {
        $sql = OpenM_DB::update($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(self::VALUE=>$value), array(self::ID=>$valueID,self::USER_ID=>$userId));
        self::$db->request($sql);
        return TRUE;
    }
    
    public function delete($valueID,$userID){
        $sql = OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
            self::ID=>$valueID,
            self::USER_ID=>$userID
        ));
        if (self::$db->request($sql))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * récupére propertyId, valueId, value, userId à partir d'une valueId
     * @param int $userID
     * @param int $propertyValueId
     * @return HashtableString
     */
    public function get($userID, $propertyValueId ){
        return self::$db->request_ArrayList(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), 
                array(
                    self::USER_ID => intval($userID),
                    self::ID => intval($propertyValueId)
        )));
    }
    
    
    /**
     * retourne les propertiétes de l'utilisateur, par son id
     * peux restreindre les propriétes avec le tableau propertiesList
     * @param String $userId
     * @param array $propertiesIdList
     * @return ArrayList
     */
    public function getProperties($userId, $propertiesIdList = array()) {
        $str_propertiesId = null;
        if (is_array($propertiesIdList) && count($propertiesIdList) != 0) {
            $str_propertiesId = "".OpenM_Book_User_PropertyDAO::ID." IN (";
            foreach ($propertiesIdList as $value) {
                $str_propertiesId .= "$value,";
            }
            $str_propertiesId = substr($str_propertiesId, 0, -1);
            $str_propertiesId .= ")";
        }
        $sql = "SELECT * FROM ("
                . "SELECT  p.".OpenM_Book_User_PropertyDAO::ID." ," . OpenM_Book_User_PropertyDAO::NAME . " , " . self::ID . " ," . self::VALUE
                . " FROM " . $this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME) . " as p, " . $this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME) . " as v "
                . "WHERE p." . OpenM_Book_User_PropertyDAO::ID . "=v." . self::PROPERTY_ID
                . " AND " . self::USER_ID . "=$userId ";
        if ($str_propertiesId) {
            $sql .= " AND p." . $str_propertiesId;
        }
        $sql.=" UNION "
                . "SELECT ".OpenM_Book_User_PropertyDAO::ID."," . OpenM_Book_User_PropertyDAO::NAME . ", '', '' "
                . " FROM " . $this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME)
                . " WHERE " . OpenM_Book_User_PropertyDAO::ID . " NOT IN ( SELECT " . self::PROPERTY_ID . " FROM " . $this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME) . " WHERE " . self::USER_ID . "=" . $userId . ")";
        if ($str_propertiesId) {
            $sql .= " AND " . $str_propertiesId;
        }
        $sql.=") t GROUP BY t." . OpenM_Book_User_PropertyDAO::ID . ", " . OpenM_Book_User_Property_ValueDAO::ID
                . " ORDER BY t." . OpenM_Book_User_PropertyDAO::NAME;


        return self::$db->request_ArrayList($sql);
    }

    public function getFromUser($userIdTarget, $userIdCalling, $propertiesIdArray = null) {    
        if ($propertiesIdArray != null || count($propertiesIdArray)!=0){
            $str_propertiesId = " AND p.".OpenM_Book_User_PropertyDAO::ID." IN (";
            foreach ($propertiesIdArray as $value) {
                $str_propertiesId .= "$value ,";
            }
            $str_propertiesId = substr($str_propertiesId, 0, -1);
            $str_propertiesId .= ")";
        }else
            $str_propertiesId = "";
        
        
        $sql = "SELECT ".self::ID.", ".self::VALUE.", p.".OpenM_Book_User_PropertyDAO::ID.", ".OpenM_Book_User_PropertyDAO::NAME." " 
               ."FROM ".$this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME)." v, ".$this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME)." p "
               ."WHERE ".self::USER_ID." = $userIdTarget AND ".self::ID." IN ("
                 ."SELECT ".OpenM_Book_User_Property_Value_VisibilityDAO::VALUE_ID." "
                 ."FROM ".$this->getTABLE(OpenM_Book_User_Property_Value_VisibilityDAO::OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY_TABLE_NAME)." "
                 ."WHERE ".OpenM_Book_User_Property_Value_VisibilityDAO::GROUP_VISIBILITY_ID." IN ( "
                   ."SELECT ". OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID." "
                   ."FROM ".$this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME)." "
                   ."WHERE ".OpenM_Book_Group_Content_GroupDAO::GROUP_ID." IN ( "
                     ."SELECT ".OpenM_Book_Group_Content_UserDAO::GROUP_ID." "
                     ."FROM ".$this->getTABLE(OpenM_Book_Group_Content_UserDAO::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME)." "
                     ."WHERE ".OpenM_Book_Group_Content_UserDAO::USER_ID." = $userIdCalling "
               .")"
               ." UNION "
               ."SELECT ".OpenM_Book_Group_Content_UserDAO::GROUP_ID." "
                     ."FROM ".$this->getTABLE(OpenM_Book_Group_Content_UserDAO::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME)." "
                     ."WHERE ".OpenM_Book_Group_Content_UserDAO::USER_ID." = $userIdCalling " 
               .")) "
               ."AND v.".self::PROPERTY_ID." = p.".OpenM_Book_User_PropertyDAO::ID." "
               ."$str_propertiesId"
               ."Order by p.".OpenM_Book_User_PropertyDAO::ID;
       return self::$db->request_HashtableString($sql, self::ID);
    }

    public function getSequencePropertyName() {
        return self::SEQUENCE_FILE_PATH;
    }

}

?>
