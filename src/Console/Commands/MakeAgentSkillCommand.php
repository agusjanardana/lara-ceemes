<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class MakeAgentSkillCommand extends Command
{
    protected $signature = 'ceemes:make-agent-skill
                            {--path=.agents/skills/lara-ceemes : Project-relative destination directory}
                            {--force : Overwrite Lara Ceemes skill files that already exist}';

    protected $description = 'Generate a project-local AI agent skill for Lara Ceemes development';

    public function handle(Filesystem $files): int
    {
        $pathOption = $this->option('path');
        if (! is_string($pathOption)) {
            $this->components->error('The --path option must be a project-relative string.');

            return self::FAILURE;
        }

        $relativePath = trim($pathOption, ' \\/');

        if ($relativePath === '' || str_contains($relativePath, '..') || preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $relativePath) === 1) {
            $this->components->error('The --path option must be a safe path relative to the Laravel project.');

            return self::FAILURE;
        }

        $source = dirname(__DIR__, 3).'/resources/agent-skills/lara-ceemes';
        $destination = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath));
        $skillFiles = [
            'SKILL.md',
            'references/concepts.md',
            'references/commands-and-api.md',
            'references/best-practices.md',
        ];

        if (! $files->isDirectory($source)) {
            $this->components->error('The Lara Ceemes agent skill template is missing from the package.');

            return self::FAILURE;
        }

        if (! (bool) $this->option('force')) {
            foreach ($skillFiles as $skillFile) {
                if ($files->exists($destination.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $skillFile))) {
                    $this->components->error("Agent skill already exists at [{$relativePath}]. Use --force to update it.");

                    return self::FAILURE;
                }
            }
        }

        foreach ($skillFiles as $skillFile) {
            $sourceFile = $source.'/'.$skillFile;
            $destinationFile = $destination.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $skillFile);
            $files->ensureDirectoryExists(dirname($destinationFile));
            $files->copy($sourceFile, $destinationFile);
        }

        $this->components->success("Lara Ceemes agent skill generated at [{$relativePath}/SKILL.md].");
        $this->components->info('Commit the generated skill so every coding agent working on this project can use it.');

        return self::SUCCESS;
    }
}
