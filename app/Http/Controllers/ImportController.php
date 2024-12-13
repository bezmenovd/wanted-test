<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportRequest;
use App\Jobs\ImportJob;

class ImportController
{
    public function __invoke(ImportRequest $request)
    {
        $filename = sprintf("%s.xlsx", md5($request->file->getClientOriginalName() . microtime(true)));

        $request->file->storeAs('uploads', $filename);

        ImportJob::dispatch($filename);

        return response()->json();
    }
}
