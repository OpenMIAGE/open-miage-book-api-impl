<?php

Import::php("OpenM-Book.api.OpenM_Book_Moderator");
Import::php("OpenM-Book.api.Impl.OpenM_BookCommonsImpl");

/**
 * 
 * @package OpenM
 * @subpackage OpenM\OpenM-Book\api\Impl  
 * @author Gaël SAUNIER
 */
class OpenM_Book_ModeratorImpl extends OpenM_BookCommonsImpl implements OpenM_Book_Moderator {

    /**
     * OK
     * just test in case of only moderator and not admin
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

        $communityModeratorDAO = new OpenM_Book_Community_ModeratorDAO();
        if (!$communityModeratorDAO->isUserModerator($user->get(OpenM_Book_UserDAO::ID), $communityId)) {
            $adminDAO = new OpenM_Book_AdminDAO();
            $admin = $adminDAO->get($user->get(OpenM_Book_UserDAO::UID));
            if ($admin == null)
                return $this->error(self::RETURN_ERROR_MESSAGE_NOT_ENOUGH_RIGHTS_VALUE);
        }

        $communityContentUserDAO = new OpenM_Book_Community_Content_UserDAO();
        if ($communityContentUserDAO->countOfUsers($communityId) > 0)
            return $this->error("community must not contain users before remove");

        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        if ($groupContentGroupDAO->hasDescendant($communityId))
            return $this->error("community must not contain community descendant");

        $moderator = $communityModeratorDAO->getFromCommunity($communityId);
        $bannedGroupDAO = new OpenM_Book_Community_Banned_UsersDAO();
        $bannedGroup = $bannedGroupDAO->getFromCommunity($communityId);
        $groupDAO = new OpenM_Book_GroupDAO();
        $groupDAO->delete($moderator->get(OpenM_Book_Community_ModeratorDAO::MODERATOR_ID)->toInt(), true);
        $groupDAO->delete($bannedGroup->get(OpenM_Book_Community_Banned_UsersDAO::BANNED_GROUP_ID)->toInt(), true);
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

    public function renameCommunity($communityId, $newName) {
        return $this->notImplemented();
    }

}

?>