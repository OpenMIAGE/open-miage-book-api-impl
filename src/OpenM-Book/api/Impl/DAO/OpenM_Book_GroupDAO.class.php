<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * Description of OpenM_Book_GroupDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER & Nicolas Rouzeaud
 */
class OpenM_Book_GroupDAO extends OpenM_Book_DAO {

    const OpenM_BOOK_GROUP_TABLE_NAME = "OpenM_BOOK_GROUP";
    const SEQUENCE_FILE_PATH = "OpenM_Book.DAO.sequence.group.file.path";

    //champs de la table
    const ID = "group_id";
    const NAME = "name";
    const TYPE = "type";


    //Valeur des type
    const TYPE_COMMUNITY = 1;
    const TYPE_NOT_GENERIC = 0;

    /**
     * Enregistre un groupe dans la base de données
     * @param String $name
     * @param int $type
     * @return HashtableString le nouveau groupe
     * @throws OpenM_DBException
     */
    public function create($name = null, $type = 0) {
        $newId = $this->sequence->next();

        $array = array(
            self::ID => $newId,
            self::TYPE => intval("$type"),
        );

        if ($name != null)
            $array[self::NAME] = self::$db->escape($name);

        self::$db->request(OpenM_DB::insert($this->getTABLE(self::OpenM_BOOK_GROUP_TABLE_NAME), $array));

        $return = new HashtableString();
        return $return->put(self::ID, $newId)
                        ->put(self::NAME, $name)
                        ->put(self::TYPE, $type);
    }

    /**
     * retourne un groupe de la bdd
     * @param int $groupId
     * @return HashtableString 
     */
    public function get($groupId) {
        $return = self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_GROUP_TABLE_NAME), array(
                    self::ID => intval("$groupId"),
        )));
        return $return->put(self::NAME, self::$db->unescape($return->get(self::NAME)));
    }

    /**
     * 
     * @param int $groupId
     * @param String $name
     * @param int $type
     * @return boolean
     * @throws OpenM_DBException
     */
    public function update($groupId, $name = null, $type = null) {
        if (is_null($name) && is_null($type)) {
            return TRUE;
        } else {
            $arrayArgument = array();
            if (is_null($name)) {
                $arrayArgument[self::TYPE] = intval("$type");
            } else {
                $name = self::$db->escape($name);
                if (is_null($type)) {
                    $arrayArgument[self::NAME] = $name;
                } else {
                    $arrayArgument[self::TYPE] = intval("$type");
                    $arrayArgument[self::NAME] = $name;
                }
            }
            self::$db->request(OpenM_DB::update($this->getTABLE(self::OpenM_BOOK_GROUP_TABLE_NAME), $arrayArgument, array(
                        self::ID => intval("$groupId")
            )));
            return TRUE;
        }
    }

    /**
     * @return HashtableString 
     */
    public function getCommunityRoot() {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OpenM_BOOK_GROUP_TABLE_NAME))
                        . " WHERE " . self::ID . "=("
                        . self::$db->limit("SELECT min("
                                . OpenM_Book_Community_To_SectionDAO::COMMUNITY_ID . ") as "
                                . OpenM_Book_Community_To_SectionDAO::COMMUNITY_ID . " FROM "
                                . $this->getTABLE(OpenM_Book_Community_To_SectionDAO::OPENM_BOOK_COMMUNITY_TO_SECTION_TABLE_NAME), 1)
                        . ")"
        );
    }

    /**
     * supprime la groupe de la bdd, Attention suppression en cascade 
     * (va supprimer toutes les dépendances dans toutes les tables
     * @param type $groupId
     * @return boolean
     */
    public function delete($groupId, $cascade = true) {
        $groupId = intval("$groupId");
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OpenM_BOOK_GROUP_TABLE_NAME), array(
                    self::ID => $groupId
        )));

        if ($cascade) {
            $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
            $groupContentGroupDAO->deleteGroup($groupId);

            $communityModeratorDAO = new OpenM_Book_Community_ModeratorDAO();
            $communityModeratorDAO->deleteFromCommunity($groupId);

            $communityBannedDAO = new OpenM_Book_Community_Banned_UsersDAO();
            $communityBannedDAO->deleteFromCommunity($groupId);

            $groupContentUserDAO = new OpenM_Book_Group_Content_UserDAO();
            $groupContentUserDAO->deleteFromGroup($groupId);

            $communityContentUserDAO = new OpenM_Book_Community_Content_UserDAO();
            $communityContentUserDAO->deleteFromCommunity($groupId);

            $communityContentUserValidationDAO = new OpenM_Book_Community_Content_User_ValidationDAO();
            $communityContentUserValidationDAO->deleteFromCommunity($groupId);

            $communityPeriod = new OpenM_Book_Community_PeriodDAO();
            $communityPeriod->deleteFromCommunity($groupId);

            $communityToSection = new OpenM_Book_Community_To_SectionDAO();
            $communityToSection->delete($groupId);

            $communityVisibility = new OpenM_Book_Community_VisibilityDAO();
            $communityVisibility->deleteFromCommunity($groupId);

            $searchDAO = new OpenM_Book_SearchDAO();
            $searchDAO->deleteFromGroup($groupId);
        }
    }

    /**
     * retourne le nom de la propriété du fichier de compte des groupes
     * (pour l'ID unique du groupe)
     * @return String
     */
    public function getSequencePropertyName() {
        return self::SEQUENCE_FILE_PATH;
    }

}

?>