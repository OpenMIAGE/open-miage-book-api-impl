<?php

require_once 'src.php';
require_once 'lib.php';
require_once 'config.php';

Import::php("OpenM-SSO.api.Impl.OpenM_SSOInstaller");
OpenM_SSOInstaller::main();
?>