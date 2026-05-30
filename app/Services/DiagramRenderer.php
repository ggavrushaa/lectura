<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class DiagramRenderer
{
    private string $mmdc;

    public function __construct()
    {
        $this->mmdc = config('lectura.mmdc');
    }

    /** @return list<string> */
    public function renderCommand(string $input, string $output): array
    {
        return [$this->mmdc, '-i', $input, '-o', $output, '-b', 'transparent'];
    }

    /**
     * @return array{svg:string,png:string}|null  null если схема пустая/битая
     */
    public function render(string $mermaid, string $outDir, string $name): ?array
    {
        $mermaid = trim($mermaid);
        if ($mermaid === '') {
            return null;
        }

        if (! is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }

        $input = "{$outDir}/{$name}.mmd";
        $svg = "{$outDir}/{$name}.svg";
        $png = "{$outDir}/{$name}.png";
        file_put_contents($input, $mermaid);

        if (! $this->run($this->renderCommand($input, $svg)) || ! is_file($svg)) {
            return null; // битая схема — graceful skip
        }
        $this->run($this->renderCommand($input, $png)); // png опционален

        return [
            'svg' => $svg,
            'png' => is_file($png) ? $png : '',
        ];
    }

    private function run(array $cmd): bool
    {
        $process = new Process($cmd);
        $process->setTimeout(120);
        $process->run();

        return $process->isSuccessful();
    }
}
