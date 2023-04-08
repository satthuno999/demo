<?php

require_once __DIR__ . '/bootstrap_helper.php';

resetEnvironmentToBackup('plain', false);

require_once __DIR__ . '/../../../tests/bootstrap.php';

// Fix for "Autoload path not allowed: .../demo/tests/testcase.php"
\OC_App::loadApp('demo');
