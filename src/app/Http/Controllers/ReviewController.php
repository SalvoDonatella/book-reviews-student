<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * List the logged-in user's own reviews, across every film, most
     * recent first.
     */
    public function mine()
    {
        $reviews = auth()->user()->reviews()
            ->with('film')
            ->latest()
            ->paginate(12);

        return view('reviews.mine', compact('reviews'));
    }

    /**
     * Show the form for leaving a review on the given film.
     */
    public function create(Film $film)
    {
        return view('reviews.create', compact('film'));
    }

    /**
     * Store a newly created review for the given film.
     */
    public function store(Request $request, Film $film)
    {
        $validated = $request->validate($this->validationRules());

        // The migration's unique index on [film_id, user_id] already stops
        // a second row for this exact pair existing - this check exists so
        // trying anyway gets the same friendly redirect-back-with-errors
        // treatment as any other validation failure, rather than a raw
        // "Duplicate entry" database exception reaching the browser.
        if ($film->reviews()->where('user_id', auth()->id())->exists()) {
            return back()->withErrors([
                'rating' => 'You have already reviewed this film.',
            ])->withInput();
        }

        $validated['user_id'] = auth()->id();
        $film->reviews()->create($validated);

        return redirect()->route('films.show', $film);
    }

    /**
     * Show the form for editing the given review.
     */
    public function edit(Review $review)
    {
        $this->authorizeOwner($review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the given review.
     */
    public function update(Request $request, Review $review)
    {
        $this->authorizeOwner($review);

        $review->update($request->validate($this->validationRules()));

        return redirect()->route('films.show', $review->film);
    }

    /**
     * Delete the given review.
     */
    public function destroy(Review $review)
    {
        $this->authorizeOwner($review);

        // $review->film has to be read before delete() - afterwards the
        // row (and the foreign key that made this relationship work) is
        // gone, and there'd be nothing left to redirect back to.
        $film = $review->film;
        $review->delete();

        return redirect()->route('films.show', $film);
    }

    /**
     * Validation rules shared by store() and update() - a review still has
     * to make sense the second time it's saved, not just the first.
     */
    private function validationRules(): array
    {
        return [
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ];
    }

    /**
     * Guard edit/update/destroy against anyone but the review's own author.
     * A stand-in for the Policy class Part 2 introduces - Laravel's actual
     * mechanism for exactly this kind of check - kept this plain for now
     * so that lesson isn't taught early by accident.
     */
    private function authorizeOwner(Review $review): void
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
