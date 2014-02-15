<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * Description of openM_Book_Group_section
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER
 */
class OpenM_Book_Community_To_SectionDAO extends OpenM_Book_DAO {

    const OPENM_BOOK_COMMUNITY_TO_SECTION_TABLE_NAME = "OpenM_BOOK_COMMUNITY_TO_SECTION";
    const COMMUNITY_ID = "community_id";
    const SECTION_ID = "section_id";

    /**
     * 
     * @param int $groupId
     * @param int $sectionId
     * @return boolean
     * @throws OpenM_DBException
     */
    public function create($groupId, $sectionId) {
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OPENM_BOOK_COMMUNITY_TO_SECTION_TABLE_NAME), array(
                    self::COMMUNITY_ID => intval("$groupId"),
                    self::SECTION_ID => intval("$sectionId")
        )));

        $return = new HashtableString();
        return $return->put(self::COMMUNITY_ID, $groupId)
                        ->put(self::SECTION_ID, $sectionId);
    }

    public function delete($groupId, $sectionId = null) {
        $array = array(
            self::COMMUNITY_ID => intval("$groupId")
        );
        if ($sectionId != null)
            $array[self::SECTION_ID] = intval("$sectionId");

        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_COMMUNITY_TO_SECTION_TABLE_NAME), $array));
    }

    public function getFromGroup($groupId) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_TO_SECTION_TABLE_NAME), array(
                            self::COMMUNITY_ID => intval("$groupId")
        )));
    }

    public function getFromSection($sectionId) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_TO_SECTION_TABLE_NAME), array(
                            self::SECTION_ID => intval("$sectionId")
                        )), self::COMMUNITY_ID);
    }

    public function getCommunityAncestors($communityId) {
        $communityAncestors = OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_COMMUNITY_TO_SECTION_TABLE_NAME), array(), array(
                    self::COMMUNITY_ID
                ))
                . " WHERE "
                . self::COMMUNITY_ID . " IN "
                . "("
                . OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(
                    OpenM_Book_Group_Content_GroupDAO::GROUP_ID => intval($communityId)
                        ), array(OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID))
                . ")"
                . " OR " . self::COMMUNITY_ID . "=$communityId";
        return self::$db->request_HashtableString(
                        "SELECT i." . OpenM_Book_GroupDAO::NAME . ", j."
                        . OpenM_Book_Group_Content_GroupDAO::GROUP_ID . ", j."
                        . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID
                        . " FROM " . $this->getTABLE(OpenM_Book_GroupDAO::OpenM_BOOK_GROUP_TABLE_NAME) . " i, "
                        . "("
                        . OpenM_DB::select($this->getTABLE(OpenM_Book_Group_Content_GroupDAO::OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME))
                        . " WHERE "
                        . OpenM_Book_Group_Content_GroupDAO::GROUP_ID . " IN ($communityAncestors)"
                        . " AND " . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID . " IN ($communityAncestors)"
                        . ") j"
                        . " WHERE i." . OpenM_Book_GroupDAO::ID . "=j." . OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID
                        , OpenM_Book_GroupDAO::ID);
    }

}

?>