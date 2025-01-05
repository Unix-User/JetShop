<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clients = Client::where('team_id', $user->currentTeam->id)
                         ->paginate($request->input('per_page', 15));
        return response()->json($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'email' => 'required|string',
            'phone' => 'string'
        ]);

        $client = new Client();
        $client->name = $request->name;
        $client->address = $request->address;
        $client->email = $request->email;
        $client->phone = $request->phone;
        $client->team_id = $user->currentTeam->id;
        $client->save();

        return redirect()->route('clients.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'string|max:255',
            'address' => 'string',
            'email' => 'string|email',
            'phone' => 'string|nullable'
        ]);

        $client->update($request->only('name', 'address', 'email', 'phone'));

        return response()->json($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['message' => 'Client deleted successfully'], 200);
    }
}

