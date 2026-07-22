<?php

namespace App\Services;

use App\Models\DocumentApprovalWorkflow;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DefaultApprovalWorkflowResolver
{
    /**
     * Resolve and validate the active approval workflow
     * configured for a fixed-template document type.
     */
    public function resolve(
        string $templateKey
    ): DocumentApprovalWorkflow {
        $template = config(
            "signature_templates.{$templateKey}"
        );

        if (!is_array($template)) {
            throw ValidationException::withMessages([
                'document_type' =>
                    "Document type [{$templateKey}] is not configured.",
            ]);
        }

        $workflow = DocumentApprovalWorkflow::query()
            ->active()
            ->where(
                'template_key',
                $templateKey
            )
            ->with([
                'steps.user.roles',
            ])
            ->first();

        if (!$workflow) {
            throw ValidationException::withMessages([
                'approval_workflow' =>
                    'No active default approval workflow is configured for this document type.',
            ]);
        }

        if ($workflow->steps->isEmpty()) {
            throw ValidationException::withMessages([
                'approval_workflow' =>
                    'The default approval workflow has no configured approvers.',
            ]);
        }

        $supportedRoles = collect(
            $template['blocks'] ?? []
        )
            ->filter(
                fn (mixed $block): bool =>
                    is_array($block) &&
                    is_string($block['role'] ?? null) &&
                    trim($block['role']) !== ''
            )
            ->pluck('role')
            ->map(
                fn (string $role): string =>
                    trim($role)
            )
            ->unique()
            ->values()
            ->all();

        foreach ($workflow->steps as $step) {
            $requiredRole = trim(
                (string) $step->required_role
            );

            if ($requiredRole === '') {
                throw ValidationException::withMessages([
                    'approval_workflow' =>
                        "Workflow step {$step->step_order} has no required role.",
                ]);
            }

            if (!in_array(
                $requiredRole,
                $supportedRoles,
                true
            )) {
                throw ValidationException::withMessages([
                    'approval_workflow' =>
                        "Role [{$requiredRole}] has no signature block in template [{$templateKey}].",
                ]);
            }

            if (!$step->user) {
                throw ValidationException::withMessages([
                    'approval_workflow' =>
                        "No user is assigned to workflow step {$step->step_order}.",
                ]);
            }

            if ($step->user->email_verified_at === null) {
                throw ValidationException::withMessages([
                    'approval_workflow' =>
                        "Assigned user [{$step->user->name}] is not verified.",
                ]);
            }

            $actualRole = $this->resolveUserRole(
                $step->user
            );

            if ($actualRole !== $requiredRole) {
                throw ValidationException::withMessages([
                    'approval_workflow' =>
                        "Assigned user [{$step->user->name}] must have role [{$requiredRole}].",
                ]);
            }
        }

        return $workflow;
    }

    /**
     * Determine the user's application role.
     *
     * The users.role column is checked first because your
     * current UserController keeps it synchronized with Laratrust.
     */
    public function resolveUserRole(
        User $user
    ): ?string {
        if (
            is_string($user->role) &&
            trim($user->role) !== ''
        ) {
            return trim($user->role);
        }

        $user->loadMissing('roles');

        $role = $user->roles
            ->pluck('name')
            ->first();

        return is_string($role) &&
            trim($role) !== ''
                ? trim($role)
                : null;
    }
}