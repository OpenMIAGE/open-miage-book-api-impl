<?phpImport::php("OpenM-Book.api.Impl.DAO.OpenM_Book_DAO");/** * Description of OpenM_Book_Group_Content_Group * * @package OpenM  * @subpackage OpenM\OpenM-Book\api\Impl\DAO   * @author Gael SAUNIER */class OpenM_Book_Group_Content_GroupDAO extends OpenM_Book_DAO {    const OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME = "OpenM_BOOK_GROUP_CONTENT_GROUP";    const OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME = "OpenM_BOOK_GROUP_CONTENT_GROUP_INDEX";    const GROUP_PARENT_ID = "group_id_parent";    const GROUP_ID = "group_id";    public function create($groupParentId, $groupId) {        $return = new HashtableString();        $return->put(self::GROUP_PARENT_ID, $groupParentId)                ->put(self::GROUP_ID, $groupId);        try {            self::$db->request(OpenM_DB::insert($this->getTABLE(self:: OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME), array(                        self::GROUP_PARENT_ID => intval($groupParentId),                        self::GROUP_ID => intval($groupId)            )));        } catch (OpenM_DBException $e) {            return $return;        }        $result = self::$db->request(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(                    self::GROUP_ID => intval($groupParentId)                ))                . " AND " . self::GROUP_PARENT_ID . " NOT IN ("                . OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(                    self::GROUP_ID => intval($groupId)                        ), array(                    self::GROUP_PARENT_ID                ))                . ")");        $values = "($groupId, $groupParentId), ";        while ($row = self::$db->fetch_array($result)) {            $values .= "(" . $groupId . ", " . $row[self::GROUP_PARENT_ID] . "), ";        }        $values = substr($values, 0, -2);        self::$db->request("INSERT INTO " . $this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME)                . " (" . self::GROUP_ID . ", " . self::GROUP_PARENT_ID . ") VALUES $values");        $result2 = self::$db->request(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(                    self::GROUP_PARENT_ID => intval($groupId)                ))                . " AND " . self::GROUP_ID . " NOT IN ("                . OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(                    self::GROUP_PARENT_ID => intval($groupParentId)                        ), array(                    self::GROUP_ID                ))                . ")");        $values = "";        while ($row = self::$db->fetch_array($result2)) {            $values .= "(" . $row[self::GROUP_ID] . ", " . $groupParentId . "), ";        }        $values = substr($values, 0, -2);        if (strlen($values) > 0) {            self::$db->request("INSERT INTO " . $this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME)                    . " (" . self::GROUP_ID . ", "                    . self:: GROUP_PARENT_ID . ") VALUES $values");        }    }    public function deleteGroup($groupId) {        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME), array(                    self::GROUP_ID => intval($groupId))) . " OR " .                self::GROUP_PARENT_ID . "=$groupId");        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(                    self::GROUP_ID => intval($groupId))) . " OR " .                self::GROUP_PARENT_ID . "= $groupId");    }    public function delete($groupParentId, $groupId) {        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME), array(                    self::GROUP_ID => intval($groupId),                    self::GROUP_PARENT_ID => intval($groupParentId)        )));    }    public function deleteFromParent($parentGroupId) {        self::$db->request(OpenM_DB::delete($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME), array(                    self::GROUP_PARENT_ID => intval($parentGroupId)        )));    }    public function deleteFromGroup($groupId) {        self::$db->request(OpenM_DB:: delete($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME), array(                    self::GROUP_ID => intval($groupId)        )));    }    public function getParents($groupId) {        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME), array(                            self::GROUP_ID => intval($groupId)                        )), self::GROUP_PARENT_ID);    }    public function getChilds($parentGroupId) {        return $this->_unescape(self::$db->request_HashtableString(                                OpenM_DB::select($this->getTABLE(OpenM_Book_GroupDAO::OpenM_BOOK_GROUP_TABLE_NAME))                                . " WHERE "                                . OpenM_Book_GroupDAO::ID                                . " IN ("                                . OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME), array(                                    self::GROUP_PARENT_ID => intval($parentGroupId)                                        ), array(                                    self::GROUP_ID                                ))                                . ")"                                , self::GROUP_ID), OpenM_Book_GroupDAO::NAME);    }    private function _unescape(HashtableString $result, $var) {        $e = $result->keys();        while ($e->hasNext()) {            $key = $e->next();            $row = $result->get($key);            $row->put($var, self::$db->unescape($row->get("$var")));        }        return $result;    }    public function getAncestors($groupId) {        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(                            self::GROUP_ID => intval($groupId)                        )), self::GROUP_PARENT_ID);    }    public function isAncestor($groupIdAncestor, $groupIdDescendant) {        return $this->isDescendant($groupIdDescendant, $groupIdAncestor);    }    public function getAncestorsOrSelf($groupId) {        $h = new                HashtableString();        $return = $this->getAncestors($groupId);        if ($return == null)            $return = new HashtableString ();        return $return->put($groupId, $h->put(self::GROUP_ID, $groupId)                                ->put(self::GROUP_PARENT_ID, $groupId));    }    public function getDescendants($parentGroupId) {        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(                            self::GROUP_PARENT_ID => intval($parentGroupId)                        )), self::GROUP_ID);    }    public function isDescendant($groupIdDescendant, $groupIdAncestor) {        $result = self::$db->request(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME), array(                    self::GROUP_PARENT_ID => intval($groupIdAncestor),                    self::GROUP_ID => intval($groupIdDescendant)        )));        return self::$db->fetch_array($result) != null;    }    public function getDescendantsOrSelf($parentGroupId) {        $h = new HashtableString();        $return = $this->getDescendants($parentGroupId);        if ($return == null)            $return = new HashtableString();        return $return->put($parentGroupId, $h->put(self::GROUP_ID, $parentGroupId)->put(self::GROUP_PARENT_ID, $parentGroupId));    }    public function get($groupParentId, $groupId) {        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_TABLE_NAME), array(                            self::GROUP_ID => intval($groupId),                            self::GROUP_PARENT_ID => intval($groupParentId)        )));    }    public function hasDescendant($parentGroupId) {        $count = self::$db->request_fetch_array(self::$db->limit("SELECT count(*) as count FROM "                        . $this->getTABLE(self::OPENM_BOOK_GROUP_CONTENT_GROUP_INDEX_TABLE_NAME)                        . " WHERE " . self::GROUP_PARENT_ID . "=$parentGroupId", 1));        return $count["count"] > 0;    }}?>