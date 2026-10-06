<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Eager-load directors, and aggregate the review count/average rating
        // in the same query (withCount/withAvg), rather than loading every
        // review row just to print a number - avoids the N+1 problem without
        // pulling data the listing never displays.
        $films = Film::with('directors')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->search($request->query('search'))
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view('films.index', compact('films'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Film $film)
    {
        // Route model binding already resolved $film from {film} in the URL;
        // load its directors and reviews (plus each review's director) here,
        // rather than in the route, since not every route touching a Film
        // needs this much loaded.
        $film->load(['directors', 'reviews.user']);

        return view('films.show', compact('film'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('films.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $fillable already stops the wrong *keys* reaching the database;
        // this is what stops the wrong *values* - the same job the hand-
        // written validator class from the PHP module did, rule strings
        // instead of hand-written checks. A failure redirects back with
        // the errors and the submitted input attached automatically -
        // nothing here has to do that by hand.
        $validated = $request->validate($this->validationRules());

        if ($request->hasFile('image')) {
            // Store the upload on the 'public' disk, under storage/app/public/films
            // rather than storage/app/private - files on this disk are the ones the
            // storage:link symlink makes reachable over HTTP at all. putFile() picks
            // a random filename for us and returns the path it saved to, which is
            // what belongs in the column - never the uploaded file's own original name.
            $validated['image'] = Storage::disk('public')->putFile('films', $request->file('image'));
        }

        $film = Film::create($validated);

        return redirect()->route('films.show', $film);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Film $film)
    {
        return view('films.edit', compact('film'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Film $film)
    {
        // Same rules as store() - a film still has to make sense the second
        // time it's saved, not just the first. validationRules() below is
        // what keeps that "same rules" true by construction rather than by
        // remembering to copy a change into both methods.
        $validated = $request->validate($this->validationRules());

        if ($request->hasFile('image')) {
            $validated['image'] = Storage::disk('public')->putFile('films', $request->file('image'));
        }

        $film->update($validated);

        return redirect()->route('films.show', $film);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Film $film)
    {
        // The row and its cover image are two separate things to delete -
        // removing one never automatically removes the other. Deleting the
        // file first, while $film->image still holds its path, closes the
        // gap update() left open: a replaced cover was already an orphaned
        // file sitting in storage/app/public/films; a deleted film without
        // this line would just create another one, permanently this time.
        if ($film->image) {
            Storage::disk('public')->delete($film->image);
        }

        $film->delete();

        return redirect()->route('films.index');
    }

    /**
     * Validation rules shared by store() and update() - a film has to make
     * sense the same way whether it's being created or edited.
     */
    private function validationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'year' => 'required|integer|digits:4',
            'isbn' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'edition_number' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
