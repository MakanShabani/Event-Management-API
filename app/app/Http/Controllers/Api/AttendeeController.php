<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AttendeeController extends Controller
{

    public function index(Event $event)
    {
        $attendees = $event->attendees()->latest()->paginate(10);
        return AttendeeResource::collection($attendees);
    }


    public function store(Request $request, Event $event)
    {
        $attendee = $event->attendees()->create(
            ['user_id' => 1]
        );

        return new AttendeeResource($attendee);
    }


    public function show(string $eventId, Attendee $attendee)
    {
        try {
            $event = Event::findOrFail($eventId);
            return new AttendeeResource($attendee);
        } catch (ModelNotFoundException $ex) {

            return response()->json(['message' => 'Event does not exists.'], 404);
        }
    }


    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $eventId, Attendee $attendee)
    {

        $attendee->delete();
        return response()->noContent();
    }
}
