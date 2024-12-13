<?php

namespace App\Http\Controllers;

use App\Models\Row;
use Illuminate\Http\Request;

class ListController
{
    public function __invoke(Request $request)
    {
        $rows = Row::query()->get();
        
        $out = [];

        foreach ($rows as $row) {
            $out[$row->date->toDateString()][] = [
                'id' => $row->id,
                'name' => $row['name']
            ];
        }

        ksort($out);

        return response()->json([
            'count' => $rows->count(),
            'rows' => $out
        ]);
    }
}
