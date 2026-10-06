<?php

namespace Database\Seeders;

use App\Models\Director;
use App\Models\Film;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
    /**
     * Seed the films table.
     *
     * Films are a weak entity here - a film is meaningless without at
     * least one director to attach to the director_film pivot - so this must
     * run after DirectorSeeder.
     */
    public function run(): void
    {
        $directors = Director::all();

        $films = Film::factory(20)->create();

        // Most films have a single director.
        $films->each(function (Film $film) use ($directors) {
            $films->directors()->attach($directors->random());
        });

        // A couple of films are co-written - attach a second, different
        // director to two randomly chosen films.
        $films->random(2)->each(function (Film $film) use ($directors) {
            $firstDirectorId = $films->directors->first()->id;

            $secondDirector = $directors
                ->reject(fn (Director $director) => $director->id === $firstDirectorId)
                ->random();

            $film->directors()->attach($secondDirector);
        });
    }
}
