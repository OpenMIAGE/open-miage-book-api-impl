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
      } */


    public function create($propertyId, $propertyValue, $userID) {
        $newid = $this->sequence->next();
        $sql = "INSERT INTO " . $this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME) . " (" . self::ID . "," . self::PROPERTY_ID . "," . self::USER_ID . "," . self::VALUE . ") VALUES ("
                . "$newid,"
                . "(SELECT " . OpenM_Book_User_PropertyDAO::ID . " FROM " . $this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME) . " WHERE  " . OpenM_Book_User_PropertyDAO::ID . "=$propertyId  ),"
                . "$userID,"
                . "'" . self::$db->escape($propertyValue) . "')";

        try {
            self::$db->request($sql);
        } catch (OpenM_DBException $e) {
            $this->sequence->before();
            return null;
        }
        $retour = new HashtableString();
        return $retour->put(self::ID, $newid);
    }

    public function update($valueID, $userId, $value) {
        self::$db->request(OpenM_DB::update($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
                    self::VALUE => self::$db->escape($value)
                        ), array(
                    self::ID => $valueID,
                    self::USER_ID => $userId
                )));
        return TRUE;
    }

    public function delete($valueID, $userID) {
        $sql = OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
                    self::ID => $valueID,
                    self::USER_ID => $userID
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
    public function get($userID, $propertyValueId) {
        return self::$db->request_ArrayList(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
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
    public function getProperties($userId) {
        $sql = "SELECT * FROM (SELECT * FROM ("
                . "SELECT  p." . OpenM_Book_User_PropertyDAO::ID . " ," . OpenM_Book_User_PropertyDAO::NAME . " , " . self::ID . " ," . self::VALUE
                . " FROM " . $this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME) . " as p, "
                . $this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME) . " as v "
                . "WHERE p." . OpenM_Book_User_PropertyDAO::ID . "=v." . self::PROPERTY_ID
                . " AND " . self::USER_ID . "=$userId) as a"
                . " UNION "
                . "SELECT " . OpenM_Book_User_PropertyDAO::ID . "," . OpenM_Book_User_PropertyDAO::NAME . ", '', '' "
                . " FROM " . $this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME)
                . " WHERE " . OpenM_Book_User_PropertyDAO::ID . " NOT IN "
                . "(SELECT " . self::PROPERTY_ID
                . " FROM " . $this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME)
                . " WHERE " . self::USER_ID . "=" . $userId
                . ")) t "
                . "GROUP BY t." . OpenM_Book_User_PropertyDAO::ID . ", " . OpenM_Book_User_Property_ValueDAO::ID
                . " ORDER BY t." . OpenM_Book_User_PropertyDAO::NAME;


        $result = self::$db->request($sql, self::ID);
        $return = new HashtableString();
        $i = 0;
        while ($line = self::$db->fetch_array($result)) {
            $return->put($i, HashtableString::from($line));
            $i++;
        }
        return $return;
    }

    public function getFromUser($userIdTarget, $userIdCalling) {
        return new HashtableString();
    }

    public function getSequencePropertyName() {
        return self::SEQUENCE_FILE_PATH;
    }

}

?>
