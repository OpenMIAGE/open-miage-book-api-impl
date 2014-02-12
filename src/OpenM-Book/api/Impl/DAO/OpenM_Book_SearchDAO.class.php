<?php

Import::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");

/**
 * Description of OpenM_Book_SearchDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author Gael SAUNIER
 */
class OpenM_Book_SearchDAO extends OpenM_Book_DAO {

    const OpenM_BOOK_SEARCH_TABLE_NAME = "OpenM_BOOK_GROUP_SEARCH";
    const STRING = "string";
    const ID = "id";
    const TYPE = "type";
    const OWNER = "owner_id";
    const TYPE_GENERIC_GROUP = 1;
    const TYPE_PERSONAL_GROUP = 2;
    const TYPE_USER = 3;
    const LENTH_WORD = 10;
    const MAX_TERM_NUMBER = 8;
    const MAX_RESULT_DEFAULT_NUMBER = 10;
    const MAX_RESULT_MAX_NUMBER = 30;

    public function index($string, $id, $type, $owner = null) {
        $terms = OpenM_Book_Tool::strlwr($string);
        $arrayString = array_values(array_unique(explode(" ", $terms)));

        $limit = min(array(sizeof($arrayString), self::MAX_TERM_NUMBER));
        $array = array(
            self::ID => $id,
            self::TYPE => $type,
        );

        if ($owner != null)
            $array[self::OWNER] = $owner;

        for ($i = 0; $i < $limit; $i++) {
            $a = $array;
            $a[self::STRING] = new String($arrayString[$i]);
            self::$db->request(OpenM_DB::insert($this->getTABLE(self::OpenM_BOOK_SEARCH_TABLE_NAME), $a));
        }
    }

    public function unIndex($id, $type) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OpenM_BOOK_SEARCH_TABLE_NAME), array(
                    self::ID => intval("$id"),
                    self::TYPE => intval("$type")
        )));
    }

    public function search($string, $maxNumberResult = null, $genericsGroup = true, $personalGroups = true, $user = true) {
        $terms = preg_replace("/[^a-z0-9]+/", " ", OpenM_Book_Tool::strlwr($string));
        $arrayString = explode(" ", $terms);
        $like = "";
        $limit = min(array(sizeof($arrayString), self::MAX_TERM_NUMBER));
        for ($i = 0; $i < $limit; $i++)
            $like .= self::STRING . " LIKE '" . substr($arrayString[$i], 0, self::LENTH_WORD) . "%' OR ";
        $like = substr($like, 0, -4);
        if ($limit > 1)
            $like = "($like)";

        $request = "";
        $i = 0;

        if ($genericsGroup) {
            $request .= "(" . $this->searchGenericGroups($like) . ")";
            $i++;
        }
        if ($personalGroups) {
            $request .= ((strlen($request) > 0) ? " UNION ALL (" : "(") . $this->searchPersonalGroups($like) . ")";
            $i++;
        }
        if ($user) {
            $request .= ((strlen($request) > 0) ? " UNION ALL (" : "(") . $this->searchUsers($like) . ")";
            $i++;
        }
        if (strlen($request) == 0)
            return null;

        if ($i > 1)
            $request = "($request)";

        $request = "SELECT count(*) as nb, " . self::ID . ", " . self::STRING . ", " . self::TYPE . " FROM $request o GROUP BY  " . self::STRING . ", " . self::TYPE;
        $request = "SELECT " . self::ID . ", " . self::STRING . ", " . self::TYPE . " FROM ($request) o ORDER BY nb DESC, " . self::STRING;

        if ($maxNumberResult == null)
            $maxNumberResult = self::MAX_RESULT_DEFAULT_NUMBER;
        else
            $maxNumberResult = min(array(intval($maxNumberResult), self::MAX_RESULT_MAX_NUMBER));

        $return = new HashtableString();
        $sql = self::$db->limit($request, $maxNumberResult);
        $result = self::$db->request($sql, self::ID);
        $i = 0;
        while ($line = self::$db->fetch_array($result)) {
            $l = HashtableString::from($line);
            $return->put($i, $l->put(self::STRING, self::$db->unescape($l->get(self::STRING))));
            $i++;
        }
        return $return;
    }

    private function searchGenericGroups($like) {
        return "SELECT " . OpenM_Book_GroupDAO::ID . " as " . self::ID
                . ", " . OpenM_Book_GroupDAO::NAME . " as " . self::STRING
                . ", " . self::TYPE_GENERIC_GROUP . " as " . self::TYPE
                . " FROM "
                . $this->getTABLE(OpenM_Book_GroupDAO::OpenM_BOOK_GROUP_TABLE_NAME)
                . " a, " . $this->getTABLE(self::OpenM_BOOK_SEARCH_TABLE_NAME) . " b"
                . " WHERE a." . OpenM_Book_GroupDAO::ID . " = b." . self::ID
                . " AND b." . self::TYPE . " = " . self::TYPE_GENERIC_GROUP
                . " AND $like";
    }

    private function searchPersonalGroups($like) {
        return "SELECT " . OpenM_Book_GroupDAO::ID . " as " . self::ID
                . ", " . OpenM_Book_GroupDAO::NAME . " as " . self::STRING
                . ", " . self::TYPE_PERSONAL_GROUP . " as " . self::TYPE
                . " FROM "
                . $this->getTABLE(OpenM_Book_GroupDAO::OpenM_BOOK_GROUP_TABLE_NAME) . " a, "
                . $this->getTABLE(self::OpenM_BOOK_SEARCH_TABLE_NAME) . " b"
                . " WHERE a." . OpenM_Book_GroupDAO::ID . " = b." . self::ID
                . " AND b." . self::TYPE . " = " . self::TYPE_PERSONAL_GROUP
                . " AND $like";
    }

    private function searchUsers($like) {
        return "SELECT " . OpenM_Book_UserDAO::ID . " as " . self::ID
                . ", " . self::$db->concat(array(OpenM_Book_UserDAO::FIRST_NAME, "' '", OpenM_Book_UserDAO::LAST_NAME)) . " as " . self::STRING
                . ", " . self::TYPE_USER . " as " . self::TYPE
                . " FROM "
                . $this->getTABLE(OpenM_Book_UserDAO::OpenM_Book_User_Table_Name) . " a, "
                . $this->getTABLE(self::OpenM_BOOK_SEARCH_TABLE_NAME) . " b"
                . " WHERE a." . OpenM_Book_UserDAO::ACTIVATED . " = " . OpenM_Book_UserDAO::ACTIVE
                . " AND a." . OpenM_Book_UserDAO::ID . " = b." . self::ID
                . " AND b." . self::TYPE . " = " . self::TYPE_USER
                . " AND " . $like;
    }

    public function deleteFromGroup($groupId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OpenM_BOOK_SEARCH_TABLE_NAME), array(
                    self::ID => intval($groupId)
                )) . " AND " . self::TYPE . " IN(" . self::TYPE_GENERIC_GROUP . ", " . self::TYPE_PERSONAL_GROUP . ")");
    }

}

?>