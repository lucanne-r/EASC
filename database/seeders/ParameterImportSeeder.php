<?php

namespace Database\Seeders;

use App\Models\DcCode;
use App\Models\Project;
use App\Models\WeightStandard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParameterImportSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::where('slug', 'kakotora-line-1')->firstOrFail();

        $std = $this->readCsv(database_path('data/weight_standards_kakotora_line1.csv'));
        $dc  = $this->readCsv(database_path('data/dc_codes_kakotora_line1.csv'));

        DB::transaction(function () use ($project, $std, $dc) {
            DcCode::where('project_id', $project->id)->delete();
            WeightStandard::where('project_id', $project->id)->delete();

            foreach ($std as $r) {
                WeightStandard::create([
                    'project_id' => $project->id,
                    'type_code'  => $r['type_code'],
                    'nominal'    => (int) $r['nominal'],
                    'tolerance'  => (int) $r['tolerance'],
                ]);
            }

            foreach ($dc as $r) {
                DcCode::create([
                    'project_id'  => $project->id,
                    'prog_no'     => $r['prog_no'] !== '' ? (int) $r['prog_no'] : null,
                    'dc_code'     => $r['dc_code'],
                    'part_number' => $r['part_number'] ?: null,
                    'model'       => $r['model'] ?: null,
                    'type_code'   => $r['type_code'] ?: null,
                    'scale_prog'  => $r['scale_prog'] !== '' ? (int) $r['scale_prog'] : null,
                    'status'      => $r['status'] ?: null,
                    'remark'      => $r['remark'] ?: null,
                ]);
            }
        });

        $this->command->info('Selesai: ' . count($std) . ' standar timbangan dan ' . count($dc) . ' DC Code diimpor ke ' . $project->name . '.');
    }

    private function readCsv(string $path): array
    {
        if (! is_file($path)) {
            throw new \RuntimeException("File tidak ditemukan: $path");
        }
        $h = fopen($path, 'r');
        $head = fgetcsv($h);
        $head[0] = ltrim($head[0], "\xEF\xBB\xBF");
        $out = [];
        while (($row = fgetcsv($h)) !== false) {
            if ($row === [null]) continue;
            $out[] = array_combine($head, $row);
        }
        fclose($h);

        return $out;
    }
}
