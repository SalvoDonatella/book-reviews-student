<?php

namespace App\Http\Controllers;

use App\Models\Director;

class DirectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $directors = Director::withCount('films')
            ->orderBy('name')
            ->paginate(12);

        return view('directors.index', compact('directors'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Director $director)
    {
        // Each film's own directors and review stats are loaded too, since
        // this page reuses the films.partials.card partial from the
        // catalogue listing - it expects the same data shape either way.
        $director->load(['films' => function ($query) {
            $query->with('directors')->withCount('reviews')->withAvg('reviews', 'rating');
        }]);

        return view('directors.show', compact('director'));
    }
}
