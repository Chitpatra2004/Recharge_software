<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DocumentController extends Controller
{
    private const FIELD_MAP = [
        'pan' => 'pan_image_path',
        'gst' => 'gst_certificate_path',
        'doc' => 'document_path',
    ];

    /** GET /api/v1/seller/documents */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $docs = [];
        foreach (self::FIELD_MAP as $type => $field) {
            $path = $user->{$field};
            $has  = ! empty($path) && Storage::disk('private')->exists($path);
            $docs[$type] = [
                'has'      => $has,
                'filename' => $has ? basename($path) : null,
                'view_url' => $has ? URL::temporarySignedRoute(
                    'seller.document.view',
                    now()->addMinutes(10),
                    ['type' => $type, 'uid' => $user->id]
                ) : null,
            ];
        }

        return response()->json([
            'documents'       => $docs,
            'approval_status' => $user->approval_status ?? 'pending',
            'account_status'  => $user->status,
        ]);
    }

    /** POST /api/v1/seller/documents/upload */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:pan,gst,doc'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $user  = $request->user();
        $type  = $request->input('type');
        $field = self::FIELD_MAP[$type];

        if ($user->{$field} && Storage::disk('private')->exists($user->{$field})) {
            Storage::disk('private')->delete($user->{$field});
        }

        $path = $request->file('file')->store("sellers/{$user->id}/documents", 'private');
        $user->update([$field => $path]);

        return response()->json(['message' => 'Document uploaded successfully.']);
    }

    /** GET /seller/documents/{type}/view  (signed route — no Bearer needed) */
    public function serve(Request $request, string $type): Response
    {
        $field = self::FIELD_MAP[$type] ?? null;
        if (! $field) {
            abort(404);
        }

        $uid  = (int) $request->query('uid');
        $user = User::findOrFail($uid);
        $path = $user->{$field};

        if (! $path || ! Storage::disk('private')->exists($path)) {
            abort(404, 'Document not found.');
        }

        $file     = Storage::disk('private')->get($path);
        $mimeType = mime_content_type(Storage::disk('private')->path($path));

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"');
    }
}
