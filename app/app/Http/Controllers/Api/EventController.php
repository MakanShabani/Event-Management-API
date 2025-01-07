<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Http\Traits\LoadRelationshipsTrait;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller implements HasMiddleware
{

    use LoadRelationshipsTrait;
    //Relations filed to use with LoadRelationshipsTrait
    private  array $relations = ['user', 'attendees', 'attendees.user'];


    //defining Middleware(s) for the Controllers routes
    public static function middleware(): array
    {
        return [
            new Middleware(middleware: [JwtMiddleware::class], except: ['index', 'show']),
        ];
    }



    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = $this->loadRelationships(Event::query());

        if (is_array($query))
            return response()->json($query, 422);

        return EventResource::collection($query->latest()->paginate());
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        //
        $validatedData = $request->validated();

        //Check if an event with exact name and start time exist or not, if it does not exist, we create a new event and save it, if exists we send back response with 409 conflict status code

        if (Event::withName($validatedData['name'])
            ->withStartTime($validatedData['start_time'])->exists()
        )
            return response()->json(['message' => 'An event with the provided data already exists'], 409);

        //Create new event and return it
        return Event::create([
            ...$validatedData,
            'user_id' => $request->user()->id
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return new EventResource(Event::findOrFail($id)->load('user', 'attendees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, string $id)
    {

        $validatedData = $request->validated();

        $event = Event::findOrFail($id);

        Gate::authorize('update', $event);

        $event->update($validatedData);

        return response()->noContent();
        // //Event does not exist
        // return response()->json(['message' => "Event with id:{$id} does not exist."], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Event::findOrFail($id)->delete();
        return response()->noContent();
    }
}
