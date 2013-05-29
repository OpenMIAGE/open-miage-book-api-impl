<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * Description of OpenM_Book_SectionDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Nicolas Rouzeaud & Gael SAUNIER
 */
class OpenM_Book_SectionDAO extends OpenM_Book_DAO {
    //nom de la table

    const OpenM_BOOK_SECTION_Table_Name = "OpenM_BOOK_SECTION";

    //champs de la table
    const ID = "section_id";
    const NAME = "name";
    const USER_CAN_REGISTER = "user_can_register";
    const ONLY_ONE_COMMUNITY = "only_one_community";
    const REG_EXP = "reg_exp";
    const SECTION_PARENT_ID = "section_id_parent";
    const VALIDATION_REQUIRED = "validation_required";
    const MANAGE_PERIOD = "manage_period";
    const USER_CAN_ADD_COMMUNITY = "user_can_add_community";
    const ACTIVATED = 1;
    const DESACTIVATED = 0;

    public function create($name, $regExp, $section_parent_id = null, $onlyOneCommunity = self::ACTIVATED, $userCanRegister = self::DESACTIVATED, $needValidation = self::DESACTIVATED, $managePeriod = self::DESACTIVATED, $userCanAddCommunity = self::DESACTIVATED) {
        $arrayARgument = array(
            self::NAME => self::$db->escape($name),
            self::ONLY_ONE_COMMUNITY => ($onlyOneCommunity) ? 1 : 0,
            self::USER_CAN_REGISTER => ($userCanRegister) ? 1 : 0,
            self::VALIDATION_REQUIRED => ($needValidation) ? 1 : 0,
            self::MANAGE_PERIOD => ($managePeriod) ? 1 : 0,
            self::USER_CAN_ADD_COMMUNITY => ($userCanAddCommunity) ? 1 : 0,
            self::REG_EXP => $regExp
        );
        if (!is_null($section_parent_id))
            $arrayARgument[self::SECTION_PARENT_ID] = intval($section_parent_id);
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OpenM_BOOK_SECTION_Table_Name), $arrayARgument));

        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_SECTION_Table_Name), $arrayARgument));
    }

    public function get($sectionId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_SECTION_Table_Name), array(
                            self::ID => intval($sectionId)
                        )));
    }

    public function getFromCommunity($communityId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_SECTION_Table_Name))
                        . " WHERE " . self::ID . "=("
                        . OpenM_DB::select($this->getTABLE(OpenM_Book_Community_To_SectionDAO::OPENM_BOOK_COMMUNITY_TO_SECTION_TABLE_NAME), array(
                            OpenM_Book_Community_To_SectionDAO::COMMUNITY_ID => intval($communityId)
                                ), array(
                            OpenM_Book_Community_To_SectionDAO::SECTION_ID
                        ))
                        . ")"
        );
    }

    public function getFromParent($sectionId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_SECTION_Table_Name), array(
                            self::SECTION_PARENT_ID => intval($sectionId)
                        )), self::ID);
    }

    public function getGroupChildsFromSection($sectionId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(OpenM_Book_GroupDAO::OpenM_BOOK_GROUP_TABLE_NAME), array(), array(
                            OpenM_Book_GroupDAO::ID
                        ))
                        . " WHERE " . OpenM_Book_GroupDAO::ID . " IN ("
                        . OpenM_DB::select($this->getTABLE(OpenM_Book_Community_To_SectionDAO::OPENM_BOOK_COMMUNITY_TO_SECTION_TABLE_NAME), array(), array(
                            OpenM_Book_Community_To_SectionDAO::COMMUNITY_ID
                        ))
                        . " WHERE " . OpenM_Book_Community_To_SectionDAO::SECTION_ID . " IN ("
                        . OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_SECTION_Table_Name), array(
                            self::SECTION_PARENT_ID => intval($sectionId)
                                ), array(
                            self::ID
                        ))
                        . "))", OpenM_Book_GroupDAO::ID);
    }

    public function getRoot() {
        return self::$db->request_fetch_HashtableString(self::$db->limit("SELECT *, min(" . self::ID . ") FROM "
                                . $this->getTABLE(self::OpenM_BOOK_SECTION_Table_Name), 1));
    }

    public function remove($sectionId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OpenM_BOOK_SECTION_Table_Name), array(
                    self::ID => intval($sectionId)
                )));
    }

    public function update($sectionId, $property, $value) {
        self::$db->request(OpenM_DB::update($this->getTABLE(self::OpenM_BOOK_SECTION_Table_Name), array(
                    $property => $value
                        ), array(
                    self::ID => intval($sectionId)
                )));
    }

}

?>