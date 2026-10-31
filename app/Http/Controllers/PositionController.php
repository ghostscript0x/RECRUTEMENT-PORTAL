<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PositionController extends Controller
{
    /** Public list of positions currently open for applications. */
    public function index(Request $request): View
    {
        $hasPositionsTable = Schema::hasTable((new Position)->getTable());

        return view('positions.index', [
            'positions' => $hasPositionsTable ? Position::accepting()->latest()->get() : collect(),
            'closed' => $hasPositionsTable ? Position::where('status', 'closed')->latest()->limit(10)->get() : collect(),
        ]);
    }

    public function show(Position $position): View
    {
        abort_unless($position->isAccepting(), 404);

        return view('positions.show', ['position' => $position]);
    }
}
