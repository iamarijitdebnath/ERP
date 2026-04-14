<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesLeadInquiryRequest;
use App\Models\Sales\SalesLeadInquiry;
use App\Models\Sales\SalesLead;
use App\Models\Inventory\Item;
use App\Models\HRMS\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesLeadInquiryController extends Controller
{
    public function index(Request $request) {
        if ($request->expectsJson()) {
            $inquiries = SalesLeadInquiry::with(['lead', 'product', 'employee'])
                ->latest()
                ->paginate($request->input('limit', 25));
            return $this->apiResponse(200, "Fetched Inquiries", ['inquiries' => $inquiries]);
        }
        return view('pages.sales.sales-lead-inquiry.index');
    }

    public function create(Request $request) {
        $lead = null;
        if($request->has('lead_id')){
            $lead = SalesLead::find($request->lead_id);
        }
        $leads = SalesLead::forCurrentCompany()->get();
        $products = Item::forCurrentCompany()->get();
        $employees = Employee::forCurrentCompany()->get();
        return view('pages.sales.sales-lead-inquiry.show', compact('lead', 'leads', 'products', 'employees'));
    }

    public function store(SalesLeadInquiryRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            SalesLeadInquiry::create($data);
            DB::commit();
            return redirect()->route('sales.inquiry.index')->with('success', 'Inquiry added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add inquiry: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesLeadInquiry $inquiry) {
        $inquiry->load(['lead', 'product', 'employee', 'followups']);
        $leads = SalesLead::forCurrentCompany()->get();
        $products = Item::forCurrentCompany()->get();
        $employees = Employee::forCurrentCompany()->get();
        return view('pages.sales.sales-lead-inquiry.show', compact('inquiry', 'leads', 'products', 'employees'));
    }

    public function edit(SalesLeadInquiry $inquiry) {
        return $this->show($inquiry);
    }

    public function update(SalesLeadInquiryRequest $request, SalesLeadInquiry $inquiry)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $inquiry->update($data);
            DB::commit();
            return redirect()->route('sales.inquiry.index')->with('success', 'Inquiry updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update inquiry: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(SalesLeadInquiry $inquiry)
    {
        try {
            $inquiry->delete();
            return redirect()->back()->with('success', 'Inquiry deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }
}
