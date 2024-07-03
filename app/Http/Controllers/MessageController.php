<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\MessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Recipiants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MessageController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $validator = Validator::make($request->all(), (new MessageRequest)->rules());
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        /**
         * @var App\Models\User $user
         */
        $user = auth()->user();
        $user_id = $request->user_id;

        DB::beginTransaction();
        try
        {
            $conversation = Conversation::query()
                ->whereHas('participants', function ($builder) use ($user_id, $user) {
                    $builder->join('participants as participants2','participants2.conversation_id','=','participants.conversation_id')
                            ->where('participants.user_id','=',$user_id)
                            ->where('participants2.user_id','=',$user->id);
            })->first();

            if(!$conversation)
            {
                $conversation = Conversation::create();
                $conversation->participants()->attach([$user->id, $user_id]);
            }

            $message = $conversation->messages()->create([
                'user_id' => $user->id,
                'message' => $request->message
            ]);

            $message->recipiants()->attach($user_id);

            $conversation->update([
                'last_message_id' => $message->id,
            ]);

            DB::commit();

            $message['user'] = $message->user->userable;

            return $this->sendResponse($this->show_message($message));
        } catch (Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Recipiants::query()->where('message_id', $id)->delete();
        Message::query()->find($id)->delete();

        return $this->sendResponse();
    }

    public function extracted_data($messages)
    {
        if (empty($messages))
        {
            return $this->sendResponse([]);
        }

        foreach ($messages as $key => $message)
        {
            $messages[$key] = $this->show_message($message);
        }

        $messages = $messages->sortByDesc('date');
        $messages = array_values($messages->all());

        return $this->sendResponse($messages);
    }

    public function show_message($message)
    {
        return [
            'id'              => $message->id,
            'message'         => $message->message,
            'user_id'         => $message->user_id,
            'date'            => $message->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
