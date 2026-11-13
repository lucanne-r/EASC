<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityImportSeeder extends Seeder
{
    // Ganti kalau mau diisi ke project lain, misalnya 'kakotora-line-4'
    private string $slug = 'kakotora-line-1';

    public function run(): void
    {
        $project = DB::table('projects')->where('slug', $this->slug)->first();
        if (! $project) {
            $this->command->error("Project {$this->slug} belum ada. Buat dulu lewat halaman awal.");
            return;
        }

        $h = fopen(database_path('data/control_activities.csv'), 'r');
        $head = array_map(fn ($x) => trim($x, "\xEF\xBB\xBF \t\n\r"), fgetcsv($h));
        $n = 0;
        while (($row = fgetcsv($h)) !== false) {
            if (count($row) !== count($head)) continue;
            $r = array_combine($head, $row);
            if (trim($r['code']) === '') continue;

            Activity::updateOrCreate(
                ['project_slug' => $this->slug, 'code' => trim($r['code'])],
                [
                    'project' => $project->name ?? $this->slug,
                    'machine_area' => $r['machine_area'],
                    'category' => $r['category'],
                    'priority' => $r['priority'],
                    'activity' => $r['activity'],
                    'pic' => $r['pic'],
                    'status' => $r['status'] ?: 'Open',
                ]
            );
            $n++;
        }
        fclose($h);
        $this->command->info("$n aktivitas diimpor ke {$this->slug}.");
    }
}