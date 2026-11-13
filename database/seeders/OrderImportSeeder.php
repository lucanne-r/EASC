<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Project;
use App\Models\Rfi;
use Illuminate\Database\Seeder;

class OrderImportSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::where('slug', 'kakotora-line-1')->first();
        if (! $project) {
            $this->command->error('Project "kakotora-line-1" tidak ditemukan. Buat dulu lewat halaman awal.');
            return;
        }

        $path = database_path('data/orders_kakotora_line1.csv');
        if (! is_file($path)) {
            $this->command->error("File CSV tidak ada di: $path");
            return;
        }

        // RFI yang dipakai di Excel. Budget 25-064 sesuai sel J4 Excel.
        $rfis = [
            '25-064'  => Rfi::firstOrCreate(['project_id' => $project->id, 'rfi_no' => '25-064'],  ['name' => 'KAKOTORA', 'budget' => 1700000000]),
            'Expanse' => Rfi::firstOrCreate(['project_id' => $project->id, 'rfi_no' => 'Expanse'], ['name' => 'Expanse',  'budget' => 0]),
        ];

        // Impor ulang selalu mengganti SEMUA order milik project ini.
        Order::where('project_id', $project->id)->delete();

        $fh = fopen($path, 'r');
        $head = fgetcsv($fh);
        $n = 0;

        while (($row = fgetcsv($fh)) !== false) {
            if (count($row) !== count($head)) {
                continue;
            }
            $r = array_combine($head, $row);

            Order::create([
                'project_id'  => $project->id,
                'rfi_id'      => $r['rfi_no'] !== '' ? ($rfis[$r['rfi_no']]->id ?? null) : null,
                'period'      => $r['period'],
                'item_no'     => $r['item_no'] !== '' ? (int) $r['item_no'] : null,
                'pr_no'       => $r['pr_no'] !== '' ? $r['pr_no'] : null,
                'description' => $r['description'],
                'value'       => $r['value'] !== '' ? (int) $r['value'] : null,
                'approvals'   => $r['approvals'] !== '' ? explode('|', $r['approvals']) : [],
                'po_issued'   => $r['po_issued'] === '1',
                'po_no'       => $r['po_no'] !== '' ? $r['po_no'] : null,
                'is_closed'   => $r['is_closed'] === '1',
                'remark'      => $r['remark'] !== '' ? $r['remark'] : null,
            ]);
            $n++;
        }
        fclose($fh);

        $this->command->info("Selesai: $n order diimpor ke {$project->name}.");
    }
}
