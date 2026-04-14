<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesLeadFollowupRequest;
use App\Models\Sales\SalesLeadFollowup;
use App\Models\Sales\SalesLeadInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesLeadFollowupController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $followups = SalesLeadFollowup::with(['inquiry.lead'])
                ->latest()
                ->paginate($request->input('limit', 25));
            return $this->apiResponse(200, "Fetched Followups", ['followups' => $followups]);
        }
        return view('pages.sales.sales-lead-followup.index');
    }

    public function create(Request $request) {
        $inquiry = null;
        if($request->has('inquiry_id')){
            $inquiry = SalesLeadInquiry::find($request->inquiry_id);
        }
        // Ideally filter inquiries by company?
        // Assuming SalesLeadInquiry::forCurrentCompany() scope exists or we use whereHas('lead', ...)
        $inquiries = SalesLeadInquiry::whereHas('lead', function($q){
            $q->forCurrentCompany();
        })->with(['lead', 'product'])->get();

        return view('pages.sales.sales-lead-followup.show', compact('inquiry', 'inquiries'));
    }

    public function store(SalesLeadFollowupRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            SalesLeadFollowup::create($data);
            DB::commit();
            return redirect()->route('sales.followup.index')->with('success', 'Follow-up added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add follow-up: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesLeadFollowup $followup) {
        $followup->load('inquiry.lead');
        $inquiries = SalesLeadInquiry::whereHas('lead', function($q){
            $q->forCurrentCompany();
        })->with(['lead', 'product'])->get();
        return view('pages.sales.sales-lead-followup.show', compact('followup', 'inquiries'));
    }

    public function edit(SalesLeadFollowup $followup) {
        return $this->show($followup);
    }

    public function update(SalesLeadFollowupRequest $request, SalesLeadFollowup $followup)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $followup->update($data);
            DB::commit();
            return redirect()->route('sales.followup.index')->with('success', 'Follow-up updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update follow-up: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(SalesLeadFollowup $followup)
    {
        try {
            $followup->delete();
            return redirect()->back()->with('success', 'Follow-up deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }
}
