<?php

Import::php("OpenM-Book.api.OpenM_Book_Admin");
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
class OpenM_Book_AdminImpl extends OpenM_BookCommonsImpl implements OpenM_Book_Admin {

    private $admin;
    private $apiToDAOPropertyNameConvertion;

    private function isAdmin() {
        if ($this->admin instanceof HashtableString)
            return true;
        $uid = $this->getManager()->getID();
        $adminDAO = new OpenM_Book_AdminDAO();
        OpenM_Log::debug("Search Admin in DAO", __CLASS__, __METHOD__, __LINE__);
        $this->admin = $adminDAO->get($uid);
        if ($this->admin == null) {
            $this->error = $this->error(self::RETURN_ERROR_MESSAGE_NOT_ENOUGH_RIGHTS_VALUE);
            return false;
        } else {
            OpenM_Log::debug("Admin found in DAO", __CLASS__, __METHOD__, __LINE__);
            return true;
        }
    }

    private function isUserRegisteredAndAdmin() {
        if (!$this->isUserRegistered())
            return false;
        if (!$this->isAdmin())
            return false;
        return true;
    }

    private function getConvertorArray($key) {
        if (is_array($this->apiToDAOPropertyNameConvertion))
            return $this->apiToDAOPropertyNameConvertion[$key];
        $this->apiToDAOPropertyNameConvertion = array(
            self::RETURN_COMMUNITY_NAME_REGEXP_PARAMETER => OpenM_Book_SectionDAO::REG_EXP,
            self::RETURN_USER_CAN_REGISTER_PARAMETER => OpenM_Book_SectionDAO::USER_CAN_REGISTER,
            self::RETURN_CONTAINS_ONLY_ONE_COMMUNITY_PARAMETER => OpenM_Book_SectionDAO::ONLY_ONE_COMMUNITY,
            self::RETURN_VALIDATION_REQUIRED_PARAMETER => OpenM_Book_SectionDAO::VALIDATION_REQUIRED,
            self::RETURN_MANAGE_PERIOD_PARAMETER => OpenM_Book_SectionDAO::MANAGE_PERIOD,
            self::RETURN_BRANCH_NAME_PARAMETER => OpenM_Book_SectionDAO::NAME,
            OpenM_Book_SectionDAO::REG_EXP => self::RETURN_COMMUNITY_NAME_REGEXP_PARAMETER,
            OpenM_Book_SectionDAO::USER_CAN_REGISTER => self::RETURN_USER_CAN_REGISTER_PARAMETER,
            OpenM_Book_SectionDAO::ONLY_ONE_COMMUNITY => self::RETURN_CONTAINS_ONLY_ONE_COMMUNITY_PARAMETER,
            OpenM_Book_SectionDAO::VALIDATION_REQUIRED => self::RETURN_VALIDATION_REQUIRED_PARAMETER,
            OpenM_Book_SectionDAO::MANAGE_PERIOD => self::RETURN_MANAGE_PERIOD_PARAMETER,
            OpenM_Book_SectionDAO::NAME => self::RETURN_BRANCH_NAME_PARAMETER
        );
        return $this->apiToDAOPropertyNameConvertion[$key];
    }

    /**
     * OK
     * 
     * @param type $branchIdParent
     * @param type $name
     * @return HashtableString
     */
    public function addBranch($branchIdParent, $name) {
        if (!$this->isUserRegisteredAndAdmin())
            return $this->error;
        if (!String::isString($name))
            return $this->error("name must be a string");
        if (!OpenM_Book_Tool::isGroupIdValid($branchIdParent))
            return $this->error("branchIdParent must be in a valid format");

        $sectionDAO = new OpenM_Book_SectionDAO();
        OpenM_Log::debug("check if section parent exists in DAO", __CLASS__, __METHOD__, __LINE__);
        $sectionParent = $sectionDAO->get($branchIdParent);
        if ($sectionParent == null)
            return $this->error("branch parent not found");

        $onlyOneCommunity = false;
        if ($sectionParent->get(OpenM_Book_SectionDAO::ONLY_ONE_COMMUNITY)->toInt() == OpenM_Book_SectionDAO::ACTIVATED)
            $onlyOneCommunity = true;

        OpenM_Log::debug("create the new section in DAO", __CLASS__, __METHOD__, __LINE__);
        $section = $sectionDAO->create($name, self::COMMUNITY_NAME_REGEXP_DEFAULT_PARAMETER_VALUE, $branchIdParent, $onlyOneCommunity);

        $return = $this->ok()->put(self::RETURN_BRANCH_ID_PARAMETER, $section->get(OpenM_Book_SectionDAO::ID));

        if ($onlyOneCommunity) {
            OpenM_Log::debug("User cant add community", __CLASS__, __METHOD__, __LINE__);
            $communitiyToSectionDAO = new OpenM_Book_Community_To_SectionDAO();
            OpenM_Log::debug("check if branch parent contains only one community", __CLASS__, __METHOD__, __LINE__);
            $communitiesParent = $communitiyToSectionDAO->getFromSection($branchIdParent);
            if ($communitiesParent->size() != 1)
                return $this->error("parent branch must contain only one community");
            OpenM_Log::debug("recover communityId parent", __CLASS__, __METHOD__, __LINE__);
            $communityParentId = $communitiesParent->get($communitiesParent->keys()->next())->get(OpenM_Book_Community_To_SectionDAO::COMMUNITY_ID)->toInt();
            
            $community = self::_addCommunity($communityParentId, $name, $section);
            
            $return->put(self::RETURN_COMMUNITY_ID_PARAMETER, $community->get(OpenM_Book_GroupDAO::ID));
        }

        return $return;
    }
    
    public static function _addCommunity($communityParentId, $name, $section){
        OpenM_Log::debug("User can add community", __CLASS__, __METHOD__, __LINE__);
        $groupDAO = new OpenM_Book_GroupDAO();
        OpenM_Log::debug("Create group community in DAO", __CLASS__, __METHOD__, __LINE__);
        $community = $groupDAO->create($name, OpenM_Book_GroupDAO::TYPE_COMMUNITY);
        OpenM_Log::debug("Create group moderator in DAO", __CLASS__, __METHOD__, __LINE__);
        $moderator = $groupDAO->create("moderator");
        OpenM_Log::debug("Create group banned users in DAO", __CLASS__, __METHOD__, __LINE__);
        $bannedGroup = $groupDAO->create("banned");
        OpenM_Log::debug("Create associate moderator group in DAO", __CLASS__, __METHOD__, __LINE__);
        $communityModeratorDAO = new OpenM_Book_Community_ModeratorDAO();
        $communityModeratorDAO->create($community->get(OpenM_Book_GroupDAO::ID), $moderator->get(OpenM_Book_GroupDAO::ID));
        OpenM_Log::debug("Create associate banned group in DAO", __CLASS__, __METHOD__, __LINE__);
        $communityBannedGroupDAO = new OpenM_Book_Community_Banned_UsersDAO();
        $communityBannedGroupDAO->create($community->get(OpenM_Book_GroupDAO::ID), $bannedGroup->get(OpenM_Book_GroupDAO::ID));
        $groupContentGroupDAO = new OpenM_Book_Group_Content_GroupDAO();
        OpenM_Log::debug("add community in community parent in DAO", __CLASS__, __METHOD__, __LINE__);
        $groupContentGroupDAO->create($communityParentId, $community->get(OpenM_Book_GroupDAO::ID));
        OpenM_Log::debug("search community moderator group parent in DAO", __CLASS__, __METHOD__, __LINE__);
        $moderatorParent = $communityModeratorDAO->getFromCommunity($communityParentId);
        OpenM_Log::debug("search community banned group parent in DAO", __CLASS__, __METHOD__, __LINE__);
        $bannedGroupParent = $communityBannedGroupDAO->getFromCommunity($communityParentId);
        OpenM_Log::debug("add community moderator group in community moderator group parent in DAO", __CLASS__, __METHOD__, __LINE__);
        $groupContentGroupDAO->create($moderator->get(OpenM_Book_GroupDAO::ID), $moderatorParent->get(OpenM_Book_Community_ModeratorDAO::MODERATOR_ID)->toInt());
        OpenM_Log::debug("add community banned group in community banned group parent in DAO", __CLASS__, __METHOD__, __LINE__);
        $groupContentGroupDAO->create($bannedGroup->get(OpenM_Book_GroupDAO::ID), $bannedGroupParent->get(OpenM_Book_Community_Banned_UsersDAO::BANNED_GROUP_ID)->toInt());        
        OpenM_Log::debug("add community section association in DAO", __CLASS__, __METHOD__, __LINE__);
        $communitiyToSectionDAO = new OpenM_Book_Community_To_SectionDAO();
        $communitiyToSectionDAO->create($community->get(OpenM_Book_GroupDAO::ID), $section->get(OpenM_Book_SectionDAO::ID)->toInt());
        return $community;
    }

    /**
     * OK
     */
    public function getTree($branchId = null) {
        if (!$this->isUserRegisteredAndAdmin())
            return $this->error;
        if ($branchId != null && !OpenM_Book_Tool::isGroupIdValid($branchId))
            return $this->error("branchId must be in a valid format");

        $sectionDAO = new OpenM_Book_SectionDAO();

        OpenM_Log::debug("Search section in DAO", __CLASS__, __METHOD__, __LINE__);
        if ($branchId == null)
            $section = $sectionDAO->getRoot();
        else
            $section = $sectionDAO->get($branchId);
        OpenM_Log::debug("section found in DAO", __CLASS__, __METHOD__, __LINE__);

        $return_section = new HashtableString();
        OpenM_Log::debug("add selected section in return", __CLASS__, __METHOD__, __LINE__);
        $return_section->put(self::RETURN_BRANCH_ID_PARAMETER, $section->get(OpenM_Book_SectionDAO::ID))
                ->put(self::RETURN_BRANCH_NAME_PARAMETER, $section->get(OpenM_Book_SectionDAO::NAME));

        OpenM_Log::debug("Search section childs' in DAO", __CLASS__, __METHOD__, __LINE__);
        $childs = $sectionDAO->getFromParent($section->get(OpenM_Book_SectionDAO::ID)->toInt());
        $return_childs = new HashtableString();
        $e = $childs->enum();
        while ($e->hasNext()) {
            $child = $e->next();
            $childTemp = new HashtableString();
            $return_childs
                    ->put($child->get(OpenM_Book_SectionDAO::ID), $childTemp
                            ->put(self::RETURN_BRANCH_ID_PARAMETER, $child->get(OpenM_Book_SectionDAO::ID))
                            ->put(self::RETURN_BRANCH_NAME_PARAMETER, $child->get(OpenM_Book_SectionDAO::NAME)));
        }

        $return_parent_section = new HashtableString();
        OpenM_Log::debug("check if section has parent", __CLASS__, __METHOD__, __LINE__);
        if ($section->get(OpenM_Book_SectionDAO::SECTION_PARENT_ID) != null) {
            OpenM_Log::debug("Search parent section in DAO", __CLASS__, __METHOD__, __LINE__);
            $sectionParent = $sectionDAO->get($section->get(OpenM_Book_SectionDAO::SECTION_PARENT_ID));
            OpenM_Log::debug("parent section found in DAO", __CLASS__, __METHOD__, __LINE__);
            $return_parent_section->put(self::RETURN_BRANCH_ID_PARAMETER, $sectionParent->get(OpenM_Book_SectionDAO::ID))
                    ->put(self::RETURN_BRANCH_NAME_PARAMETER, $sectionParent->get(OpenM_Book_SectionDAO::NAME));
        }

        return $this->ok()->put(self::RETURN_BRANCH_PARAMETER, $return_section)
                        ->put(self::RETURN_PARENT_BRANCH_PARAMETER, $return_parent_section)
                        ->put(self::RETURN_BRANCH_CHILDS_PARAMETER, $return_childs);
    }

    /**
     * OK
     */
    public function removeBranch($branchId) {
        if (!$this->isUserRegisteredAndAdmin())
            return $this->error;
        if (!OpenM_Book_Tool::isGroupIdValid($branchId))
            return $this->error("branchId must be in a valid format");

        $sectionDAO = new OpenM_Book_SectionDAO();
        OpenM_Log::debug("check if section exists in DAO", __CLASS__, __METHOD__, __LINE__);
        $section = $sectionDAO->get($branchId);
        if ($section == null)
            return $this->error("section not found");

        OpenM_Log::debug("check if contains section child", __CLASS__, __METHOD__, __LINE__);
        $childs = $sectionDAO->getFromParent($branchId);
        if ($childs->size() > 0)
            return $this->error("section has childs, you must remove it before");

        $communityToSectionDAO = new OpenM_Book_Community_To_SectionDAO();
        OpenM_Log::debug("check if contains community child", __CLASS__, __METHOD__, __LINE__);
        $communities = $communityToSectionDAO->getFromSection($branchId);
        if ($communities->size() > 0)
            return $this->error("section contains community, you must remove all community child before");

        OpenM_Log::debug("remove the section from DAO", __CLASS__, __METHOD__, __LINE__);
        $sectionDAO->remove($branchId);

        return $this->ok();
    }

    public function addAdmin($userId) {
        if (!$this->isUserRegisteredAndAdmin())
            return $this->error;

        if (!OpenM_Book_Tool::isUserIdValid($userId))
            return $this->error("user must be in a valid format");

        $userDAO = new OpenM_Book_UserDAO();
        OpenM_Log::debug("search user in DAO", __CLASS__, __METHOD__, __LINE__);
        $user = $userDAO->get($userId);
        if ($user == null)
            return $this->error("user not found");
        OpenM_Log::debug("user found in DAO", __CLASS__, __METHOD__, __LINE__);
        $adminDAO = new OpenM_Book_AdminDAO();
        OpenM_Log::debug("add user as admin in DAO", __CLASS__, __METHOD__, __LINE__);
        $adminDAO->create($user->get(OpenM_Book_UserDAO::UID));
        return $this->ok();
    }

    /**
     * OK
     * 
     * @param type $branchId
     * @return HashtableString
     */
    public function getBranchProperties($branchId) {
        if (!$this->isUserRegisteredAndAdmin())
            return $this->error;
        if (!OpenM_Book_Tool::isGroupIdValid($branchId))
            return $this->error("branchId must be in a valid format");

        $sectionDAO = new OpenM_Book_SectionDAO();
        OpenM_Log::debug("check if section exists in DAO", __CLASS__, __METHOD__, __LINE__);
        $section = $sectionDAO->get($branchId);
        if ($section == null)
            return $this->error("branch not found");

        return $this->ok()
                        ->put(self::RETURN_BRANCH_ID_PARAMETER, $section->get(OpenM_Book_SectionDAO::ID))
                        ->put(self::RETURN_BRANCH_NAME_PARAMETER, $section->get(OpenM_Book_SectionDAO::NAME))
                        ->put(self::RETURN_CONTAINS_ONLY_ONE_COMMUNITY_PARAMETER, $section->get(OpenM_Book_SectionDAO::ONLY_ONE_COMMUNITY))
                        ->put(self::RETURN_VALIDATION_REQUIRED_PARAMETER, $section->get(OpenM_Book_SectionDAO::VALIDATION_REQUIRED))
                        ->put(self::RETURN_MANAGE_PERIOD_PARAMETER, $section->get(OpenM_Book_SectionDAO::MANAGE_PERIOD))
                        ->put(self::RETURN_USER_CAN_REGISTER_PARAMETER, $section->get(OpenM_Book_SectionDAO::USER_CAN_REGISTER))
                        ->put(self::RETURN_USER_CAN_ADD_COMMUNITY_PARAMETER, $section->get(OpenM_Book_SectionDAO::USER_CAN_ADD_COMMUNITY))
                        ->put(self::RETURN_COMMUNITY_NAME_REGEXP_PARAMETER, $section->get(OpenM_Book_SectionDAO::REG_EXP));
    }

    /**
     * @todo check secondary options
     */
    public function setBranchProperty($branchId, $propertyName, $propertyValue) {
        if (!$this->isUserRegisteredAndAdmin())
            return $this->error;
        if (!OpenM_Book_Tool::isGroupIdValid($branchId))
            return $this->error("branchId must be in a valid format");

        $sectionDAO = new OpenM_Book_SectionDAO();
        OpenM_Log::debug("check if section exists in DAO", __CLASS__, __METHOD__, __LINE__);
        $section = $sectionDAO->get($branchId);
        if ($section == null)
            return $this->error("branch not found");

        OpenM_Log::debug("check if propertyValue make change", __CLASS__, __METHOD__, __LINE__);
        if ($section->get($this->getConvertorArray($propertyName)) . "" == "$propertyValue") {
            OpenM_Log::debug("no change", __CLASS__, __METHOD__, __LINE__);
            return $this->ok();
        }

        OpenM_Log::debug("check if propertyName is defined", __CLASS__, __METHOD__, __LINE__);
        if (!$section->containsKey($this->getConvertorArray($propertyName)))
            return $this->error("propertyName (='$propertyName') not defined");

        if ($propertyName == self::BRANCH_NAME_PARAMETER
                || $propertyName == self::COMMUNITY_NAME_REGEXP_PARAMETER) {
            if (!RegExp::preg("/^([a-zA-Z0-9]|[ \t])+$/", $propertyValue)
                    && (preg_match("/$propertyValue/", "test") === false))
                return $this->error("propertyName ('$propertyName') not in a valid format");
        }
        else if ($propertyName == self::CONTAINS_ONLY_ONE_COMMUNITY_PARAMETER
                || $propertyName == self::MANAGE_PERIOD_PARAMETER
                || $propertyName == self::USER_CAN_REGISTER_PARAMETER
                || $propertyName == self::VALIDATION_REQUIRED_PARAMETER
                || $propertyName == self::USER_CAN_ADD_COMMUNITY_PARAMETER) {
            if ((String::isString($propertyValue) && !RegExp::preg("/^(0|1)$/", $propertyValue))
                    || (is_numeric($propertyValue) && $propertyValue != 1 && $propertyValue != 0))
                return $this->error("propertyName (='$propertyName') not in a valid format: '0' OR '1'");
            else if (String::isString($propertyValue)) {
                OpenM_Log::debug("convert string to int", __CLASS__, __METHOD__, __LINE__);
                $propertyValue = intval("$propertyValue");
            }
        }
        else
            return $this->error("propertyName (='$propertyName') not correcly manage for now");


        OpenM_Log::debug("check if change respect rules", __CLASS__, __METHOD__, __LINE__);
        switch ($propertyName) {
            case self::CONTAINS_ONLY_ONE_COMMUNITY_PARAMETER:
                OpenM_Log::debug("check for CONTAINS_ONLY_ONE_COMMUNITY rules", __CLASS__, __METHOD__, __LINE__);
                if ($propertyValue == 1) {
                    OpenM_Log::debug("activate CONTAINS_ONLY_ONE_COMMUNITY", __CLASS__, __METHOD__, __LINE__);
                    $sectionChilds = $sectionDAO->getFromParent($section->get(OpenM_Book_SectionDAO::ID)->toInt());
                    if ($sectionChilds->size() > 1)
                        return $this->error("to put this branch on USER_CAN_ADD_COMMUNITY mode activated, "
                                        . "this branch mustn't have to have more than one branch child");
                    $e = $sectionChilds->keys();
                    if ($e->hasNext()) {
                        $sectionChild = $sectionChilds->get($e->next());
                        if ($sectionChild->get(OpenM_Book_SectionDAO::ONLY_ONE_COMMUNITY)->toInt() == OpenM_Book_SectionDAO::DESACTIVATED)
                            return $this->error("to put this branch on CONTAINS_ONLY_ONE_COMMUNITY mode activated, "
                                            . "this branch mustn't have to have any branch child on CONTAINS_ONLY_ONE_COMMUNITY mode desactivated");
                    }
                }else {
                    OpenM_Log::debug("desactivate CONTAINS_ONLY_ONE_COMMUNITY", __CLASS__, __METHOD__, __LINE__);
                    $sectionParent = $sectionDAO->get($section->get(OpenM_Book_SectionDAO::SECTION_PARENT_ID)->toInt());
                    if ($sectionParent->get(OpenM_Book_SectionDAO::ONLY_ONE_COMMUNITY)->toInt() == OpenM_Book_SectionDAO::DESACTIVATED)
                        return $this->error("to put this branch on CONTAINS_ONLY_ONE_COMMUNITY mode activated, "
                                        . "this branch mustn't have to be child of branch on CONTAINS_ONLY_ONE_COMMUNITY mode desactivated");
                }
                break;
            case self::MANAGE_PERIOD_PARAMETER:
                return $this->error("not manage for now");
                break;
            case self::USER_CAN_REGISTER_PARAMETER:
                return $this->error("not manage for now");
                break;
            case self::VALIDATION_REQUIRED_PARAMETER:
                return $this->error("not manage for now");
                break;
            case self::USER_CAN_ADD_COMMUNITY_PARAMETER:
                return $this->error("not manage for now");
                break;
            default:
                break;
        }
        
        OpenM_Log::debug("update property in DAO", __CLASS__, __METHOD__, __LINE__);
        $sectionDAO->update($branchId, $this->getConvertorArray($propertyName), $propertyValue);
        return $this->ok();
    }

    /**
     * OK
     */
    public function getAdmins() {
        if (!$this->isUserRegisteredAndAdmin())
            return $this->error;

        OpenM_Log::debug("search admins in DAO", __CLASS__, __METHOD__, __LINE__);
        $adminDAO = new OpenM_Book_AdminDAO();
        $admins = $adminDAO->getAll();
        OpenM_Log::debug($admins->size() . " admins found in DAO", __CLASS__, __METHOD__, __LINE__);
        $e = $admins->keys();
        $admin_list = new HashtableString();
        while ($e->hasNext()) {
            $key = $e->next();
            $admin = $admins->get($key);
            $admin_list->put($admin->get(OpenM_Book_UserDAO::ID), $admin->get(OpenM_Book_UserDAO::FIRST_NAME) . " " . $admin->get(OpenM_Book_UserDAO::LAST_NAME));
        }

        return $this->ok()->put(self::RETURN_ADMIN_LIST_PARAMETER, $admin_list);
    }

    /**
     * OK
     */
    public function removeAdmin($userId) {
        if (!$this->isUserRegisteredAndAdmin())
            return $this->error;

        if (!OpenM_Book_Tool::isUserIdValid($userId))
            return $this->error("user must be in a valid format");

        $userDAO = new OpenM_Book_UserDAO();
        OpenM_Log::debug("search user in DAO", __CLASS__, __METHOD__, __LINE__);
        $user = $userDAO->get($userId);
        if ($user == null)
            return $this->error("user not found");
        OpenM_Log::debug("user found in DAO", __CLASS__, __METHOD__, __LINE__);
        $adminDAO = new OpenM_Book_AdminDAO();
        OpenM_Log::debug("check count of admin", __CLASS__, __METHOD__, __LINE__);
        if ($adminDAO->getCount() < 2)
            return $this->error("admin list must contains at least one admin, you cant remove the last one");

        OpenM_Log::debug("remove user as admin in DAO", __CLASS__, __METHOD__, __LINE__);
        $adminDAO->remove($user->get(OpenM_Book_UserDAO::UID));
        return $this->ok();
    }

    /**
     * OK
     */
    public function install() {
        if (!$this->isUserRegistered())
            return $this->error;
        $adminDAO = new OpenM_Book_AdminDAO();
        if ($adminDAO->containsOneAtLeast())
            return $this->error("no administrator must be registered before launching install");
        $adminDAO->create($this->getManager()->getID());
        return $this->ok();
    }

}

?>