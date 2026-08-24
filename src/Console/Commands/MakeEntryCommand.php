<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Entries\CreateEntry;
use LaraCeemes\Models\Blueprint;

final class MakeEntryCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-entry
                            {--collection= : Collection handle}
                            {--blueprint= : Blueprint handle}
                            {--title= : Entry title}
                            {--slug= : Entry slug}
                            {--status=draft : draft or published}
                            {--data= : JSON object containing Blueprint values}';

    protected $description = 'Create a Lara Ceemes Entry';

    public function handle(CreateEntry $action): int
    {
        $collectionHandle = $this->stringOptionOrAsk('collection', 'Collection handle');
        $blueprintHandle = $this->stringOptionOrAsk('blueprint', 'Blueprint handle');
        $blueprint = Blueprint::query()
            ->where('handle', $blueprintHandle)
            ->whereHas('collection', fn ($query) => $query->where('handle', $collectionHandle))
            ->firstOrFail();
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

        $entry = $action->execute($blueprint, $payload);

        $this->components->success("Entry [{$entry->title}] created.");

        return self::SUCCESS;
    }
}
