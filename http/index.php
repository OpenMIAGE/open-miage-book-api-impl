<?php

require_once 'config.php';
require_once 'src.php';
require_once 'lib.php';

Import::php("OpenM-Controller.api.OpenM_RESTDefaultServer");
OpenM_RESTDefaultServer::handle();
?>