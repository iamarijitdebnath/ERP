<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesLeadRequest;
use App\Models\Sales\SalesLead;
use App\Models\HRMS\Employee;
use App\Models\Inventory\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesLeadController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $leads = SalesLead::forCurrentCompany()
                ->when($request->filled('q'), fn($q) => $q->search($request->q))
                ->latest()
                ->paginate($request->input('limit', 25))
                ->onEachSide(1);

            return $this->apiResponse(200, "Fetched Leads", ['leads' => $leads]);
        }

        return view('pages.sales.sales-lead.index');
    }

    public function create()
    {
        $lead = new SalesLead(); // Empty model for form
        return view('pages.sales.sales-lead.show', compact('lead'));
    }

    public function store(SalesLeadRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        try {
            SalesLead::create($data);
            return redirect()->back()->with('success', 'Lead created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lead creation failed: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesLead $lead)
    {
        $lead->load(['inquiries.product', 'inquiries.employee', 'inquiries.followups']);
        
        $products = Item::forCurrentCompany()->get();
        $employees = Employee::forCurrentCompany()->get(); // Assuming Employee has similar scope or is available

        return view('pages.sales.sales-lead.show', compact('lead', 'products', 'employees'));
    }

    public function update(SalesLeadRequest $request, SalesLead $lead)
    {
        $data = $request->validated();

        try {
            $lead->update($data);
            return redirect()->back()->with('success', 'Lead updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lead update failed: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(SalesLead $lead)
    {
        try {
            $lead->delete();
            return redirect()->route('sales.sales-lead.index')->with('success', 'Lead deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }
}
