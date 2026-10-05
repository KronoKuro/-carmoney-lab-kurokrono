<?php

declare(strict_types=1);

use CarMoneyLab\AppFactory;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

AppFactory::create()->run();
