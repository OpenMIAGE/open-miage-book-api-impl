<?php

require_once 'src.php';
require_once 'lib.php';

define('OpenM_SSOClientInstaller_CONFIG', "install.config.properties");
Import::php("OpenM-SSO.client.OpenM_SSOClientInstaller");
OpenM_SSOClientInstaller::step2();
?>