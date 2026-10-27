<?php

namespace Modules\Contract\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Contract\Entities\Contract;
use Modules\Contract\Entities\ContractType;
use Modules\Contract\Events\CreateContractType;
use Modules\Contract\Events\DestroyContractType;
use Modules\Contract\Events\UpdateContractType;

class ContractTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(\Auth::user()->isAbleTo('contracttype manage'))
        {
            $contractTypes = ContractType::where('created_by', '=',creatorId())->where('workspace',getActiveWorkSpace())->get();

            return view('contract::contract_type.index')->with('contractTypes', $contractTypes);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if(\Auth::user()->isAbleTo('contracttype create'))
        {
            return view('contract::contract_type.create');
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if(\Auth::user()->isAbleTo('contracttype create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract_type.index')->with('error', $messages->first());
            }

            $contractType             = new ContractType();
            $contractType->name       = $request->name;
            $contractType->workspace = getActiveWorkSpace();
            $contractType->created_by = creatorId();
            $contractType->save();

            event(new CreateContractType($request,$contractType));

            return redirect()->route('contract_type.index')->with('success', __('Contract Type successfully created!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('contract::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(ContractType $contractType)
    {
        if(\Auth::user()->isAbleTo('contracttype edit'))
        {
            if($contractType->created_by == creatorId())
            {
                return view('contract::contract_type.edit', compact('contractType'));
            }
            else
            {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, ContractType $contractType)
    {
        if(\Auth::user()->isAbleTo('contracttype edit'))
        {
            if($contractType->created_by == creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required|max:20',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('contract_type.index')->with('error', $messages->first());
                }

                $contractType->name = $request->name;
                $contractType->save();

                event(new UpdateContractType($request,$contractType));

                return redirect()->route('contract_type.index')->with('success', __('Contract Type successfully updated!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(ContractType $contractType)
    {
        if(\Auth::user()->isAbleTo('contracttype delete'))
        {
            if($contractType->created_by == creatorId())
            {
                $contract = Contract::where('type',$contractType->id)->where('created_by',$contractType->created_by)->count();
                if($contract == 0)
                {
                    event(new DestroyContractType($contractType));
                    $contractType->delete();

                    return redirect()->route('contract_type.index')->with('success', __('Contract Type successfully deleted!'));
                }
                else{
                    return redirect()->back()->with('error', __('This Contract Type is Used on Contract.'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
