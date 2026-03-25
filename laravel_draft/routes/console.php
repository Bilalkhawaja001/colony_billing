<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('mbs:about', function () {
    $this->comment('MBS Laravel Draft (auth-only LIMITED GO)');
});
