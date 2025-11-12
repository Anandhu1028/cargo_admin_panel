<?php

namespace App\Http\Controllers\Admin;

use App\Models\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /** 
     * Display a listing of customers
     */
    public function index()
    {
        $customers = Customer::latest()->get();
        return view('admin.customer.index', compact('customers'));
    }

    /** 
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'Active';
        Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer added successfully.');
    }

    /** 
     * Show the form for editing the specified customer (modal)
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customer.edit', compact('customer'));
    }

    /** 
     * Update the specified customer
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /** 
     * Remove the specified customer from storage
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('delete-success', 'Customer deleted successfully.');
    }
}
