<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComplaintRequest;
use App\Models\Complaint;
use App\Models\ComplaintLog;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    /**
     * GET /api/v1/complaints
     */
    public function index(Request $request): JsonResponse
    {
        $complaints = $request->user()
            ->complaints()
            ->with('transaction:id,mobile,amount,status')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $complaints]);
    }

    /**
     * POST /api/v1/complaints
     */
    public function store(ComplaintRequest $request): JsonResponse
    {
        $complaint = Complaint::create([
            ...$request->validated(),
            'user_id'       => $request->user()->id,
            'ticket_number' => 'TKT-' . strtoupper(Str::random(8)),
            'status'        => 'open',
            'priority'      => 'medium',
        ]);

        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'user_id'      => $request->user()->id,
            'action'       => 'created',
            'note'         => 'Complaint raised by user.',
        ]);

        ActivityLogger::log('complaint.created', "Ticket: {$complaint->ticket_number}", $complaint, [], $request->user()->id, $request);

        return response()->json(['data' => $complaint], 201);
    }

    /**
     * GET /api/v1/complaints/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $complaint = Complaint::with(['logs.user:id,name', 'transaction:id,mobile,amount,status'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['data' => $complaint]);
    }
}
