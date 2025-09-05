<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index()
    {
        Request::validate([
            'starts_at' => ['nullable', 'date:Y-m-d'],
            'ends_at' => ['nullable', 'date:Y-m-d'],
        ]);

        return Inertia::render('Events/Index', [
            'starts_at' => Request::get('starts_at'),
            'ends_at' => Request::get('ends_at'),
            // utilisation du scope isBetween et orderByDate et where user_id
            'events' => Event::isBetween(Request::get('starts_at'), Request::get('ends_at'))
                ->where('user_id', auth()->id())
                ->orderByDate()
                ->get()
        ]);
    }

    public function store()
    {
        $data = Request::validate([
            'title' => ['required', 'max:255'],
            'starts_at' => ['required', 'date:Y-m-d H:i:s'],
            'ends_at' => ['required', 'date:Y-m-d H:i:s']
        ]);

        Event::create([
            ...$data,
            'user_id' => auth()->id()
        ]);

        return Redirect::back();
    }

    public function update(Event $event)
    {
        $data = Request::validate([
            'title' => ['required', 'max:255'],
            'starts_at' => ['required', 'date:Y-m-d H:i:s'],
            'ends_at' => ['required', 'date:Y-m-d H:i:s']
        ]);

        $event->update([
            ...$data,
        ]);

        return Redirect::back();
    }

    public function destroy(Event $event)
    {
        // Vérification que l'utilisateur peut supprimer cet événement
        if ($event->user_id !== auth()->id()) {
            abort(403, 'Vous ne pouvez pas supprimer cet événement.');
        }

        $event->delete();

        return Redirect::back();
    }
}
