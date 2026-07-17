<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientMdmConfiguration;
use App\Models\MdmDevice;
use Illuminate\Http\Request;

class ClientMdmConfigController extends Controller
{
    public function index()
    {
        $clients = Client::with('mdmConfigurations')->orderBy('name')->get();

        return view('client-mdm-configs.index', compact('clients'));
    }

    public function edit(Client $client)
    {
        $configs = MdmDevice::whereNotNull('configuration')
            ->where('configuration', '!=', '')
            ->select('configuration')
            ->distinct()
            ->orderBy('configuration')
            ->pluck('configuration');

        $deviceCounts = MdmDevice::whereNotNull('configuration')
            ->where('configuration', '!=', '')
            ->selectRaw('configuration, count(*) as cnt')
            ->groupBy('configuration')
            ->pluck('cnt', 'configuration');

        $assigned = $client->mdmConfigurations()->pluck('configuration')->toArray();

        return view('client-mdm-configs.edit', compact('client', 'configs', 'deviceCounts', 'assigned'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'configurations'   => 'array',
            'configurations.*' => 'string',
        ]);

        $selected = $request->input('configurations', []);

        $client->mdmConfigurations()->whereNotIn('configuration', $selected)->delete();

        $existing = $client->mdmConfigurations()->pluck('configuration')->toArray();
        foreach (array_diff($selected, $existing) as $configuration) {
            ClientMdmConfiguration::create([
                'client_id'     => $client->id,
                'configuration' => $configuration,
            ]);
        }

        return redirect()->route('client-mdm-configs.index')
            ->with('success', "MDM configurations updated for {$client->name}.");
    }
}
