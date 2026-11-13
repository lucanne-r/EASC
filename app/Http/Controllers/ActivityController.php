<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ActivityController extends Controller
{
    private function project(string $slug)
    {
        $p = DB::table('projects')->where('slug', $slug)->first();
        abort_if(! $p, 404, 'Project tidak ditemukan.');
        return $p;
    }

    private function filtered(Request $r, string $slug)
    {
        $q = Activity::where('project_slug', $slug);
        foreach (['status', 'priority', 'category', 'pic'] as $f) {
            if ($r->filled($f)) $q->where($f, $r->input($f));
        }
        if ($r->filled('machine')) $q->where('machine_area', $r->machine);
        if ($r->filled('due_from')) $q->where('due_date', '>=', $r->due_from);
        if ($r->filled('due_to')) $q->where('due_date', '<=', $r->due_to);
        if ($r->order === 'assigned') $q->where('work_order', '>', 0);
        if ($r->order === 'unassigned') $q->where('work_order', 0);
        if (! $r->filled('status')) $q->where('status', '!=', 'Delete');
        if ($r->filled('q')) {
            $s = '%' . $r->q . '%';
            $q->where(fn ($x) => $x->where('code', 'like', $s)->orWhere('activity', 'like', $s)
                ->orWhere('pic', 'like', $s)->orWhere('machine_area', 'like', $s));
        }
        return $q->orderByRaw("CASE WHEN status='Closed' THEN 1 ELSE 0 END")
            ->orderByRaw('CASE WHEN work_order>0 THEN 0 ELSE 1 END')
            ->orderBy('work_order')
            ->orderByRaw("CASE priority WHEN 'P1' THEN 0 WHEN 'P2' THEN 1 WHEN 'P3' THEN 2 ELSE 3 END")
            ->orderBy('code');
    }

    public function index(Request $r, string $slug)
    {
        $project = $this->project($slug);
        $live = Activity::where('project_slug', $slug)->where('status', '!=', 'Delete');

        $st = (clone $live)->select('status', DB::raw('count(*) n'))->groupBy('status')->pluck('n', 'status');
        $total = $st->sum();
        $stats = [
            'total' => $total,
            'open' => $st['Open'] ?? 0,
            'progress' => $st['In Progress'] ?? 0,
            'hold' => $st['Hold'] ?? 0,
            'closed' => $st['Closed'] ?? 0,
            'rate' => $total ? round(($st['Closed'] ?? 0) / $total * 100, 1) : 0,
        ];

        $machines = (clone $live)->select('machine_area',
                DB::raw("sum(status='Closed') closed"),
                DB::raw("sum(status!='Closed') open"), DB::raw('count(*) n'))
            ->groupBy('machine_area')->orderBy('machine_area')->get();
        $prio = (clone $live)->where('status', '!=', 'Closed')->select('priority', DB::raw('count(*) n'))
            ->groupBy('priority')->pluck('n', 'priority');
        $cats = (clone $live)->where('status', '!=', 'Closed')->where('category', '!=', '')
            ->select('category', DB::raw('count(*) n'))->groupBy('category')->orderByDesc('n')->pluck('n', 'category');

        $activities = $this->filtered($r, $slug)->with('attachments')->get();
        $pics = Activity::where('project_slug', $slug)->where('pic', '!=', '')->distinct()->orderBy('pic')->pluck('pic');

        return view('project.activity', compact('project', 'slug', 'activities', 'stats', 'machines', 'prio', 'cats', 'pics'));
    }

    public function export(Request $r, string $slug)
    {
        $rows = Activity::where('project_slug', $slug)->orderBy('code')->get();
        return response()->streamDownload(function () use ($rows) {
            $o = fopen('php://output', 'w');
            fwrite($o, "\xEF\xBB\xBF");
            fputcsv($o, ['ID', 'Project', 'Machine / Area', 'Priority', 'Category', 'Activity', 'PIC', 'Status', 'Due Date', 'Urutan']);
            foreach ($rows as $a) {
                fputcsv($o, [$a->code, $a->project, $a->machine_area, $a->priority, $a->category, $a->activity, $a->pic, $a->status, $a->due_date, $a->work_order]);
            }
            fclose($o);
        }, "activity_{$slug}.csv", ['Content-Type' => 'text/csv']);
    }

    public function destroy(string $slug, int $id)
    {
        $a = Activity::where('project_slug', $slug)->findOrFail($id);
        $a->update(['status' => 'Delete', 'work_order' => 0]);
        return back()->with('ok', "{$a->code} dihapus (status Delete, masih bisa dilihat lewat filter status).");
    }
        public function nextId(Request $r, string $slug)
    {
        $code = Activity::MACHINES[$r->input('machine')] ?? null;
        if (! $code) {
            return response()->json(['error' => 'Pilih mesin yang terdaftar, atau isi ID manual untuk Other.'], 400);
        }
        $prefix = Str::contains($slug, 'line-4') ? "L4-$code" : "L1-$code";
        $max = Activity::where('code', 'like', "$prefix-%")->pluck('code')
            ->map(fn ($c) => (int) Str::afterLast($c, '-'))->max() ?? 0;
        return response()->json(['id' => sprintf('%s-%02d', $prefix, $max + 1)]);
    }

    private function rules(): array
    {
        return [
            'machine_area' => 'nullable|string|max:255', 'priority' => 'nullable|string|max:10',
            'category' => 'nullable|string|max:50', 'activity' => 'required|string',
            'pic' => 'nullable|string|max:100', 'status' => 'required|in:' . implode(',', Activity::STATUSES),
            'due_date' => 'nullable|date', 'work_order' => 'nullable|integer|min:0',
        ];
    }

    private function clean(array $d): array
    {
        foreach (['machine_area', 'priority', 'category', 'pic', 'due_date'] as $k) $d[$k] = $d[$k] ?? '';
        $d['work_order'] = (int) ($d['work_order'] ?? 0);
        return $d;
    }

    public function store(Request $r, string $slug)
    {
        $project = $this->project($slug);
        $data = $r->validate($this->rules() + ['code' => 'required|string|max:50']);
        if (Activity::where('project_slug', $slug)->where('code', $data['code'])->exists()) {
            return back()->withInput()->withErrors(['code' => "ID {$data['code']} sudah ada."]);
        }
        $a = Activity::create($this->clean($data) + ['project_slug' => $slug, 'project' => $project->name ?? $slug]);
        $this->saveFiles($a, $r);
        return redirect()->route('activity.index', $slug)->with('ok', "Aktivitas {$a->code} ditambahkan.");
    }

    public function update(Request $r, string $slug, int $id)
    {
        $a = Activity::where('project_slug', $slug)->findOrFail($id);
        $a->update($this->clean($r->validate($this->rules())));
        $this->saveFiles($a, $r);
        return back()->with('ok', "Aktivitas {$a->code} diperbarui.");
    }

    public function close(string $slug, int $id)
    {
        $a = Activity::where('project_slug', $slug)->findOrFail($id);
        $a->update(['status' => 'Closed', 'work_order' => 0]);
        return back()->with('ok', "{$a->code} ditutup.");
    }

    public function upload(Request $r, string $slug, int $id)
    {
        $a = Activity::where('project_slug', $slug)->findOrFail($id);
        $this->saveFiles($a, $r);
        return back()->with('ok', 'Lampiran disimpan.');
    }

    private function saveFiles(Activity $a, Request $r): void
    {
        $dir = storage_path('app/activity_attachments');
        File::ensureDirectoryExists($dir);
        foreach ((array) $r->file('attachments') as $f) {
            if (! $f) continue;
            $stored = Str::random(16) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $f->getClientOriginalName());
            $f->move($dir, $stored);
            $a->attachments()->create(['original_name' => $f->getClientOriginalName(), 'stored_name' => $stored]);
        }
    }

    public function download(string $slug, int $att)
    {
        $x = ActivityAttachment::findOrFail($att);
        $path = storage_path('app/activity_attachments/' . $x->stored_name);
        abort_unless(is_file($path), 404, 'File lampiran tidak ada di storage/app/activity_attachments.');
        return response()->file($path, ['Content-Disposition' => 'inline; filename="' . $x->original_name . '"']);
    }

    public function deleteAttachment(string $slug, int $att)
    {
        $x = ActivityAttachment::findOrFail($att);
        @unlink(storage_path('app/activity_attachments/' . $x->stored_name));
        $x->delete();
        return back()->with('ok', 'Lampiran dihapus.');
    }
}