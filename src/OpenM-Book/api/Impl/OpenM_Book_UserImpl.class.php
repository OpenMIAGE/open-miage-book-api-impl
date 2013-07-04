<?php

Import::php("OpenM-Book.api.OpenM_Book_User");
Import::php("OpenM-Book.api.Impl.OpenM_Book_AdminImpl");
Import::php("OpenM-Book.api.OpenM_Book_Moderator");
Import::php("OpenM-Book.api.Impl.OpenM_BookCommonsImpl");
Import::php("OpenM-Mail.api.OpenM_MailTool");

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
 * @author Nicolas Rouzeaud & Gaël SAUNIER
 */
class OpenM_Book_UserImpl extends OpenM_BookCommonsImpl implements OpenM_Book_User {

    /**
     * OK
     */
    public function addPropertyValue($propertyId, $propertyValue) {
        if (!$this->isIdValid($propertyId))
            return $this->error("PropertyId must be an integer");
        if (!String::isString($propertyValue))
            return $this->error("PropertyValue must be a string");

        if ($this->isUserRegistered())
            $user = $this->user;
        else
            return $this->error;

        $propertyDAO = new OpenM_Book_User_PropertyDAO();
        OpenM_Log::debug("check if propertyId exist in DAO", __CLASS__, __METHOD__, __LINE__);
        $property = $propertyDAO->getById($propertyId);
        if ($property == null)
            return $this->error("propertyId not found");

        OpenM_Log::debug("propertyId exist in DAO", __CLASS__, __METHOD__, __LINE__);
        $userPropertyValueDAO = new OpenM_Book_User_Property_ValueDAO();
        OpenM_Log::debug("create property value in DAO", __CLASS__, __METHOD__, __LINE__);
        $value = $userPropertyValueDAO->create($propertyId, $propertyValue, $user->get(OpenM_Book_UserDAO::ID)->toInt());

        $userDAO = new OpenM_Book_UserDAO();
        OpenM_Log::debug("update user update time in DAO", __CLASS__, __METHOD__, __LINE__);
        $userDAO->updateTime($user->get(OpenM_Book_UserDAO::ID)->toInt());

        return $this->ok()->put(self::RETURN_USER_PROPERTY_VALUE_ID_PARAMETER, $value->get(OpenM_Book_User_Property_ValueDAO::ID));
    }

    /**
     * @todo
     */
    public function getPropertyVisibility($propertyValueId) {
        return $this->notImplemented();
    }

    /**
     * OK
     */
    public function setPropertyValue($propertyValueId, $propertyValue) {
        if (!RegExp::preg("/^-?[0-9]+$/", $propertyValueId))
            return $this->error("propertyValueId must be an int");
        if (!String::isString($propertyValue))
            return $this->error("propertyValue must be a string");

        if ($this->isUserRegistered())
            $user = $this->user;
        else
            return $this->error;

        $userDAO = new OpenM_Book_UserDAO();
        $userId = $user->get(OpenM_Book_UserDAO::ID)->toInt();

        switch ($propertyValueId) {
            case self::FIRST_NAME_PROPERTY_VALUE_ID :
                if (!RegExp::preg("/^[a-zA-Z]([a-zA-Z]|[ \t])+[a-zA-Z]?$/", OpenM_Book_Tool::strlwr($propertyValue)))
                    return $this->error("firstName in bad format");
                $userDAO->update($userId, OpenM_Book_UserDAO::FIRST_NAME, $propertyValue);
                break;
            case self::LAST_NAME_PROPERTY_VALUE_ID :
                if (!RegExp::preg("/^[a-zA-Z]([a-zA-Z]|[ \t])+[a-zA-Z]?$/", OpenM_Book_Tool::strlwr($propertyValue)))
                    return $this->error("lastName in bad format");
                $userDAO->update($userId, OpenM_Book_UserDAO::LAST_NAME, $propertyValue);
                break;
            case self::PHOTO_ID_PROPERTY_VALUE_ID :
                if (!$this->isIdValid($propertyValue))
                    return $this->error("Photo ID not valid");
                $userDAO->update($userId, OpenM_Book_UserDAO::PHOTO, $propertyValue);
                break;
            case self::DEFAULT_EMAIL_PROPERTY_VALUE_ID :
                if (!OpenM_MailTool::isEMailValid($propertyValue))
                    return $this->error("mail not valid");
                $userDAO->update($userId, OpenM_Book_UserDAO::DEFAULT_MAIL, $propertyValue);
                break;
            case self::BIRTHDAY_ID_PROPERTY_VALUE_ID :
                $date = new Date("$propertyValue");
                if ($date->plus(Delay::years(self::AGE_LIMIT_TO_REGISTER))->compareTo(Date::now()) < 0)
                    return $this->error("you must be older than " . self::AGE_LIMIT_TO_REGISTER . " years old");
                $userDAO->update($userId, OpenM_Book_User::BIRTHDAY_ID_PROPERTY_VALUE_ID, $propertyValue);
                break;
            default:
                OpenM_Log::debug("default property treatment", __CLASS__, __METHOD__, __LINE__);
                $propertyValueDAO = new OpenM_Book_User_Property_ValueDAO();
                OpenM_Log::debug("search property value in DAO", __CLASS__, __METHOD__, __LINE__);
                $userPropertyValue = $propertyValueDAO->get($propertyValueId);
                if ($userPropertyValue->size() == 0)
                    return $this->error(self::RETURN_ERROR_MESSAGE_PROPERTY_NOTFOUND_VALUE);
                OpenM_Log::debug("check if property is property of user", __CLASS__, __METHOD__, __LINE__);
                if ($userPropertyValue->get(OpenM_Book_User_Property_ValueDAO::USER_ID) != $this->user->get(OpenM_Book_UserDAO::ID))
                    return $this->error("it's not your property");
                OpenM_Log::debug("property value found in DAO", __CLASS__, __METHOD__, __LINE__);
                $propertyValueDAO->update($propertyValueId, $propertyValue);
                OpenM_Log::debug("property updated in DAO", __CLASS__, __METHOD__, __LINE__);
                break;
        }
        return $this->ok();
    }

    /**
     * @todo
     */
    public function setPropertyVisibility($propertyValueId, $visibilityGroupJSONList) {
        return $this->notImplemented();
    }

    /**
     * OK
     */
    public function removePropertyValue($propertyValueId) {
        if (!$this->isIdValid($propertyValueId))
            return $this->error("propertyValueId must be a int");

        if ($this->isUserRegistered())
            $user = $this->user;
        else
            return $this->error;

        $userId = $user->get(OpenM_Book_UserDAO::ID)->toInt();
        $propertyValueDAO = new OpenM_Book_User_Property_ValueDAO();
        $propertyValue = $propertyValueDAO->get($propertyValueId);
        if ($propertyValue->size() == 0)
            return $this->error(self::RETURN_ERROR_MESSAGE_PROPERTY_NOTFOUND_VALUE);
        OpenM_Log::debug("check if it's property of user", __CLASS__, __METHOD__, __LINE__);
        if ($propertyValue->get(OpenM_Book_User_Property_ValueDAO::USER_ID) != $this->user->get(OpenM_Book_UserDAO::ID))
            return $this->error("it's not your property");

        OpenM_Log::debug("property owned by user", __CLASS__, __METHOD__, __LINE__);
        $propertyValueDAO->delete($propertyValueId);
        OpenM_Log::debug("property deleted", __CLASS__, __METHOD__, __LINE__);        
        $userDAO = new OpenM_Book_UserDAO();
        $userDAO->updateTime($userId);
        return $this->ok();
    }

    public function buildMyData() {
        $this->notImplemented();
    }

    public function unRegisterMe() {
        $this->notImplemented();
    }

    /**
     * OK
     */
    public function getUserProperties($userId = null, $basicOnly = null) {
        if (!String::isStringOrNull($userId))
            return $this->error("userId must be a string");
        if ($userId != null && !OpenM_Book_Tool::isUserIdValid($userId))
            return $this->error("userId must be in a valid format");
        if (!String::isStringOrNull($basicOnly) && !is_bool($basicOnly))
            return $this->error("basicOnly must be a string or a boolean");
        if ($basicOnly == null || (is_bool($basicOnly) && $basicOnly) || $basicOnly == self::TRUE_PARAMETER_VALUE)
            $basicOnly = self::TRUE_PARAMETER_VALUE;
        else if ($basicOnly != self::TRUE_PARAMETER_VALUE)
            $basicOnly = self::FALSE_PARAMETER_VALUE;

        if ($this->isUserRegistered())
            $user = $this->user;
        else
            return $this->error;

        $userIdCalling = $user->get(OpenM_Book_UserDAO::ID);

        if ($userId == null) {
            OpenM_Log::debug("user calling is the targeted user", __CLASS__, __METHOD__, __LINE__);
            $userId = $userIdCalling;
        } else if ($userId == $userIdCalling) {
            OpenM_Log::debug("the targeted user is the user that calling method", __CLASS__, __METHOD__, __LINE__);
        } else {
            OpenM_Log::debug("search the targeted user in DAO", __CLASS__, __METHOD__, __LINE__);
            $userDAO = new OpenM_Book_UserDAO();
            $user = $userDAO->get($userId);
            if ($user == null)
                return $this->error(self::RETURN_ERROR_MESSAGE_USER_NOT_FOUND_VALUE);
            OpenM_Log::debug("the targeted user is found in DAO", __CLASS__, __METHOD__, __LINE__);
            $userId = $user->get(OpenM_Book_UserDAO::ID);
        }

        $return = $this->ok();
        $isUserCalling = ($userId == $userIdCalling);

        if ($isUserCalling) {
            $adminDAO = new OpenM_Book_AdminDAO();
            OpenM_Log::debug("Check if user is admin", __CLASS__, __METHOD__, __LINE__);
            $admin = $adminDAO->get($this->user->get(OpenM_Book_UserDAO::UID));
            if ($admin != null) {
                OpenM_Log::debug("user is admin", __CLASS__, __METHOD__, __LINE__);
                $return->put(self::RETURN_USER_IS_ADMIN_PARAMETER, self::TRUE_PARAMETER_VALUE);
            }
        }

        $propertyList = new HashtableString();
        if ($basicOnly === self::FALSE_PARAMETER_VALUE) {
            OpenM_Log::debug("Check user property in DAO", __CLASS__, __METHOD__, __LINE__);
            $userPropertiesValueDAO = new OpenM_Book_User_Property_ValueDAO();

            if ($isUserCalling) {
                OpenM_Log::debug("search my Properties in DAO", __CLASS__, __METHOD__, __LINE__);
                $values = $userPropertiesValueDAO->getFromUser($userId);
            } else {
                OpenM_Log::debug("search Properties from user in DAO", __CLASS__, __METHOD__, __LINE__);
                $values = $userPropertiesValueDAO->getFromUser($userId, $userIdCalling);
            }

            if ($values != null) {
                OpenM_Log::debug("Properties found in DAO", __CLASS__, __METHOD__, __LINE__);
                $e = $values->keys();
                $i = 0;
                while ($e->hasNext()) {
                    $key = $e->next();
                    $value = $values->get($key);
                    $propertyValue = new HashtableString();
                    $propertyValue->put(self::RETURN_USER_PROPERTY_ID_PARAMETER, $value->get(OpenM_Book_User_PropertyDAO::ID)->toInt());
                    $propertyValue->put(self::RETURN_USER_PROPERTY_NAME_PARAMETER, $value->get(OpenM_Book_User_PropertyDAO::NAME));
                    if ($value->get(OpenM_Book_User_Property_ValueDAO::ID) != "") {
                        $propertyValue->put(self::RETURN_USER_PROPERTY_VALUE_ID_PARAMETER, $value->get(OpenM_Book_User_Property_ValueDAO::ID)->toInt());
                        $propertyValue->put(self::RETURN_USER_PROPERTY_VALUE_PARAMETER, $value->get(OpenM_Book_User_Property_ValueDAO::VALUE));
                    }
                    $propertyList->put($i, $propertyValue);
                    $i++;
                }
                if ($propertyList->size() > 0)
                    $return->put(self::RETURN_USER_PROPERTY_LIST_PARAMETER, $propertyList);
            }
            else
                OpenM_Log::debug("Property not found in DAO", __CLASS__, __METHOD__, __LINE__);
        }
        return $return
                        ->put(self::RETURN_USER_ID_PARAMETER, $user->get(OpenM_Book_UserDAO::ID))
                        ->put(self::RETURN_USER_FIRST_NAME_PARAMETER, $user->get(OpenM_Book_UserDAO::FIRST_NAME))
                        ->put(self::RETURN_USER_LAST_NAME_PARAMETER, $user->get(OpenM_Book_UserDAO::LAST_NAME));
    }

    /**
     * OK
     */
    public function registerMe($firstName, $lastName, $birthDay) {
        if (!String::isString($firstName))
            return $this->error("firstName must be a string");
        if (!RegExp::preg("/^[a-zA-Z]([a-zA-Z]|[ \t])+[a-zA-Z]?$/", OpenM_Book_Tool::strlwr($firstName)))
            return $this->error("firstName in bad format");
        if (!String::isString($lastName))
            return $this->error("lastName must be a string");
        if (!RegExp::preg("/^[a-zA-Z]([a-zA-Z]|[ \t])+[a-zA-Z]?$/", OpenM_Book_Tool::strlwr($lastName)))
            return $this->error("lastName in bad format");
        if (!String::isString($birthDay) && !is_numeric($birthDay))
            return $this->error("birthDay must be a string or a numeric");
        if ($birthDay instanceof String)
            $birthDay = "$birthDay";
        $birthDay = intval($birthDay);
        $birthDayDate = new Date($birthDay);
        if ($birthDayDate->compareTo(Date::now()->less(Delay::years(self::AGE_LIMIT_TO_REGISTER))) > 0)
            return $this->error(self::RETURN_ERROR_MESSAGE_YOU_ARE_TOO_YOUNG_VALUE);

        $userUID = $this->getManager()->getID();

        $userDAO = new OpenM_Book_UserDAO();
        OpenM_Log::debug("search user in DAO", __CLASS__, __METHOD__, __LINE__);
        $user = $userDAO->getFromUID($userUID);

        if ($user != null)
            return $this->error(self::RETURN_ERROR_MESSAGE_USER_ALREADY_REGISTERED_VALUE);
        OpenM_Log::debug("user not found in DAO", __CLASS__, __METHOD__, __LINE__);

        $groupDAO = new OpenM_Book_GroupDAO();
        OpenM_Log::debug("create personal group in DAO", __CLASS__, __METHOD__, __LINE__);
        $group = $groupDAO->create("personnal");
        OpenM_Log::debug("create user in DAO", __CLASS__, __METHOD__, __LINE__);
        $newUser = $userDAO->create($userUID, $firstName, $lastName, $birthDay, $group->get(OpenM_Book_GroupDAO::ID));

        OpenM_Log::debug("index user", __CLASS__, __METHOD__, __LINE__);
        $searchDAO = new OpenM_Book_SearchDAO();
        $searchDAO->index($firstName . " " . $lastName, $newUser->get(OpenM_Book_UserDAO::ID), OpenM_Book_SearchDAO::TYPE_USER);
        return $this->ok();
    }

}

?>