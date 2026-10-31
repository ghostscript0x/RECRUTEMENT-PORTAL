<?php

namespace Database\Seeders;

use App\Models\LocalGovernment;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeds all 36 Nigerian states plus the FCT and their 774 LGAs from a local
 * JSON dataset so the location selects work offline.
 */
class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/nigeria-states.json');

        if (! File::exists($path)) {
            $this->command?->warn('Geography dataset missing: '.$path);

            return;
        }

        $states = json_decode(File::get($path), true);

        foreach ($states as $state) {
            $model = State::updateOrCreate(
                ['slug' => $state['alias']],
                ['name' => $state['state'] ?? strtoupper($state['alias'])],
            );

            $rows = collect($state['lgas'] ?? [])->map(fn ($name) => [
                'state_id' => $model->id,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            if ($rows !== []) {
                LocalGovernment::upsert($rows, ['state_id', 'name'], ['updated_at']);
            }
        }

        $this->command?->info('Seeded '.State::count().' states and '.LocalGovernment::count().' LGAs.');
    }
}
