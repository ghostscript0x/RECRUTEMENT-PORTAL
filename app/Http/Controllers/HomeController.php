<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $hasPositionsTable = Schema::hasTable((new Position)->getTable());

        return view('home', [
            'positions' => $hasPositionsTable ? Position::accepting()->latest()->get() : collect(),
            'totalPositions' => $hasPositionsTable ? Position::accepting()->count() : 0,
        ]);
    }
}
