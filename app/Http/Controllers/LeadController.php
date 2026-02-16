<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;

class LeadController extends Controller
{
    /**
     * Display all leads for the logged-in user's tenant
     */
    public function index()
    {
        $user = auth()->user();

        // Always filter by tenant
        $query = Lead::where('tenant_id', $user->tenant_id);

        // Example: if Sales Agent, limit to first 5 leads (optional)
        if ($user->hasRole('sales-agent')) {
            $query->take(5);
        }

        $leads = $query->get();

        return view('leads.index', compact('leads'));
    }

    /**
     * Store a new lead
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
        ]);

        Lead::create([
            'tenant_id' => auth()->user()->tenant_id, // 🔥 always include tenant_id
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
        ]);

        return redirect()->back()->with('success', 'Lead created successfully.');
    }

    /**
     * Delete a lead
     */
    public function destroy(Lead $lead)
    {
        // 🔥 Protect: only allow deletion if lead belongs to user's tenant
        if ($lead->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        $lead->delete();

        return redirect()->back()->with('success', 'Lead deleted successfully.');
    }
}
