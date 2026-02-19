<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OperatorManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title = 'Operator List';
        $title = 'Operator List';
        $operatorList = User::role('operator')
                            ->latest('id')->get();
        return view('master_manage.operator.list', compact('operatorList','title','page_title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $page_title = 'Operator Add';
        $title = 'Operator Add';
        return view('master_manage.operator.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'status'   => 'required'
        ]);

        $data['password'] = Hash::make($request->password);
        $data['club_id']  = Auth::user()->club_id;

        $user = User::create($data);
        $user->assignRole('operator');

        return redirect()
            ->route('manage-operators.index')
            ->with('success', 'Operator added successfully');
        // return redirect()->route('admin.schools.index')->with('success', 'School added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $page_title = 'Edit Operator';
        $title = 'Edit Operator';

        $operator = User::findOrFail($id);
        return view('master_manage.operator.edit', compact(
            'operator',
            'page_title',
            'title'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $operator = User::where('id', $id)
                        ->where('club_id', Auth::user()->club_id) // security restriction
                        ->firstOrFail();

        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . $operator->id,
            'status' => 'required',
            'password' => 'nullable|min:8'
        ]);

        // If password is filled, update it
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $operator->update($data);

        return redirect()
                ->route('manage-operators.index')
                ->with('success', 'Operator updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $operator = User::where('id', $id)
                    ->where('club_id', auth()->user()->club_id) // restrict by club
                    ->firstOrFail();
        $operator->delete();

        return redirect()->route('manage-operators.index')->with('success', 'Operator deleted successfully');
    }
}
