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
 * @author Nicolas Rouzeaud & Gaël SAUNIER
 */
class OpenM_Book_UserImpl extends OpenM_BookCommonsImpl implements OpenM_Book_User {

    /**
     * @todo Dev & test
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

        $userPropertyValueDAO = new OpenM_Book_User_Property_ValueDAO();
        $newValueId = $userPropertyValueDAO->create($propertyId, $propertyValue, $user->get(OpenM_Book_UserDAO::ID)->toInt());

        if (!$newValueId)
            return $this->error("PropertyId doesn't exist");
        $newValueId = $newValueId->get(OpenM_Book_User_Property_ValueDAO::ID);
        OpenM_Log::debug("property : $propertyId with value : $propertyValue is inserted with new id : $newValueId", __CLASS__, __METHOD__, __LINE__);
        //maj updatetime de l'utilisateur

        $userDAO->updateTime($user->get(OpenM_Book_UserDAO::ID)->toInt());

        OpenM_Log::debug("END addPropertyValue", __CLASS__, __METHOD__, __LINE__);
        return $this->ok()->put(self::RETURN_USER_PROPERTY_VALUE_ID_PARAMETER, $newValueId);
    }

    /**
     * @todo check and test
     */
    public function getPropertyVisibility($propertyValueId) {
        if (!$this->isIdValid($propertyValueId))
            return $this->error("propertyValueId must be an int");

        if ($this->isUserRegistered())
            $user = $this->user;
        else
            return $this->error;

        $userPorpertyValueGroupDAO = new OpenM_Book_User_Property_Value_VisibilityDAO();
        $groupsVisibilities = $userPorpertyValueGroupDAO->get($user->get(OpenM_Book_UserDAO::ID)->toInt(), $propertyValueId);
        OpenM_Log::debug("recuperation groupVisibility OK", __CLASS__, __METHOD__, __LINE__);
        $retour = new HashtableString();
        $i = 0;
        $enu = $groupsVisibilities->enum();
        while ($enu->hasNext()) {
            $row = $enu->next();
            $hashtableTMP = new HashtableString;
            $hashtableTMP->put(OpenM_Groups::RETURN_GROUP_ID_PARAMETER, $row->get(OpenM_Book_User_Property_Value_VisibilityDAO::GROUP_VISIBILITY_ID));
            $hashtableTMP->put(OpenM_Groups::RETURN_GROUP_NAME_PARAMETER, $row->get(OpenM_Book_GroupDAO::NAME));
            $retour->put($i, $hashtableTMP);
            $i++;
        }
        OpenM_Log::debug("END getPropertyVisibility", __CLASS__, __METHOD__, __LINE__);
        return $this->ok()->put(self::RETURN_USER_GROUP_PROPERTY_VISIBILITY_LIST_PARAMETER, $retour);
    }

    /**
     * @todo test
     */
    public function setPropertyValue($propertyValueId, $propertyValue) {
        if (!RegExp::preg("/^-?[0-9]+$/", $propertyValueId) )
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
                $retour = $propertyValueDAO->get($userId, $propertyValueId);
                if ($retour->size() == 0)
                    return $this->error(self::RETURN_ERROR_MESSAGE_PROPERTY_NOTFOUND_VALUE);
                OpenM_Log::debug("property value found in DAO", __CLASS__, __METHOD__, __LINE__);
                $propertyValueDAO->update($propertyValueId, $userId, $propertyValue);
                OpenM_Log::debug("property updated in DAO", __CLASS__, __METHOD__, __LINE__);
                break;
        }
        return $this->ok();
    }

    /**
     * @todo check dev & test
     */
    public function setPropertyVisibility($propertyValueId, $visibilityGroupJSONList) {
        OpenM_Log::debug("START setPropertyVisibility", __CLASS__, __METHOD__, __LINE__);

        if (!$this->isIdValid($propertyValueId))
            return $this->error("PropertyValueId must be an integer");
        if (!String::isString($visibilityGroupJSONList))
            return $this->error("visibilityGroupJSONList must be a string");
        //récupération des ID de group
        $groupVisibilityIdArray = OpenM_MapConvertor::JSONToArray($visibilityGroupJSONList);
        if ($groupVisibilityIdArray) {
            foreach ($groupVisibilityIdArray as $value) {
                if (!OpenM_Book_Tool::isGroupIdValid($value))
                    return $this->error("visibilityGroupJSONList must contains only numeric");
            }
            OpenM_Log::debug("tous les groupsId sont OK", __CLASS__, __METHOD__, __LINE__);
        } else {
            OpenM_Log::debug("mauvais format de la liste visibilityGroupJSONList", __CLASS__, __METHOD__, __LINE__);
            return $this->error(self::RETURN_ERROR_MESSAGE_LIST_JSON_BAD_FORMAT_VALUE . " - visibilityGroupJSONLIST ");
        }

        if ($this->isUserRegistered())
            $user = $this->user;
        else
            return $this->error;

        //verrification de l'appartenance de la valeur à l'utilisateur
        $propertyValueDAO = new OpenM_Book_User_Property_ValueDAO();
        $retour = $propertyValueDAO->get($user->get(OpenM_Book_UserDAO::ID)->toInt(), $propertyValueId);
        if ($retour->size() == 0)
            return $this->error(self::RETURN_ERROR_MESSAGE_PROPERTY_NOTFOUND_VALUE);

        $groupsValueDAO = new OpenM_Book_User_Property_Value_VisibilityDAO();
        $groupsValueDAO->delete($propertyValueId);
        foreach ($groupVisibilityIdArray as $value) {
            $groupsValueDAO->create($propertyValueId, $value);
        }

        OpenM_Log::debug("END setPropertyVisibility", __CLASS__, __METHOD__, __LINE__);
        return $this->ok();
    }

    /**
     * @todo check dev & test
     */
    public function removePropertyValue($propertyValueId) {
        if (!$this->isIdValid($propertyValueId))
            return $this->error("propertyValueId must be a int");

        if ($this->isUserRegistered())
            $user = $this->user;
        else
            return $this->error;

        $userId = $user->get(OpenM_Book_UserDAO::ID)->toInt();
        //test appartenance prop
        $propValueDAO = new OpenM_Book_User_Property_ValueDAO();
        $property = $propValueDAO->get($userId, $propertyValueId);
        if ($property->size() == 0)
            return $this->error(self::RETURN_ERROR_MESSAGE_PROPERTY_NOTFOUND_VALUE);

        OpenM_Log::debug("property $propertyValueId owned by user", __CLASS__, __METHOD__, __LINE__);
        $propValueDAO->delete($propertyValueId, $userId);
        OpenM_Log::debug("property deleted", __CLASS__, __METHOD__, __LINE__);
        $valueGroupDAO = new OpenM_Book_User_Property_Value_VisibilityDAO();
        $valueGroupDAO->delete($propertyValueId);
        OpenM_Log::debug("group visibility deleted", __CLASS__, __METHOD__, __LINE__);
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
                $values = $userPropertiesValueDAO->getProperties($userId);
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
     * 
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