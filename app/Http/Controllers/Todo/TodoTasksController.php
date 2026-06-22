<?php

namespace App\Http\Controllers\Todo;

use App\Http\Controllers\Controller;
use App\Services\Todo\TodoTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class TodoTasksController extends Controller
{
    public function __construct(private readonly TodoTaskService $tasks)
    {
    }

    public function index(Request $request): View
    {
        return view('ui.todo-tasks', [
            'filters' => [
                'status' => (string)$request->query('status', ''),
                'priority' => (string)$request->query('priority', ''),
                'month_cycle' => (string)$request->query('month_cycle', ''),
                'category' => (string)$request->query('category', ''),
                'search' => (string)$request->query('search', ''),
            ],
            'payload' => $this->tasks->list($request->query()),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        return response()->json($this->tasks->list($request->query()));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $task = $this->tasks->create($request->only([
                'task_key',
                'title',
                'description',
                'category',
                'status',
                'priority',
                'month_cycle',
                'due_date',
                'assigned_to_user_id',
            ]), $this->currentUserId());
        } catch (InvalidArgumentException $exception) {
            return $this->errorResponse($request, $exception->getMessage());
        }

        return $this->successResponse($request, 'Task created.', $task);
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $task = $this->tasks->update($id, $request->only([
                'task_key',
                'title',
                'description',
                'category',
                'status',
                'priority',
                'month_cycle',
                'due_date',
                'assigned_to_user_id',
            ]), $this->currentUserId());
        } catch (InvalidArgumentException $exception) {
            return $this->errorResponse($request, $exception->getMessage());
        }

        return $this->successResponse($request, 'Task updated.', $task);
    }

    public function complete(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $task = $this->tasks->complete($id, $this->currentUserId());

        return $this->successResponse($request, 'Task completed.', $task);
    }

    public function archive(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $task = $this->tasks->archive($id, $this->currentUserId());

        return $this->successResponse($request, 'Task archived.', $task);
    }

    private function currentUserId(): ?int
    {
        $userId = session('user_id');

        return $userId === null ? null : (int)$userId;
    }

    private function successResponse(Request $request, string $message, object $task): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'task' => $task,
            ]);
        }

        return redirect('/todo-tasks')->with('status', $message);
    }

    private function errorResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 422);
        }

        return redirect('/todo-tasks')->with('status', $message);
    }
}
