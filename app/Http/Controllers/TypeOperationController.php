<?php

namespace App\Http\Controllers;

use App\Enums\OperationSens;
use App\Http\Requests\StoreTypeOperationRequest;
use App\Http\Requests\UpdateTypeOperationRequest;
use App\Models\TypeOperation;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TypeOperationController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        $this->authorize('viewAny', TypeOperation::class);

        return view('admin.type-operations.index', [
            'types' => TypeOperation::query()->withCount('operations')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TypeOperation::class);

        return view('admin.type-operations.create', [
            'sens' => OperationSens::cases(),
        ]);
    }

    public function store(StoreTypeOperationRequest $request): RedirectResponse
    {
        $type = TypeOperation::query()->create($request->validated());
        $this->audit->log('modification_parametres', $type, null, $type->toArray());

        return redirect()->route('type-operations.index')->with('success', 'Type d’opération créé.');
    }

    public function edit(TypeOperation $type_operation): View
    {
        $this->authorize('update', $type_operation);

        return view('admin.type-operations.edit', [
            'type' => $type_operation,
            'sens' => OperationSens::cases(),
        ]);
    }

    public function update(UpdateTypeOperationRequest $request, TypeOperation $type_operation): RedirectResponse
    {
        $avant = $type_operation->toArray();
        $type_operation->update($request->validated());
        $this->audit->log('modification_parametres', $type_operation, $avant, $type_operation->fresh()->toArray());

        return redirect()->route('type-operations.index')->with('success', 'Type d’opération mis à jour.');
    }
}
