<?php

require_once 'src.php';
require_once 'lib.php';

define('OpenM_SSOClientInstaller_CONFIG', "install.config.properties");
Import::php("OpenM-SSO.client.OpenM_SSOClientInstaller");
OpenM_SSOClientInstaller::step1();
?>
<p>
    launch <a href="install2.php">step2</a> (second step of installation).
</p>