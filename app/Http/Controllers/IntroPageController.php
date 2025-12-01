<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateIntroPageRequest;
use App\Models\IntroPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class IntroPageController extends Controller
{
    public function show(): Response
    {
        $introPage = IntroPage::query()->firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Welkom bij jouw adventskalender',
                'body' => 'Hier zie je straks een warme uitleg over hoe jouw gepersonaliseerde adventskalender werkt. Een beheerder kan deze tekst aanpassen in het beheermenu.',
            ]
        );

        return Inertia::render('Intro', [
            'introPage' => $introPage,
        ]);
    }

    public function edit(): Response
    {
        Gate::authorize('admin');

        $introPage = IntroPage::query()->firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Welkom bij jouw adventskalender',
                'body' => 'Hier zie je straks een warme uitleg over hoe jouw gepersonaliseerde adventskalender werkt. Een beheerder kan deze tekst aanpassen in het beheermenu.',
            ]
        );

        return Inertia::render('Admin/IntroPage', [
            'introPage' => $introPage,
        ]);
    }

    public function update(UpdateIntroPageRequest $request): RedirectResponse
    {
        Gate::authorize('admin');

        $introPage = IntroPage::query()->firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Welkom bij jouw adventskalender',
                'body' => 'Hier zie je straks een warme uitleg over hoe jouw gepersonaliseerde adventskalender werkt. Een beheerder kan deze tekst aanpassen in het beheermenu.',
            ]
        );

        $introPage->update($request->validated());

        return redirect()
            ->route('admin.intro.edit')
            ->with('success', __('Introductietekst bijgewerkt.'));
    }
}
