<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Google\Service\Dfareporting\Resource\Conversions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $conversations = $user->conversations()->with([
        'last_message',
        'participants' => function($builder) use($user){
            $builder->where('id','<>',$user->id);
        }])
        ->withCount([
        'recipiants as new messages' => function($builder) use($user){
            $builder->where('recipiants.user_id','=',$user->id)
            ->whereNull('read_at');
        }
        ])->get();
        return $this->sendResponse($conversations);

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
            $builder->where('id','<>', Auth::id());
        }])
        ->find($id)->messages;
        return $this->sendResponse($messages);

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
    public function destroy(Conversation $conversation)
    {
        //
    }
}
