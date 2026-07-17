<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'document_signature_blocks',
            function (Blueprint $table) {
                $table->id();

                /*
                 * Document that owns this signature block.
                 */
                $table->foreignId('document_id')
                    ->constrained('documents')
                    ->cascadeOnDelete();

                /*
                 * Current PDF version on which the signature
                 * must be placed.
                 */
                $table->foreignId('document_file_id')
                    ->constrained('document_files')
                    ->cascadeOnDelete();

                /*
                 * Each approval must have only one
                 * signature block.
                 */
                $table->foreignId('approval_id')
                    ->unique()
                    ->constrained('approvals')
                    ->cascadeOnDelete();

                /*
                 * User authorized to sign this block.
                 */
                $table->foreignId('assigned_user_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                /*
                 * Template metadata.
                 */
                $table->string('template_key', 100);
                $table->string('label', 150)->nullable();

                /*
                 * Approval/signing order.
                 */
                $table->unsignedInteger('sequence');

                /*
                 * PDF page and normalized coordinates.
                 *
                 * Values should normally be between
                 * 0.00000000 and 1.00000000.
                 */
                $table->unsignedInteger('page_number')
                    ->default(1);

                $table->decimal('x', 12, 8);
                $table->decimal('y', 12, 8);
                $table->decimal('width', 12, 8);
                $table->decimal('height', 12, 8);

                /*
                 * locked:
                 * Approval is not yet available.
                 *
                 * available:
                 * Current signer may approve and sign.
                 *
                 * signed:
                 * Signature has been applied.
                 *
                 * cancelled:
                 * Signing was stopped, normally because
                 * the document was rejected.
                 */
                $table->string('status', 20)
                    ->default('locked');

                /*
                 * Signing result.
                 */
                $table->foreignId('signed_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('signed_document_file_id')
                    ->nullable()
                    ->constrained('document_files')
                    ->nullOnDelete();

                $table->timestamp('signed_at')->nullable();

                $table->timestamps();

                /*
                 * Prevent duplicate block positions within
                 * one document workflow.
                 */
                $table->unique(
                    [
                        'document_id',
                        'sequence',
                    ],
                    'signature_blocks_document_sequence_unique'
                );

                $table->index(
                    [
                        'document_id',
                        'status',
                    ],
                    'signature_blocks_document_status_index'
                );

                $table->index(
                    [
                        'assigned_user_id',
                        'status',
                    ],
                    'signature_blocks_user_status_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'document_signature_blocks'
        );
    }
};