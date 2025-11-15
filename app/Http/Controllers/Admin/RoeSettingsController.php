<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoeSettings;
use Illuminate\Http\Request;

class RoeSettingsController extends Controller
{
    /**
     * Display ROE settings page
     */
    public function index()
    {
        // Load single DESTINATION ROE if available
        $roeSetting = RoeSettings::where('destination', 'DESTINATION')->first();
        return view('admin.settings.roe_settings', compact('roeSetting'));
    }

    /**
     * Store or update ROE settings
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'roe_value' => 'required|numeric|min:0.000001|max:100',
        ]);

        // Persist single DESTINATION ROE (global for destination section)
        $data = [
            'destination' => 'DESTINATION',
            'roe_value' => (float) $validated['roe_value'],
        ];

        $roe = RoeSettings::where('destination', 'DESTINATION')->first();
        if ($roe) {
            $roe->update($data);
            $message = 'ROE updated successfully!';
        } else {
            RoeSettings::create($data);
            $message = 'ROE created successfully!';
        }

        return back()->with('success', $message);
    }

    /**
     * Delete ROE setting
     */
    public function destroy($id)
    {
        // keep delete for compatibility but prevent deleting global DESTINATION accidentally
        $roe = RoeSettings::findOrFail($id);
        if ($roe->destination === 'DESTINATION') {
            return back()->with('error', 'Cannot delete global DESTINATION ROE.');
        }
        $roe->delete();
        return back()->with('success', 'ROE setting deleted successfully!');
    }

    /**
     * Get ROE by destination (API endpoint)
     */
    public function getByDestination($destination)
    {
        // Try exact destination first
        $roe = RoeSettings::where('destination', $destination)->first();

        // If not found, fallback to global DESTINATION ROE
        if (! $roe) {
            $roe = RoeSettings::where('destination', 'DESTINATION')->first();
        }

        if (! $roe) {
            return response()->json(['error' => 'ROE not found'], 404);
        }

        return response()->json(['roe_value' => $roe->roe_value]);
    }


    public function storeOcean(Request $request)
{
    $request->validate([
        'ocean_freight_roe' => 'required|numeric|min:0.00001|max:100'
    ]);

    $roe = RoeSettings::where('destination', 'DESTINATION')->first();

    if (! $roe) {
        $roe = new RoeSettings();
        $roe->destination = 'DESTINATION';
    }

    $roe->ocean_freight_roe = $request->ocean_freight_roe;
    $roe->save();

    return back()->with('success', 'Ocean Freight ROE updated!');
}

}
