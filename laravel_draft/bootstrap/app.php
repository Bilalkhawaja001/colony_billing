<?php

use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\RoleGate;
use App\Http\Middleware\ShellPathRbac;
use App\Http\Middleware\MonthGuardShell;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
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
