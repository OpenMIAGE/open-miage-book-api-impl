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
    const VISIBILITY = "visibility";

    public function create($propertyId, $propertyValue, $userID, $visibilityId) {
        $newid = $this->sequence->next();
        self::$db->request(self::$db->insert($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
                    self::ID => $newid,
                    self::PROPERTY_ID => intval("$propertyId"),
                    self::USER_ID => intval("$userID"),
                    self::VALUE => self::$db->escape($propertyValue),
                    self::VISIBILITY => intval("$visibilityId")
        )));

        $retour = new HashtableString();
        return $retour->put(self::ID, $newid)
                        ->put(self::PROPERTY_ID, $propertyId)
                        ->put(self::USER_ID, $userID)
                        ->put(self::VALUE, $propertyValue)
                        ->put(self::VISIBILITY, $visibilityId);
    }

    public function update($propertyValueId, $value) {
        self::$db->request(OpenM_DB::update($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
                    self::VALUE => self::$db->escape($value)
                        ), array(
                    self::ID => $propertyValueId
        )));
    }

    public function delete($propertyValueId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
                    self::ID => intval("$propertyValueId")
        )));

        //todo : remove visibility of property
    }

    /**
     * récupére propertyId, valueId, value, userId à partir d'une valueId    
     * @param int $propertyValueId
     * @return HashtableString
     */
    public function get($propertyValueId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
                            self::ID => intval("$propertyValueId")
        )));
    }

    private function _getProperties($userId) {
        $sql = "SELECT * FROM (SELECT * FROM ("
                . "SELECT  p." . OpenM_Book_User_PropertyDAO::ID . ", " . OpenM_Book_User_PropertyDAO::NAME . ", " . self::ID . ", " . self::VALUE . ", " . self::VISIBILITY
                . " FROM " . $this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME) . " as p, "
                . $this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME) . " as v "
                . "WHERE p." . OpenM_Book_User_PropertyDAO::ID . "=v." . self::PROPERTY_ID
                . " AND " . self::USER_ID . "=$userId) as a"
                . " UNION "
                . "SELECT " . OpenM_Book_User_PropertyDAO::ID . "," . OpenM_Book_User_PropertyDAO::NAME . ", '', '', '' "
                . " FROM " . $this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME)
                . " WHERE " . OpenM_Book_User_PropertyDAO::ID . " NOT IN "
                . "(SELECT " . self::PROPERTY_ID
                . " FROM " . $this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME)
                . " WHERE " . self::USER_ID . "=" . $userId
                . ")) t "
                . "GROUP BY t." . OpenM_Book_User_PropertyDAO::ID . ", " . OpenM_Book_User_Property_ValueDAO::ID
                . " ORDER BY t." . OpenM_Book_User_PropertyDAO::NAME;


        $result = self::$db->request($sql);
        $return = new HashtableString();
        $i = 0;
        while ($line = self::$db->fetch_array($result)) {
            $l = HashtableString::from($line);
            $return->put($i, $l->put(self::VALUE, self::$db->unescape($l->get(self::VALUE))));
            $i++;
        }
        return $return;
    }

    public function getPropertiesOfUser($userId, $userIdCalling) {
        $sql = "SELECT  p." . OpenM_Book_User_PropertyDAO::ID . " ," . OpenM_Book_User_PropertyDAO::NAME . " , v." . self::ID . " , v." . self::VALUE
                . " FROM " . $this->getTABLE(OpenM_Book_User_PropertyDAO::OPENM_BOOK_USER_PROPERTY_TABLE_NAME) . " p, "
                . $this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME) . " v "
                . "WHERE p." . OpenM_Book_User_PropertyDAO::ID . "=v." . self::PROPERTY_ID
                . " AND v." . self::USER_ID . "=$userId"
                . " AND v." . self::VISIBILITY . " IN ("
                . $this->_getVisibilityGroupFromUser($userIdCalling)
                . ")"
                . " GROUP BY p." . OpenM_Book_User_PropertyDAO::ID . ", v." . self::ID
                . " ORDER BY p." . OpenM_Book_User_PropertyDAO::NAME;

        $result = self::$db->request($sql, self::ID);
        $return = new HashtableString();
        $i = 0;
        while ($line = self::$db->fetch_array($result)) {
            $l = HashtableString::from($line);
            $return->put($i, $l->put(self::VALUE, self::$db->unescape($l->get(self::VALUE))));
            $i++;
        }
        return $return;
    }

    private function _getVisibilityGroupFromUser($userId) {
        $in = OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_UserDAO::OPENM_BOOK_GROUP_CONTENT_USER_TABLE_NAME), null, array(
                    OpenM_Book_Group_Content_UserDAO::GROUP_ID
                )) . " WHERE " . OpenM_Book_Group_Content_UserDAO::USER_ID . "=$userId"
                . " UNION "
                . OpenM_DB::select($this->getTABLE(OpenM_Book_Community_Content_UserDAO::OPENM_BOOK_COMMUNITY_CONTENT_USER_TABLE_NAME), null, array(
                    OpenM_Book_Community_Content_UserDAO::COMMUNITY_ID
                )) . " WHERE " . OpenM_Book_Community_Content_UserDAO::USER_ID . "=$userId"
                . " AND " . OpenM_Book_Community_Content_UserDAO::IS_VALIDATED . "=" . OpenM_Book_Community_Content_UserDAO::VALIDATED;
        return "SELECT " . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID
                . " FROM " . $this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME) . " g "
                . " WHERE g." . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID
                . " IN ($in) "
                . " UNION "
                . $in;
    }

    public function getFromUser($userIdTarget, $userIdCalling = null) {
        if (intval("$userIdTarget") === intval("$userIdCalling"))
            return $this->_getProperties($userIdTarget);
        return $this->getPropertiesOfUser($userIdTarget, $userIdCalling);
    }

    public function isVisibilityFromUser($visibilityId, $userId) {
        $sql = OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_USER_PROPERTY_VALUE_TABLE_NAME), array(
                    self::USER_ID => intval("$userId"),
                    self::VISIBILITY => intval("$visibilityId"))
        );
        $result = self::$db->request($sql);
        if (self::$db->fetch_array($result) !== false)
            return true;
        else
            return false;
    }

    public function getSequencePropertyName() {
        return self::SEQUENCE_FILE_PATH;
    }

}

?>
