<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\SiteContext;

final class MakeContentCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-content
                            {--set= : Set handle}
                            {--title= : Content title}
                            {--slug= : Content slug}
                            {--uri= : Public URI inside the selected Site}
                            {--site= : Site handle, defaults to the default Site}
                            {--status=draft : draft or published}
                            {--data= : JSON object containing Set Field values}';

    protected $description = 'Create Content inside a Lara Ceemes Set';

    public function handle(CreateContent $action, SiteContext $sites): int
    {
        if ($site = $this->stringOption('site')) {
            $sites->useHandle($site, enabledOnly: true);
        }
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
        if (is_string($this->option('uri'))) {
            $payload['uri'] = $this->option('uri');
        }

        $content = $action->execute($set, $payload);
        $this->components->success("Content [{$content->title}] created.");

        return self::SUCCESS;
    }
}
