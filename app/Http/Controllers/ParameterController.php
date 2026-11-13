<?php

namespace App\Http\Controllers;

use App\Models\DcCode;
use App\Models\Project;
use App\Models\WeightStandard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParameterController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $tab = $request->query('tab') === 'std' ? 'std' : 'dc';

        $standards = WeightStandard::where('project_id', $project->id)->orderBy('type_code')->get();
        $std = $standards->keyBy('type_code');

        $all = DcCode::where('project_id', $project->id)->get();
        $usage = $all->whereNotNull('type_code')->countBy('type_code');
        $missing = $usage->reject(fn ($n, $t) => $std->has($t));
        $noStdCount = $all->filter(fn ($c) => ! ($c->type_code && $std->has($c->type_code)))->count();
        $dupes = $all->countBy('dc_code')->filter(fn ($n) => $n > 1)->keys()->all();
        $types = $usage->keys()->sort()->values();
        $totalDc = $all->count();

        $q = trim((string) $request->query('q'));
        $codes = DcCode::where('project_id', $project->id)
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w
                ->where('dc_code', 'like', "%$q%")
                ->orWhere('model', 'like', "%$q%")
                ->orWhere('part_number', 'like', "%$q%")))
            ->when($request->query('type'), fn ($x, $t) => $t === 'none' ? $x->whereNull('type_code') : $x->where('type_code', $t))
            ->orderBy('prog_no')->orderBy('id')->get();

        if ($request->query('filter') === 'nostd') {
            $codes = $codes->filter(fn ($c) => ! ($c->type_code && $std->has($c->type_code)))->values();
        }

        return view('project.parameter', compact(
            'project', 'tab', 'standards', 'std', 'codes', 'usage', 'missing', 'noStdCount', 'dupes', 'types', 'totalDc'
        ));
    }

    // ===== Standar Timbangan =====

    public function stdCreate(Project $project)
    {
        return view('project.parameter-std-form', ['project' => $project, 'std' => new WeightStandard(['tolerance' => 40])]);
    }

    public function stdStore(Request $request, Project $project)
    {
        $data = $request->validate([
            'type_code' => ['required', 'string', 'max:40', Rule::unique('weight_standards')->where('project_id', $project->id)],
            'nominal'   => 'required|integer|min:1',
            'tolerance' => 'required|integer|min:0',
        ]);
        WeightStandard::create($data + ['project_id' => $project->id]);

        return redirect("/p/{$project->slug}/parameter?tab=std")->with('ok', 'Standar timbangan ditambahkan.');
    }

    public function stdEdit(Project $project, WeightStandard $standard)
    {
        $this->own($project, $standard->project_id);

        return view('project.parameter-std-form', ['project' => $project, 'std' => $standard]);
    }

    public function stdUpdate(Request $request, Project $project, WeightStandard $standard)
    {
        $this->own($project, $standard->project_id);
        $standard->update($request->validate([
            'nominal'   => 'required|integer|min:1',
            'tolerance' => 'required|integer|min:0',
        ]));

        return redirect("/p/{$project->slug}/parameter?tab=std")->with('ok', 'Standar diperbarui. Semua DC Code bertipe ini ikut berubah.');
    }

    public function stdDestroy(Project $project, WeightStandard $standard)
    {
        $this->own($project, $standard->project_id);

        if (DcCode::where('project_id', $project->id)->where('type_code', $standard->type_code)->exists()) {
            return back()->withErrors(["TYPE {$standard->type_code} masih dipakai DC Code, tidak bisa dihapus."]);
        }
        $standard->delete();

        return back()->with('ok', 'Standar timbangan dihapus.');
    }

    // ===== DC Code =====

    public function dcCreate(Project $project)
    {
        return view('project.parameter-dc-form', ['project' => $project, 'dc' => new DcCode(), 'types' => $this->types($project)]);
    }

    public function dcStore(Request $request, Project $project)
    {
        DcCode::create($this->dcData($request) + ['project_id' => $project->id]);

        return redirect("/p/{$project->slug}/parameter")->with('ok', 'DC Code ditambahkan.');
    }

    public function dcEdit(Project $project, DcCode $dc)
    {
        $this->own($project, $dc->project_id);

        return view('project.parameter-dc-form', ['project' => $project, 'dc' => $dc, 'types' => $this->types($project)]);
    }

    public function dcUpdate(Request $request, Project $project, DcCode $dc)
    {
        $this->own($project, $dc->project_id);
        $dc->update($this->dcData($request));

        return redirect("/p/{$project->slug}/parameter")->with('ok', 'DC Code diperbarui.');
    }

    public function dcDestroy(Project $project, DcCode $dc)
    {
        $this->own($project, $dc->project_id);
        $dc->delete();

        return back()->with('ok', 'DC Code dihapus.');
    }

    // ===== bantu =====

    private function types(Project $project)
    {
        return WeightStandard::where('project_id', $project->id)->orderBy('type_code')->pluck('type_code');
    }

    private function own(Project $project, $projectId): void
    {
        abort_unless($projectId === $project->id, 404);
    }

    private function dcData(Request $request): array
    {
        return $request->validate([
            'prog_no'     => 'nullable|integer|min:0',
            'dc_code'     => 'required|string|max:50',
            'part_number' => 'nullable|string|max:80',
            'model'       => 'nullable|string|max:120',
            'type_code'   => 'nullable|string|max:40',
            'scale_prog'  => 'nullable|integer|min:0',
            'status'      => ['nullable', Rule::in(DcCode::STATUSES)],
            'remark'      => 'nullable|string|max:255',
        ]);
    }
}