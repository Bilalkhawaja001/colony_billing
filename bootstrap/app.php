<?php

use App\Http\Controllers\Todo\TodoTasksController;
use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\RoleGate;
use App\Http\Middleware\ShellPathRbac;
use App\Http\Middleware\MonthGuardShell;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware(['ensure.auth', 'force.password.change', 'shell.rbac'])->group(function () {
                Route::get('/todo-tasks', [TodoTasksController::class, 'index']);
                Route::get('/ui/todo-tasks', fn () => redirect('/todo-tasks'));
            });

            Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])->group(function () {
                Route::get('/api/todo-tasks', [TodoTasksController::class, 'list']);
            });

            Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY'])->group(function () {
                Route::post('/todo-tasks', [TodoTasksController::class, 'store']);
                Route::patch('/todo-tasks/{id}', [TodoTasksController::class, 'update']);
                Route::post('/todo-tasks/{id}/complete', [TodoTasksController::class, 'complete']);
                Route::post('/todo-tasks/{id}/archive', [TodoTasksController::class, 'archive']);
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'ensure.auth' => EnsureAuthenticated::class,
            'force.password.change' => ForcePasswordChange::class,
            'role' => RoleGate::class,
            'shell.rbac' => ShellPathRbac::class,
            'month.guard.shell' => MonthGuardShell::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
