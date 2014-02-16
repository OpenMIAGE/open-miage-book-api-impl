<?php

Import::php("OpenM-Book.api.OpenM_Book_Moderator");
Import::php("OpenM-Book.api.Impl.OpenM_BookCommonsImpl");
Import::php("OpenM-Book.api.Impl.OpenM_Book_AdminImpl");

/**
 * 
 * @package OpenM
 * @subpackage OpenM\OpenM-Book\api\Impl
 * @license http://www.apache.org/licenses/LICENSE-2.0 Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @link http://www.open-miage.org
 * @author Gaël SAUNIER
 */
class OpenM_Book_ModeratorImpl extends OpenM_BookCommonsImpl implements OpenM_Book_Moderator {

    private $isAdmin = false;
    private $isModerator = false;

    private function hasEnoughRights($communityId) {
        if (!$this->isUserRegistered())
            return false;
        OpenM_Log::debug("check if user is moderator of community", __CLASS__, __METHOD__, __LINE__);
        $communityModeratorDAO = new OpenM_Book_Community_ModeratorDAO();
        if (!$communityModeratorDAO->isUserModerator($this->user->get(OpenM_Book_UserDAO::ID)->toInt(), $communityId)) {
            OpenM_Log::debug("user is not moderator of community", __CLASS__, __METHOD__, __LINE__);
            $adminDAO = new OpenM_Book_AdminDAO();
            OpenM_Log::debug("check if user is administrator", __CLASS__, __METHOD__, __LINE__);
            $admin = $adminDAO->get($this->user->get(OpenM_Book_UserDAO::UID)->toInt());
            if ($admin == null) {
                $this->error = $this->error(self::RETURN_ERROR_MESSAGE_NOT_ENOUGH_RIGHTS_VALUE);
                return false;
            }
            $this->isAdmin = true;
            OpenM_Log::debug("user is administrator", __CLASS__, __METHOD__, __LINE__);
        }
        $this->isModerator = true;
        return true;
    }

    /**
     * OK 
     */
    public function removeCommunity($communityId) {
        if (!OpenM_Book_Tool::isGroupIdValid($communityId))
            return $this->error("communityId must be in a valid format");
        if (String::isString($communityId))
            $communityId = intval("$communityId");

        if (!$this->hasEnoughRights($communityId))
            return $this->error;

        OpenM_Log::debug("check if community contains users", __CLASS__, __METHOD__, __LINE__);
        $communityContentUserDAO = new OpenM_Book_Community_Content_UserDAO();
        if ($communityContentUserDAO->countOfUsers($communityId) > 0)
            return $this->error("community must not contain users before remove");

        OpenM_Log::debug("check if community contains childs", __CLASS__, __METHOD__, __LINE__);
        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        if ($groupContentGroupDAO->hasDescendant($communityId))
            return $this->error("community must not contain community descendant");

        OpenM_Log::debug("check if community has parent", __CLASS__, __METHOD__, __LINE__);
        $communityDAO = new OpenM_Book_Community_To_SectionDAO();
        $section = $communityDAO->getCommunityAncestors($communityId);
        if ($section->size() == 0)
            return $this->error("community must have parent");

        OpenM_Log::debug("recover moderator group of community", __CLASS__, __METHOD__, __LINE__);
        $communityModeratorDAO = new OpenM_Book_Community_ModeratorDAO();
        $moderator = $communityModeratorDAO->getFromCommunity($communityId);
        OpenM_Log::debug("recover banned group of community", __CLASS__, __METHOD__, __LINE__);
        $bannedGroupDAO = new OpenM_Book_Community_Banned_UsersDAO();
        $bannedGroup = $bannedGroupDAO->getFromCommunity($communityId);
        $groupDAO = new OpenM_Book_GroupDAO();
        OpenM_Log::debug("delete moderator group of community", __CLASS__, __METHOD__, __LINE__);
        $groupDAO->delete($moderator->get(OpenM_Book_Community_ModeratorDAO::MODERATOR_ID)->toInt(), true);
        OpenM_Log::debug("delete banned group of community", __CLASS__, __METHOD__, __LINE__);
        $groupDAO->delete($bannedGroup->get(OpenM_Book_Community_Banned_UsersDAO::BANNED_GROUP_ID)->toInt(), true);
        OpenM_Log::debug("delete community", __CLASS__, __METHOD__, __LINE__);
        $groupDAO->delete($communityId, true);

        return $this->ok();
    }

    /**
     * OK
     */
    public function renameCommunity($communityId, $newName) {
        if (!OpenM_Book_Tool::isGroupIdValid($communityId))
            return $this->error("communityId must be in a valid format");
        if (String::isString($communityId))
            $communityId = intval("$communityId");
        if (!String::isString($newName))
            return $this->error("newName must be a String");

        if (!$this->hasEnoughRights($communityId))
            return $this->error;

        OpenM_Log::debug("Check if newName respect RegExp constraints", __CLASS__, __METHOD__, __LINE__);
        $sectionDAO = new OpenM_Book_SectionDAO();
        $section = $sectionDAO->getFromCommunity($communityId);
        OpenM_Log::debug("Constraints : '$newName' / '^" . $section->get(OpenM_Book_SectionDAO::REG_EXP) . "$'", __CLASS__, __METHOD__, __LINE__);
        if (!RegExp::ereg("^" . $section->get(OpenM_Book_SectionDAO::REG_EXP) . "$", $newName))
            return $this->error("you must respect names' constraints : " . $section->get(OpenM_Book_SectionDAO::REG_EXP));

        $groupDAO = new OpenM_Book_GroupDAO();
        $community = $groupDAO->get($communityId);
        if ($community === null)
            return $this->error("community not found");

        OpenM_Log::debug("unindex old name of community", __CLASS__, __METHOD__, __LINE__);
        $groupSearchDAO = new OpenM_Book_SearchDAO();
        $groupSearchDAO->unIndex($communityId, OpenM_Book_SearchDAO::TYPE_GENERIC_GROUP);

        OpenM_Log::debug("update name of community", __CLASS__, __METHOD__, __LINE__);
        $groupDAO->update($communityId, $newName);

        OpenM_Log::debug("search ancestors name of community", __CLASS__, __METHOD__, __LINE__);
        $groupContentUserDAO = new OpenM_Book_Group_Content_GroupDAO();
        $ancestors = $groupContentUserDAO->getCommunityAncestorNames($communityId);
        $names = "";
        $parent = $ancestors->get($communityId);
        for ($i = 0; $i < OpenM_Book_AdminImpl::INDEXED_ANCESTOR_NAME_NUMBER; $i++) {
            if ($parent === null)
                break;
            $names = $parent->get(OpenM_Book_GroupDAO::NAME) . " $names";
            $c = $parent;
            $parent = $ancestors->get($c->get(OpenM_Book_Group_Content_GroupDAO::GROUP_PARENT_ID));
        }
        OpenM_Log::debug("index new name of community", __CLASS__, __METHOD__, __LINE__);
        $groupSearchDAO->index("$names $newName", $communityId, OpenM_Book_SearchDAO::TYPE_GENERIC_GROUP);

        return $this->ok();
    }

    public function validateUser($userId, $communityId) {
        return $this->notImplemented();
    }

    public function addCommunityModerator($userId, $communityId, $validity = null) {
        return $this->notImplemented();
    }

    public function banUserFromCommunity($userId, $communityId) {
        return $this->notImplemented();
    }

    public function getCommunityModerators($communityId) {
        return $this->notImplemented();
    }

    public function removeCommunityModerartor($userId, $communityId) {
        return $this->notImplemented();
    }

    public function removeCommunityUser($userId, $communityId) {
        return $this->notImplemented();
    }

}

?>