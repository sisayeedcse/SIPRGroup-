<?php

namespace App\Http\Controllers;

class ModuleController extends Controller
{
    public function show(string $module)
    {
        $allowed = [
            'payments',
            'investments',
            'transactions',
            'members',
            'wallet',
            'goals',
            'noticeboard',
            'expenses',
            'documents',
            'activity',
            'about',
            'control',
        ];

        abort_unless(in_array($module, $allowed, true), 404);

        return view('app.module', [
            'module' => $module,
            'title' => str($module)->replace('-', ' ')->title()->toString(),
        ]);
    }
}
