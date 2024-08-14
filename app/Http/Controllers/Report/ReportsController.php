<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\BaseController;
use App\Http\Requests\replyReportRequest;
use App\Http\Requests\StoreReportRequest;
use App\Mail\AdminReply;
use App\Models\Reports;
use App\Models\User;
use App\Notifications\UserNotification;
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
        $reports = $reports->sortByDesc('created_at');
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
        // Validate the data
        $validator = Validator::make($request->all(), (new StoreReportRequest())->rules());
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $report = Reports::create([
            'user_id' => Auth::id(),
            'body'    => $request->body,
            'title'   => $request->title,
        ]);

        // To notify the user
        $user = User::find(1);
        $auth_user = auth()->user()->userable;
        $user_name = $auth_user->first_name . ' ' . $auth_user->last_name;
        $user->notify(new UserNotification('New Report Received', 'Attention: A report from ' . $user_name . ' has been sent to your dashboard. Please check it at your earliest convenience', ['report_id' => $report->id]));

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
        // Validate the data
        $validator = Validator::make($request->all(),(new replyReportRequest())->rules());
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $reply = [
            'body'  => $request->body,
            'title' => $request->title,
        ];

        $report = Reports::find($request->report_id);

        // make the report is read
        $report->update([
            'read_at' => Carbon::now()
        ]);

        $user = User::find($report->user_id);

        // send email to user
        Mail::to($user->email)->send(new AdminReply($reply));

        return $this->sendResponse();
    }

    public function newReports()
    {
        $reports = Reports::query()->whereNull('read_at')->get();
        return $this->sendresponse($reports);
    }
}
