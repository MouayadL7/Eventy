<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Conversation;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $conversations = $user->conversations()->with([
            'last_message:message',
            'participants' => function($builder) use ($user) {
                $builder->where('participants.id', '<>', $user->id);
            }])
            ->withCount([
                'recipiants as new_messages' => function($builder) use ($user) {
                    $builder->where('recipiants.user_id', '=', $user->id)
                        ->whereNull('read_at');
                }
            ])->get();

        return $this->extracted_data($conversations);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $messages = Conversation::with(['participants' => function($builder) {
            $builder->where('participants.id','<>', Auth::id());
        }])
        ->find($id)->messages;
        return (new MessageController)->extracted_data($messages);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conversation $conversation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conversation $conversation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Participant::query()->where('user_id', Auth::id())
                            ->where('conversation_id', $id)
                            ->delete();
        return $this->sendResponse();
    }

    public function extracted_data($conversations)
    {
        if (empty($conversations))
        {
            return $this->sendResponse([]);
        }

        foreach ($conversations as $key => &$conversation)
        {
            $response = $this->getConversation($conversation);
            $conversations[$key] = $response->getData()->data;
        }

        $conversations = $conversations->sortByDesc('date');
        $conversations = array_values($conversations->all());

        return $this->sendResponse($conversations);
    }

    public function getConversation($conversation)
    {
        $conversation_data = [
            'id' => $conversation->id,
            'new_messages' => $conversation->new_messages,
            'date' => $conversation->created_at->format('Y-m-d H:i:s'),
            'last_message' => $conversation->last_message->message,
        ];

        if (is_null($conversation->participants->first()))
            $conversation_data['participant'] = __('User');
        else {
            $participant = $conversation->participants[0];
            $conversation_data['participant'] = [
                'id'    => $participant->id,
                'name'  => $participant->userable->first_name . ' ' . $participant->userable->last_name,
                'image' => $participant->userable->image,
            ];
        }

        return $this->sendResponse($conversation_data);
    }
}
