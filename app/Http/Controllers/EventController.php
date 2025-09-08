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
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $eventsQuery = Event::where('user_id', auth()->id())
            ->isBetween(Request::get('starts_at'), Request::get('ends_at'))
            ->orderByDate();

        // Pagination avec 25 événements par page
        $events = $eventsQuery->paginate(25)->withQueryString();

        return Inertia::render('Events/Index', [
            'starts_at' => Request::get('starts_at'),
            'ends_at' => Request::get('ends_at'),
            'events' => $events
        ]);
    }

    public function store()
    {
        $data = Request::validate([
            'title' => ['required', 'max:255'],
            'starts_at' => ['required', 'date:Y-m-d H:i:s'],
            'ends_at' => ['required', 'date:Y-m-d H:i:s']
        ]);

        // Validation supplémentaire : starts_at doit être antérieur à ends_at
        if (strtotime($data['starts_at']) >= strtotime($data['ends_at'])) {
            return Redirect::back()->withErrors([
                'starts_at' => 'La date de début doit être antérieure à la date de fin.'
            ]);
        }

        Event::create([
            ...$data,
            'user_id' => auth()->id()
        ]);

        return Redirect::back();
    }

    public function update(Event $event)
    {
        // Vérification que l'utilisateur peut modifier cet événement
        if ($event->user_id !== auth()->id()) {
            abort(403, 'Vous ne pouvez pas modifier cet événement.');
        }

        $data = Request::validate([
            'title' => ['required', 'max:255'],
            'starts_at' => ['required', 'date:Y-m-d H:i:s'],
            'ends_at' => ['required', 'date:Y-m-d H:i:s']
        ]);

        // Validation supplémentaire : starts_at doit être antérieur à ends_at
        if (strtotime($data['starts_at']) >= strtotime($data['ends_at'])) {
            return Redirect::back()->withErrors([
                'starts_at' => 'La date de début doit être antérieure à la date de fin.'
            ]);
        }

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
