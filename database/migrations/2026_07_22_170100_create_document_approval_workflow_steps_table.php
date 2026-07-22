<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'document_approval_workflow_steps',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'document_approval_workflow_id'
                )
                    ->constrained(
                        'document_approval_workflows'
                    )
                    ->cascadeOnDelete();

                /*
                 * Approval sequence:
                 *
                 * 1 = first approver
                 * 2 = second approver
                 * 3 = third approver
                 */
                $table->unsignedInteger(
                    'step_order'
                );

                /*
                 * Required Laratrust/application role.
                 *
                 * Examples:
                 * staff
                 * supervisor
                 * depthead
                 * division
                 * executive
                 */
                $table->string(
                    'required_role',
                    100
                );

                /*
                 * The specific user selected by the admin.
                 *
                 * Nullable so a deleted user does not delete
                 * the workflow configuration. Extraction will
                 * reject workflows with a missing assigned user.
                 */
                $table->foreignId(
                    'user_id'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                /*
                 * A workflow cannot have two entries with the
                 * same approval position.
                 */
                $table->unique(
                    [
                        'document_approval_workflow_id',
                        'step_order',
                    ],
                    'workflow_steps_order_unique'
                );

                /*
                 * A fixed-template workflow should use each
                 * configured signature role only once.
                 */
                $table->unique(
                    [
                        'document_approval_workflow_id',
                        'required_role',
                    ],
                    'workflow_steps_role_unique'
                );

                $table->index(
                    [
                        'document_approval_workflow_id',
                        'user_id',
                    ],
                    'workflow_steps_user_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'document_approval_workflow_steps'
        );
    }
};