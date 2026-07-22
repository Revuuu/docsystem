<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentApprovalWorkflow;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentApprovalWorkflowController extends Controller
{
    /**
     * Create or update the default approvers for one document type.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'template_key' => [
                'required',
                'string',
                'max:100',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            /*
             * Expected structure:
             *
             * approvers[staff] = 1
             * approvers[supervisor] = 4
             * approvers[depthead] = 7
             */
            'approvers' => [
                'required',
                'array',
            ],

            'approvers.*' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ]);

        $templateKey = trim(
            $validated['template_key']
        );

        $template = config(
            "signature_templates.{$templateKey}"
        );

        if (!is_array($template)) {
            throw ValidationException::withMessages([
                'template_key' =>
                    "The document type [{$templateKey}] is not configured.",
            ]);
        }

        $templateBlocks = collect(
            $template['blocks'] ?? []
        )
            ->filter(
                fn (mixed $block): bool =>
                    is_array($block)
                    && is_string($block['role'] ?? null)
                    && trim($block['role']) !== ''
                    && is_numeric($block['sequence'] ?? null)
            )
            ->sortBy(
                fn (array $block): int =>
                    (int) $block['sequence']
            )
            ->unique(
                fn (array $block): string =>
                    trim($block['role'])
            )
            ->values();

        if ($templateBlocks->isEmpty()) {
            throw ValidationException::withMessages([
                'template_key' =>
                    'The selected document type has no configured signature blocks.',
            ]);
        }

        $allowedRoles = $templateBlocks
            ->pluck('role')
            ->map(
                fn (string $role): string =>
                    trim($role)
            )
            ->values()
            ->all();

        $submittedApprovers = collect(
            $validated['approvers']
        );

        /*
         * Reject role keys that are not part of the template.
         */
        $unknownRoles = $submittedApprovers
            ->keys()
            ->filter(
                fn (mixed $role): bool =>
                    is_string($role)
                    && !in_array(
                        $role,
                        $allowedRoles,
                        true
                    )
            )
            ->values();

        if ($unknownRoles->isNotEmpty()) {
            throw ValidationException::withMessages([
                'approvers' =>
                    'Unsupported workflow role: '
                    . $unknownRoles->join(', '),
            ]);
        }

        /*
         * Remove unchecked or empty role assignments.
         */
        $selectedApprovers = $submittedApprovers
            ->filter(
                fn (mixed $userId): bool =>
                    $userId !== null
                    && $userId !== ''
            )
            ->map(
                fn (mixed $userId): int =>
                    (int) $userId
            );

        if ($selectedApprovers->isEmpty()) {
            throw ValidationException::withMessages([
                'approvers' =>
                    'Select at least one approver for this document type.',
            ]);
        }

        $users = User::query()
            ->with('roles')
            ->whereIn(
                'id',
                $selectedApprovers
                    ->values()
                    ->unique()
                    ->all()
            )
            ->get()
            ->keyBy('id');

        $workflowSteps = [];

        /*
         * Follow the order defined in signature_templates.php.
         *
         * Approval step_order is kept contiguous even when
         * some template roles are not selected.
         */
        foreach ($templateBlocks as $block) {
            $requiredRole = trim(
                $block['role']
            );

            $userId = $selectedApprovers->get(
                $requiredRole
            );

            if (!$userId) {
                continue;
            }

            $user = $users->get(
                $userId
            );

            if (!$user) {
                throw ValidationException::withMessages([
                    "approvers.{$requiredRole}" =>
                        'The selected user no longer exists.',
                ]);
            }

            if ($user->email_verified_at === null) {
                throw ValidationException::withMessages([
                    "approvers.{$requiredRole}" =>
                        "The selected user [{$user->name}] is not verified.",
                ]);
            }

            $actualRole = $this->resolveUserRole(
                $user
            );

            if ($actualRole !== $requiredRole) {
                throw ValidationException::withMessages([
                    "approvers.{$requiredRole}" =>
                        "The selected user must have the [{$requiredRole}] role.",
                ]);
            }

            $workflowSteps[] = [
                'step_order' =>
                    count($workflowSteps) + 1,

                'required_role' =>
                    $requiredRole,

                'user_id' =>
                    $user->id,
            ];
        }

        if ($workflowSteps === []) {
            throw ValidationException::withMessages([
                'approvers' =>
                    'No valid approvers were selected.',
            ]);
        }

        DB::transaction(function () use (
            $templateKey,
            $template,
            $workflowSteps,
            $request
        ): void {
            $workflow = DocumentApprovalWorkflow::query()
                ->firstOrNew([
                    'template_key' => $templateKey,
                ]);

            if (!$workflow->exists) {
                $workflow->created_by = auth()->id();
            }

            $workflow->fill([
                'name' =>
                    $template['name']
                    ?? ucwords(
                        str_replace(
                            '_',
                            ' ',
                            $templateKey
                        )
                    ),

                'is_active' =>
                    $request->boolean(
                        'is_active'
                    ),

                'updated_by' =>
                    auth()->id(),
            ]);

            $workflow->save();

            /*
             * Replace the old configuration atomically.
             *
             * If any database operation fails, the previous
             * workflow remains unchanged.
             */
            $workflow->steps()->delete();

            $workflow->steps()->createMany(
                $workflowSteps
            );
        });

        return redirect()
            ->route('dashboard', [
                'section' =>
                    'document-templates',
            ])
            ->with(
                'workflow_success',
                'Default approvers saved successfully.'
            );
    }

    /**
     * Resolve the role from the users.role column,
     * with Laratrust as a fallback.
     */
    private function resolveUserRole(
        User $user
    ): ?string {
        if (
            is_string($user->role)
            && trim($user->role) !== ''
        ) {
            return trim($user->role);
        }

        $role = $user->roles
            ->pluck('name')
            ->first();

        return is_string($role)
            && trim($role) !== ''
                ? trim($role)
                : null;
    }
}