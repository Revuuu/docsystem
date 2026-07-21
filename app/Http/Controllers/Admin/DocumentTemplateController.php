<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;


class DocumentTemplateController extends Controller
{
    /**
     * Upload a new template version.
     */
    public function store(Request $request): RedirectResponse
    {

        $templateDefinitions = config(
            'signature_templates',
            []
        );

        $allowedTemplateKeys = array_keys(
            $templateDefinitions
        );

        $validated = $request->validate([
            'template_key' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
            ],
            
            'template_file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:20480',
            ],

            'activate_now' => [
                'nullable',
                'boolean',
            ],
        ]);

        $templateKey = $validated['template_key'];
        $templateName = $templateDefinitions[$templateKey]['name']
        ?? ucwords(str_replace('_', ' ', $templateKey));

        $activateNow = $request->boolean('activate_now');

        $storedPath = null;

        try {
            DB::transaction(function () use (
                $request,
                $templateKey,
                $templateName,
                $activateNow,
                &$storedPath
            ): void {
                /*
                 * Lock existing versions of this template type
                 * while calculating the next version.
                 */
                $existingTemplates = DocumentTemplate::query()
                    ->where('template_key', $templateKey)
                    ->lockForUpdate()
                    ->get();

                $nextVersion = ((int) $existingTemplates->max(
                    'version'
                )) + 1;

                $directory = sprintf(
                    'templates/%s/v%d',
                    $templateKey,
                    $nextVersion
                );

                $filename = sprintf(
                    '%s_v%d.pdf',
                    $templateKey,
                    $nextVersion
                );

                $storedPath = $request
                    ->file('template_file')
                    ->storeAs(
                        $directory,
                        $filename,
                        'local'
                    );

                if ($storedPath === false) {
                    throw new \RuntimeException(
                        'The template PDF could not be stored.'
                    );
                }

                /*
                 * Deactivate the currently active version before
                 * activating the newly uploaded version.
                 */
                if ($activateNow) {
                    DocumentTemplate::query()
                        ->where('template_key', $templateKey)
                        ->where('is_active', true)
                        ->update([
                            'is_active' => false,
                            'updated_by' => auth()->id(),
                            'updated_at' => now(),
                        ]);
                }

                DocumentTemplate::create([
                    'template_key' => $templateKey,
                    'name' => $templateName,
                    'version' => $nextVersion,
                    'file_path' => $storedPath,
                    'is_active' => $activateNow,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            });
        } catch (Throwable $exception) {
            /*
             * Remove the uploaded file when the database
             * transaction fails.
             */
            if ($storedPath !== null) {
                Storage::disk('local')->delete($storedPath);
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'template_file' =>
                        'The template could not be uploaded: ' .
                        $exception->getMessage(),
                ]);
        }

        return back()->with(
            'success',
            $activateNow
                ? 'Template uploaded and activated successfully.'
                : 'Template version uploaded successfully.'
        );
    }

    /**
     * Activate one template version.
     */
    public function activate(
        DocumentTemplate $documentTemplate
    ): RedirectResponse {
        DB::transaction(function () use (
            $documentTemplate
        ): void {
            /*
             * Lock all versions under the same template key.
             */
            DocumentTemplate::query()
                ->where(
                    'template_key',
                    $documentTemplate->template_key
                )
                ->lockForUpdate()
                ->get();

            /*
             * Deactivate every other version.
             */
            DocumentTemplate::query()
                ->where(
                    'template_key',
                    $documentTemplate->template_key
                )
                ->whereKeyNot($documentTemplate->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            /*
             * Activate the selected version.
             */
            $documentTemplate->update([
                'is_active' => true,
                'updated_by' => auth()->id(),
            ]);
        });

        return back()->with(
            'success',
            sprintf(
                '%s version %d is now active.',
                $documentTemplate->name,
                $documentTemplate->version
            )
        );
    }
}