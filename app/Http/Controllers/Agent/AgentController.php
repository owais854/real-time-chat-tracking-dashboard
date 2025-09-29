<?php

namespace App\Http\Controllers\Agent;

use App\Events\VisitorTransferred;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\AgentVisitor;


class AgentController extends Controller
{
    public function __construct()
    {
        // only admin can manage agents
//        $this->middleware(['auth', 'role:agent']);
    }

    /**
     * Display a listing of the agents.
     */
    public function dashboard()
    {
        return view('agent.dashboard');
    }

    /**
     * Get all agents
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgents()
    {

        $agents = User::where('role', 'agent')
                     ->select('id', 'name', 'email')
                     ->get();



        return response()->json($agents);
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'new_agent_id' => 'required|integer|exists:users,id',
        ]);

        // Update if session_id exists, otherwise create new record
        $agentVisitor = AgentVisitor::updateOrCreate(
            ['session_id' => $request->session_id], // condition
            ['agent_id' => $request->new_agent_id]  // update or insert values
        );

        // Fire broadcast event so other agents' dashboards update in real-time
        event(new VisitorTransferred($request->session_id, $request->new_agent_id));

        return response()->json([
            'status' => 'ok',
            'agentVisitor' => $agentVisitor
        ]);
    }

}
