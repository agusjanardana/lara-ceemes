<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Models\Set;

final class MakeContentCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-content
                            {--set= : Set handle}
                            {--title= : Content title}
                            {--slug= : Content slug}
                            {--status=draft : draft or published}
                            {--data= : JSON object containing Set Field values}';

    protected $description = 'Create Content inside a Lara Ceemes Set';

    public function handle(CreateContent $action): int
    {
        $setHandle = $this->stringOptionOrAsk('set', 'Set handle');
        $set = Set::query()->where('handle', $setHandle)->firstOrFail();
        $data = json_decode($this->stringOption('data') ?? '{}', true);

        if (! is_array($data)) {
            throw ValidationException::withMessages(['data' => 'The --data option must contain a JSON object.']);
        }

        $payload = [
            'title' => $this->option('title') ?: $this->ask('Title'),
            'status' => $this->stringOption('status') ?? 'draft',
            'data' => $data,
        ];

        if (is_string($this->option('slug'))) {
            $payload['slug'] = $this->option('slug');
        }

        $content = $action->execute($set, $payload);
        $this->components->success("Content [{$content->title}] created.");

        return self::SUCCESS;
    }
}
