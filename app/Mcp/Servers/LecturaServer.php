<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\LectureStatusTool;
use App\Mcp\Tools\SearchLecturesTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Lectura Server')]
#[Version('0.1.0')]
#[Instructions('
    Lectura turns recorded lectures into structured notes.
    Use search_lectures to find what a lecture covered,
    and lecture_status to check whether
    a recording has finished processing.
')]
class LecturaServer extends Server
{
    protected array $tools = [
        SearchLecturesTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
