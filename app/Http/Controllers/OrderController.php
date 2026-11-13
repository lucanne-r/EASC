<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Project;
use App\Models\Rfi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $rfis = Rfi::where('project_id', $project->id)->withSum('orders', 'value')->orderBy('rfi_no')->get();

        $orders = Order::with('rfi')
            ->where('project_id', $project->id)
            ->when($request->period, fn ($q, $v) => $q->where('period', $v))
            ->when($request->rfi, fn ($q, $v) => $v === 'none' ? $q->whereNull('rfi_id') : $q->where('rfi_id', $v))
            ->when($request->stage === 'Close', fn ($q) => $q->where('is_closed', true))
            ->when($request->stage === 'PO', fn ($q) => $q->where('po_issued', true)->where('is_closed', false))
            ->when($request->stage === 'Open', fn ($q) => $q->where('po_issued', false)->where('is_closed', false))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('pr_no', 'like', "%$v%")
                ->orWhere('description', 'like', "%$v%")))
            ->orderByDesc('period')->orderBy('item_no')->orderBy('id')
            ->get()->groupBy('period');

        $periods = Order::where('project_id', $project->id)->distinct()->orderByDesc('period')->pluck('period');

        // Nomor PR yang muncul lebih dari sekali (di Excel ada 3)
        $dupes = Order::where('project_id', $project->id)->whereNotNull('pr_no')
            ->groupBy('pr_no')->havingRaw('count(*) > 1')->pluck('pr_no')->all();

        $noRfiTotal = (int) Order::where('project_id', $project->id)->whereNull('rfi_id')->sum('value');

        return view('project.order', compact('project', 'rfis', 'orders', 'periods', 'dupes', 'noRfiTotal'));
    }

    public function create(Project $project)
    {
        $order = new Order(['period' => now()->format('Y-m')]);

        return view('project.order-form', ['project' => $project, 'order' => $order, 'rfis' => $this->rfis($project)]);
    }

    public function store(Request $request, Project $project)
    {
        Order::create($this->data($request, $project) + ['project_id' => $project->id]);

        return redirect("/p/{$project->slug}/control-order")->with('ok', 'Order ditambahkan.');
    }

    public function edit(Project $project, Order $order)
    {
        $this->own($project, $order->project_id);

        return view('project.order-form', ['project' => $project, 'order' => $order, 'rfis' => $this->rfis($project)]);
    }

    public function update(Request $request, Project $project, Order $order)
    {
        $this->own($project, $order->project_id);
        $order->update($this->data($request, $project));

        return redirect("/p/{$project->slug}/control-order")->with('ok', 'Order diperbarui.');
    }

    public function destroy(Project $project, Order $order)
    {
        $this->own($project, $order->project_id);
        $order->delete();

        return back()->with('ok', 'Order dihapus.');
    }

    // ===== Budget RFI =====

    public function storeRfi(Request $request, Project $project)
    {
        $data = $request->validate([
            'rfi_no' => ['required', 'string', 'max:40', Rule::unique('rfis')->where('project_id', $project->id)],
            'name'   => 'nullable|string|max:150',
            'budget' => 'required|integer|min:0',
        ]);
        Rfi::create($data + ['project_id' => $project->id]);

        return back()->with('ok', 'RFI ditambahkan.');
    }

    public function updateRfi(Request $request, Project $project, Rfi $rfi)
    {
        $this->own($project, $rfi->project_id);
        $rfi->update($request->validate(['budget' => 'required|integer|min:0']));

        return back()->with('ok', 'Budget diperbarui.');
    }

    public function destroyRfi(Project $project, Rfi $rfi)
    {
        $this->own($project, $rfi->project_id);

        if ($rfi->orders()->exists()) {
            return back()->withErrors(["RFI {$rfi->rfi_no} masih dipakai order, tidak bisa dihapus."]);
        }
        $rfi->delete();

        return back()->with('ok', 'RFI dihapus.');
    }

    // ===== bantu =====

    private function rfis(Project $project)
    {
        return Rfi::where('project_id', $project->id)->orderBy('rfi_no')->get();
    }

    private function own(Project $project, $projectId): void
    {
        abort_unless($projectId === $project->id, 404);
    }

    private function data(Request $request, Project $project): array
    {
        $data = $request->validate([
            'rfi_id'       => ['nullable', Rule::exists('rfis', 'id')->where('project_id', $project->id)],
            'period'       => 'required|date_format:Y-m',
            'item_no'      => 'nullable|integer|min:1',
            'pr_no'        => 'nullable|string|max:50',
            'description'  => 'required|string|max:255',
            'value'        => 'nullable|integer|min:0',
            'approvals'    => 'nullable|array',
            'approvals.*'  => [Rule::in(Order::APPROVERS)],
            'po_no'        => 'nullable|string|max:50',
            'remark'       => 'nullable|string|max:500',
        ]);

        $data['approvals'] = $data['approvals'] ?? [];
        $data['po_issued'] = $request->boolean('po_issued');
        $data['is_closed'] = $request->boolean('is_closed');

        return $data;
    }
}