<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\BaseController;
use App\Http\Requests\replyReportRequest;
use App\Http\Requests\StoreReportRequest;
use App\Mail\AdminReply;
use App\Models\Reports;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ReportsController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = Reports::all();
        return $this->sendResponse($reports);
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
        $StoreReportRequest = new  StoreReportRequest();
        $validator = Validator::make($request->all(), $StoreReportRequest->rules());

        if ($validator->fails())
        {
            return $this->sendError($validator->errors());
        }

        $report = Reports::create([
            'user_id' => Auth::id(),
            'body'    => $request->body,
            'title'   => $request->title,
        ]);

        return $this->sendResponse($report);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $report = Reports::find($id);
        if (is_null($report)) {
            return $this->sendError(['message' => 'There is not report with this ID']);
        }

        return $this->sendResponse($report);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reports $reports)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reports $reports)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reports $reports)
    {
        //
    }

    public function reply(Request $request)
    {
        $replyReportRequest = new replyReportRequest();
        $validator = Validator::make($request->all(),$replyReportRequest->rules());

        if ($validator->fails())
        {
            return $this->sendError($validator->errors());
        }

        $reply = [
            'body'  => $request->body,
            'title' => $request->title,
        ];

        $user = User::find($request->user_id);
        Mail::to($user->email)->send(new AdminReply($reply));
        return $this->sendResponse();
    }

    public function newReports()
    {
        $reports = Reports::query()->whereNull('read_at')->get();

        if($reports->isEmpty())
        {
            return $this->sendresponse([]);
        }

        $reports->toQuery()->update([
            'read_at' => Carbon::now(),
        ]);

        return $this->sendresponse($reports);
    }
}
