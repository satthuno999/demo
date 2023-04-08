<?php

require_once __DIR__ . '/bootstrap_helper.php';

require_once __DIR__ . '/../../../tests/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Fix for "Autoload path not allowed: .../demo/tests/testcase.php"
\OC_App::loadApp('demo');
