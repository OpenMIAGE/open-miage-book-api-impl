<?php

Import::php("OpenM-Book.api.OpenM_Book_Moderator");
Import::php("OpenM-Book.api.Impl.OpenM_BookCommonsImpl");

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

    /**
     * OK 
     */
    public function removeCommunity($communityId) {
        if (!OpenM_Book_Tool::isGroupIdValid($communityId))
            return $this->error("communityId must be in a valid format");
        if (String::isString($communityId))
            $communityId = intval("$communityId");

        if (!$this->isUserRegistered())
            return $this->error;
        else
            $user = $this->user;

        OpenM_Log::debug("check if user is moderator of community", __CLASS__, __METHOD__, __LINE__);
        $communityModeratorDAO = new OpenM_Book_Community_ModeratorDAO();
        if (!$communityModeratorDAO->isUserModerator($user->get(OpenM_Book_UserDAO::ID)->toInt(), $communityId)) {
            OpenM_Log::debug("user is not moderator of community", __CLASS__, __METHOD__, __LINE__);
            $adminDAO = new OpenM_Book_AdminDAO();
            OpenM_Log::debug("check if user is administrator", __CLASS__, __METHOD__, __LINE__);
            $admin = $adminDAO->get($user->get(OpenM_Book_UserDAO::UID)->toInt());
            if ($admin == null)
                return $this->error(self::RETURN_ERROR_MESSAGE_NOT_ENOUGH_RIGHTS_VALUE);
            OpenM_Log::debug("user is administrator", __CLASS__, __METHOD__, __LINE__);
        }

        OpenM_Log::debug("check if community contains users", __CLASS__, __METHOD__, __LINE__);
        $communityContentUserDAO = new OpenM_Book_Community_Content_UserDAO();
        if ($communityContentUserDAO->countOfUsers($communityId) > 0)
            return $this->error("community must not contain users before remove");

        OpenM_Log::debug("check if community contains childs", __CLASS__, __METHOD__, __LINE__);
        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        if ($groupContentGroupDAO->hasDescendant($communityId))
            return $this->error("community must not contain community descendant");

        OpenM_Log::debug("recover moderator group of community", __CLASS__, __METHOD__, __LINE__);
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

    public function addCommunityModerator($userId, $communityId, $validity = null) {
        return $this->notImplemented();
    }

    public function blockUserRegistry($userId, $communityId) {
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

        if (!$this->isUserRegistered())
            return $this->error;
        else
            $user = $this->user;

        OpenM_Log::debug("check if user is moderator of community", __CLASS__, __METHOD__, __LINE__);
        $communityModeratorDAO = new OpenM_Book_Community_ModeratorDAO();
        if (!$communityModeratorDAO->isUserModerator($user->get(OpenM_Book_UserDAO::ID)->toInt(), $communityId)) {
            OpenM_Log::debug("user is not moderator of community", __CLASS__, __METHOD__, __LINE__);
            $adminDAO = new OpenM_Book_AdminDAO();
            OpenM_Log::debug("check if user is administrator", __CLASS__, __METHOD__, __LINE__);
            $admin = $adminDAO->get($user->get(OpenM_Book_UserDAO::UID)->toInt());
            if ($admin == null)
                return $this->error(self::RETURN_ERROR_MESSAGE_NOT_ENOUGH_RIGHTS_VALUE);
            OpenM_Log::debug("user is administrator", __CLASS__, __METHOD__, __LINE__);
        }

        $groupDAO = new OpenM_Book_GroupDAO();
        OpenM_Log::debug("update name of community", __CLASS__, __METHOD__, __LINE__);
        $groupDAO->update($communityId, $newName);

        return $this->ok();
    }

}

?>