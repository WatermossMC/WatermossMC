<?php

require_once 'src/WatermossMC/Server.php';

use WatermossMC\Server;
$server = new Server('server.properties');
$server->start();
