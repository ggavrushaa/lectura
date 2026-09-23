<?php

use App\Mcp\Servers\LecturaServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('lectura', LecturaServer::class);
