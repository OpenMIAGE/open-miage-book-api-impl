<?php

Import::php("util.OpenM_Log");
Import::php("OpenM-Book.api.Impl.DAO.*");
Import::php("OpenM-Book.api.OpenM_Book_Tool");
Import::php("OpenM-Services.api.Impl.OpenM_ServiceSSOImpl");

/**
 * Description of OpenM_BookCommonsImpl
 *
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

}

?>