<?php

Import::php("util.OpenM_Log");
Import::php("OpenM-Book.api.Impl.DAO.*");
Import::php("OpenM-Book.api.OpenM_Book_Tool");
Import::php("OpenM-Services.api.Impl.OpenM_ServiceSSOImpl");

/**
 * 
 * Description of OpenM_BookCommonsImpl
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
 * @author Gaël Saunier
 */
class OpenM_BookCommonsImpl extends OpenM_ServiceSSOImpl {
    
    protected $user;
    protected $error;

    protected function isUserRegistered() {
        if ($this->user instanceof HashtableString)
            return true;
        $userUID = $this->getManager()->getID();
        $userDAO = new OpenM_Book_UserDAO();
        OpenM_Log::debug("Search User in DAO", __CLASS__, __METHOD__, __LINE__);
        $this->user = $userDAO->getFromUID($userUID);
        if ($this->user == null) {
            $this->error = $this->error(self::RETURN_ERROR_MESSAGE_USER_NOT_REGISTERED_VALUE);
            return false;
        } else {
            OpenM_Log::debug("User found in DAO", __CLASS__, __METHOD__, __LINE__);
            OpenM_Log::debug("check if user is activated", __CLASS__, __METHOD__, __LINE__);
            if ($this->user->get(OpenM_Book_UserDAO::ACTIVATED) != OpenM_Book_UserDAO::ACTIVE)
                $this->error = $this->error(self::RETURN_ERROR_MESSAGE_USER_NOT_ACTIVATED_VALUE);
            OpenM_Log::debug("user is activated", __CLASS__, __METHOD__, __LINE__);
            return true;
        }
    }

    
    protected function isIdValid($propertyId) {
        if (is_int($propertyId))
            return true;
        if (!String::isString($propertyId))
            return false;
        return RegExp::preg(OpenM_Groups::ID_PARAMETER_PATERN, "$propertyId");
    }

}

?>