<?php

namespace Modules\Contract\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Contract\Entities\Contract;
use Modules\Contract\Entities\ContractType;
use Modules\Contract\Entities\ContractAttechment;
use Modules\Contract\Entities\ContractComment;
use Modules\Contract\Entities\ContractNote;
use Illuminate\Support\Facades\Validator;
use Modules\Contract\Entities\RenewContract;
use Modules\Contract\Events\CopyContract;
use Modules\Contract\Events\CreateContract;
use Modules\Contract\Events\DestroyContract;
use Modules\Contract\Events\SendMailContract;
use Modules\Contract\Events\StatusChangeContract;
use Modules\Contract\Events\UpdateContract;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(Auth::user()->isAbleTo('contract manage'))
        {
            if(Auth::user()->type == 'company')
            {
                $contracts = Contract::select('contracts.*', 'contract_types.name as type','users.name as user_name','projects.name as project_name')->leftJoin('contract_types', 'contracts.type', '=', 'contract_types.id')->leftJoin('users', 'contracts.user_id', '=', 'users.id')->leftJoin('projects', 'contracts.project_id', '=', 'projects.id')->contractonly()->where('contracts.created_by', '=', creatorId())->where('contracts.workspace', getActiveWorkSpace())->get();


                $curr_month  = Contract::where('created_by', '=', creatorId())->whereMonth('start_date', '=', date('m'))->get();
                $curr_week   = Contract::where('created_by', '=', creatorId())->whereBetween(
                    'start_date', [
                                    \Carbon\Carbon::now()->startOfWeek(),
                                    \Carbon\Carbon::now()->endOfWeek(),
                                ]
                )->get();
                $last_30days = Contract::where('created_by', '=', creatorId())->whereDate('start_date', '>', \Carbon\Carbon::now()->subDays(30))->get();

                // Contracts Summary
                $cnt_contract                = [];
                $cnt_contract['total']       = Contract::getContractSummary($contracts);
                $cnt_contract['this_month']  = Contract::getContractSummary($curr_month);
                $cnt_contract['this_week']   = Contract::getContractSummary($curr_week);
                $cnt_contract['last_30days'] = Contract::getContractSummary($last_30days);

                

                return view('contract::contracts.index', compact('contracts', 'cnt_contract'));
            }
            else
            {
                $contracts   = Contract::contractonly()->where('user_id', '=', Auth::user()->id)->get();


                $contracts   = Contract::select('contracts.*', 'contract_types.name as type','users.name as user_name','projects.name as project_name')->leftJoin('contract_types', 'contracts.type', '=', 'contract_types.id')->leftJoin('users', 'contracts.user_id', '=', 'users.id')->leftJoin('projects', 'contracts.project_id', '=', 'projects.id')->where('user_id', '=', Auth::user()->id)->get();

                $curr_month  = Contract::where('user_id', '=', Auth::user()->id)->whereMonth('start_date', '=', date('m'))->get();
                $curr_week   = Contract::where('user_id', '=', Auth::user()->id)->whereBetween(
                    'start_date', [
                                    \Carbon\Carbon::now()->startOfWeek(),
                                    \Carbon\Carbon::now()->endOfWeek(),
                                ]
                )->get();
                $last_30days = Contract::where('user_id', '=', Auth::user()->id)->whereDate('start_date', '>', \Carbon\Carbon::now()->subDays(30))->get();

                // Contracts Summary
                $cnt_contract                = [];
                $cnt_contract['total']       = Contract::getContractSummary($contracts);
                $cnt_contract['this_month']  = Contract::getContractSummary($curr_month);
                $cnt_contract['this_week']   = Contract::getContractSummary($curr_week);
                $cnt_contract['last_30days'] = Contract::getContractSummary($last_30days);

                return view('contract::contracts.index', compact('contracts', 'cnt_contract'));
            }
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
        if(Auth::user()->isAbleTo('contract create'))
        {
            $user       = User::where('workspace_id',getActiveWorkSpace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id');
            $contractType = ContractType::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('name', 'id');
            if(module_is_active('CustomField')){
                $customFields =  \Modules\CustomField\Entities\CustomField::where('workspace_id',getActiveWorkSpace())->where('module', '=', 'contract')->where('sub_module','contract')->get();
            }else{
                $customFields = null;
            }
            return view('contract::contracts.create', compact('contractType','user','customFields'));
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
        if(Auth::user()->isAbleTo('contract create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'subject' => 'required',
                                   'value' => 'required',
                                   'type' => 'required',
                                   'user_id' => 'required',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }

            $contract              = new Contract();
            if($request->contract_type == 'contract'){
                $last_id = DB::table('contracts')->where('contract_type', 'contract')->where('workspace',getActiveWorkSpace())->where('created_by', '=', creatorId())->max('contract_id');
                $contract_id = $last_id + 1;
                $contract->contract_id = $contract_id;
             }
            $contract->subject     = $request->subject;
            $contract->user_id     = (Auth::user()->type == 'company') ? $request->user_id : Auth::user()->id;
            $contract->project_id  = $request->project_id;
            $contract->value       = $request->value;
            $contract->contract_type = 'contract';
            $contract->type        = $request->type;
            $contract->start_date  = $request->start_date;
            $contract->end_date    = $request->end_date;
            $contract->notes       = $request->notes;
            $contract->workspace   = getActiveWorkSpace();
            $contract->created_by  = creatorId();
            $contract->save();


            if(module_is_active('CustomField'))
            {
                \Modules\CustomField\Entities\CustomField::saveData($contract, $request->customField);
            }

            event(new CreateContract($request,$contract));

            return redirect()->back()->with('success', __('Contract successfully created!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getProject(Request $request)
    {

        if($request->user_id == 0)
        {
            $project = \Modules\Taskly\Entities\ClientProject::get()->pluck('name', 'id')->toArray();
        }
        else
        {
            $projectss = \Modules\Taskly\Entities\ClientProject::where('client_id',$request->user_id)->get()->pluck('project_id');
            $project  = \Modules\Taskly\Entities\Project::whereIn('id',$projectss)->projectonly()->get()->pluck('name','id');

          
        }

        return response()->json($project);

    }

    public static function contractNumber()
    {
        $latest = Contract::where('created_by', '=', creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->id + 1;
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $contract = Contract::select('contracts.*','contract_types.name as type','users.name as user_name','projects.name as project_name')->leftJoin('contract_types', 'contracts.type', '=', 'contract_types.id')->leftJoin('users', 'contracts.user_id', '=', 'users.id')->leftJoin('projects', 'contracts.project_id', '=', 'projects.id')->where('contracts.id','=',$id)->first();
        $renewContract = RenewContract::where('contract_id',$contract->id)->latest()->first();
        if($contract){
            if($contract->created_by == creatorId() && $contract->workspace == getActiveWorkSpace())
            {
                $client   = $contract->client;
                $comments = ContractComment::where('contract_id',$contract->id)->get();
                $notes = ContractNote::where('contract_id',$contract->id)->get();
                $files = ContractAttechment::where('contract_id',$contract->id)->get();
                $renewContracts = RenewContract::where('contract_id',$contract->id)->get();
                return view('contract::contracts.show', compact('contract', 'client','comments','notes','files','renewContracts','renewContract'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }else
        {
            return redirect()->back()->with('error', __('Contract Note Found.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Contract $contract)
    {
        if(Auth::user()->isAbleTo('contract edit'))
        {
            if($contract->created_by == creatorId() && $contract->workspace == getActiveWorkSpace())
            {
                $user       = User::where('workspace_id',getActiveWorkSpace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id');
                $contractType = ContractType::where('created_by', '=',creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('name', 'id');
                $project  = \Modules\Taskly\Entities\Project::where('id',$contract->project_id)->where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->projectonly()->get()->pluck('name', 'id');

                $renewContract = RenewContract::where('contract_id',$contract->id)->latest()->first();
                
                if(module_is_active('CustomField')){
                    $contract->customField = \Modules\CustomField\Entities\CustomField::getData($contract, 'contract','contract');
                    $customFields             = \Modules\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'contract')->where('sub_module','contract')->get();
                }else{
                    $customFields = null;
                }

                return view('contract::contracts.edit', compact('contract', 'contractType', 'user','customFields','project','renewContract'));
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
    public function update(Request $request,Contract $contract)
    {
       
        if(Auth::user()->isAbleTo('contract edit'))
        {
            if($contract->created_by == creatorId() && $contract->workspace == getActiveWorkSpace())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                        'subject' => 'required',
                                        'value' => 'required',
                                        'type' => 'required',
                                        'user_id' => 'required',
                                        'start_date' => 'required',
                                        'end_date' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('contract.index')->with('error', $messages->first());
                }


                $contract->user_id     = $request->user_id;
                $contract->project_id  = $request->project_id;
                $contract->subject     = $request->subject;
                $contract->value       = $request->value;
                $contract->contract_type = 'contract';
                $contract->type        = $request->type;
                $contract->start_date  = $request->start_date;
                $contract->end_date    = $request->end_date;
                $contract->notes       = $request->notes;
                $contract->save();

                if(module_is_active('CustomField'))
                {
                    \Modules\CustomField\Entities\CustomField::saveData($contract, $request->customField);
                }
                event(new UpdateContract($request,$contract));

                return redirect()->back()->with('success', __('Contract successfully updated!'));
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

    public function contract_status_edit(Request $request, $id)
    {
        $contract = Contract::find($id);
        $contract->status   = $request->status;
        $contract->save();
        event(new StatusChangeContract($request,$contract));
    }

    public function destroy($id)
    {
        if(Auth::user()->isAbleTo('contract delete'))
        {
            $contract =Contract::find($id);
            if($contract->created_by == creatorId() && $contract->workspace == getActiveWorkSpace())
            {
                event(new DestroyContract($contract));

                $attechments = $contract->ContractAttechment()->get()->each;

                foreach($attechments->items as $attechment){
                    delete_file($attechment->files);
                    $attechment->delete();
                }

                $contract->ContractComment()->get()->each->delete();
                $contract->ContractNote()->get()->each->delete();
                $contract->delete();
                if(module_is_active('CustomField'))
                {
                    $customFields = \Modules\CustomField\Entities\CustomField::where('module','contract')->where('sub_module','contract')->get();
                    foreach($customFields as $customField)
                    {
                        $value = \Modules\CustomField\Entities\CustomFieldValue::where('record_id', '=',$id)->where('field_id',$customField->id)->first();
                        if(!empty($value)){
                            $value->delete();
                        }
                    }
                }

                return redirect()->back()->with('success', __('Contract successfully deleted!'));
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

    public function descriptionStore($id, Request $request)
    {
        if(Auth::user()->type == 'company')
        {
            $contract        =Contract::find($id);
            $contract->description = $request->description;
            $contract->save();
            return redirect()->back()->with('success', __('Description successfully saved.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

    public function fileUpload($id, Request $request)
    {
        $contract = Contract::find($id);
        if($contract->created_by == creatorId() && $contract->workspace == getActiveWorkSpace())
        {
            $request->validate(['file' => 'required']);
            $files = $request->file->getClientOriginalName();
            $path = upload_file($request,'file',$files,'contract_file');
                if($path['flag'] == 1){
                    $file = $path['url'];
                }
                else{
                    return redirect()->back()->with('error', __($path['msg']));
                }
            $file                 = ContractAttechment::create(
                [
                    'contract_id' => $request->contract_id,
                    'user_id' => Auth::user()->id,
                    'workspace'=>getActiveWorkSpace(),
                    'file_name' => $files,
                    'files' => $file,
                ]
            );
            $return               = [];
            $return['is_success'] = true;
            $return['download']   = route(
                'contracts.file.download', [
                                            $contract->id,
                                            $file->id,
                                        ]
            );
            $return['delete']     = route(
                'contracts.file.delete', [
                                        $contract->id,
                                        $file->id,
                                    ]
            );

            return response()->json(
                [
                    'is_success' => true,
                    'success' => __('Status successfully updated!'),
                ], 200
            );
        }
        else
        {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ], 401
            );
        }

    }

    public function fileDownload($id, $file_id)
    {
            $contract = Contract::find($id);
            if($contract->created_by == creatorId() && $contract->workspace == getActiveWorkSpace())
            {
                $file = ContractAttechment::find($file_id);
                if($file)
                {
                    $file_path = get_base_file($file->files);

                    // $files = $file->files;
                    return \Response::download(
                        $file_path, $file->files, [
                                      'Content-Length: ' . get_size($file_path),
                                  ]
                    );
                }
                else
                {
                    return redirect()->back()->with('error', __('File is not exist.'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }

    }


    public function fileDelete($id, $file_id)
    {

            $contract = Contract::find($id);
            $file = ContractAttechment::find($file_id);
            if($file)
            {
                $path = get_base_file($file->files);
                if(file_exists($path))
                {
                    \File::delete($path);
                }
                $file->delete();

                return redirect()->back()->with('success', __('Attechment successfully delete.'));
            }
            else
            {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('File is not exist.'),
                    ], 200
                );
            }
    }

    public function commentStore(Request $request ,$id)
    {
        if(Auth::user()->isAbleTo('comment create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'comment' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $contract              = new ContractComment();
            $contract->comment     = $request->comment;
            $contract->contract_id = $id;
            $contract->workspace = getActiveWorkSpace();
            $contract->user_id     = Auth::user()->id;
            $contract->save();


            return redirect()->back()->with('success', __('comments successfully created!') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''))->with('status', 'comments');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function commentDestroy($id)
    {
        if(\Auth::user()->isAbleTo('comment delete'))
        {
                $contract = ContractComment::find($id);
                $contract->delete();

                return redirect()->back()->with('success', __('Comment successfully deleted!'));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function noteStore($id, Request $request)
    {
        if(\Auth::user()->isAbleTo('contract note create'))
        {
                $contract              = Contract::find($id);
                $notes                 = new ContractNote();
                $notes->contract_id    = $contract->id;
                $notes->note           = $request->note;
                $notes->user_id        = Auth::user()->id;
                $notes['workspace'] = getActiveWorkSpace();
                $notes->created_by     = creatorId();
                $notes->save();
                return redirect()->back()->with('success', __('Note successfully saved.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied'));
        }

    }


    public function noteDestroy($id)
    {
        if(\Auth::user()->isAbleTo('contract note delete'))
        {
            $contract = ContractNote::find($id);
            if($contract->created_by ==creatorId() && $contract->workspace == getActiveWorkSpace())
            {
                $contract->delete();

                return redirect()->back()->with('success', __('Note successfully deleted!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }


    public function renewcontract($id)
    {
        if(Auth::user()->isAbleTo('renewcontract create'))
        {
                $contract = Contract::find($id);
                $renewContract = RenewContract::where('contract_id',$id)->latest()->first();
                return view('contract::contracts.renewcontract_create', compact('contract','renewContract'));
        
        } else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
      

    }


    public function renewcontractstore($id, Request $request)
    {

        if(Auth::user()->isAbleTo('renewcontract create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                
                                'value' => 'required',
                                'start_date' => 'required',
                                'end_date' => 'required',
                            ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.show')->with('error', $messages->first());
            }
        

            $renewContract              = new RenewContract();
            $renewContract->contract_id = $id;
            $renewContract->value       = $request->value;
            $renewContract->start_date  = $request->start_date;
            $renewContract->end_date    = $request->end_date;
            $renewContract->save();

            return redirect()->back()->with('success', __('Contract Renew successfully.'));
        } else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        
    }

    public function renewcontractDelete($id)
    { 

        if(Auth::user()->isAbleTo('renewcontract delete'))
        {
            $renewContract = RenewContract::find($id);
            $renewContract->delete();
            return redirect()->back()->with('success', __('Contract Renew successfully deleted!'));
        }else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function copycontract($id)
    {
        if(\Auth::user()->isAbleTo('contract create'))
        {
            $user       = User::where('workspace_id',getActiveWorkSpace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id');
            $contract = Contract::find($id);
            $contractType = ContractType::where('created_by', '=',creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('name', 'id');
            if(module_is_active('CustomField')){
                $contract->customField = \Modules\CustomField\Entities\CustomField::getData($contract, 'contract','contract');
                $customFields          = \Modules\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'contract')->where('sub_module','contract')->get();
            }else{
                $customFields = null;
            }

            return view('contract::contracts.copy', compact('contract', 'contractType', 'user','customFields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }


    public function copycontractstore(Request $request)
    {
        if(\Auth::user()->isAbleTo('contract create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'subject' => 'required',
                                'value' => 'required',
                                'type' => 'required',
                                'user_id' => 'required',
                                'start_date' => 'required',
                                'end_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }

            $contract              = new Contract();
            if($request->contract_type == 'contract'){
                $last_id = DB::table('contracts')->where('contract_type', 'contract')->where('workspace',getActiveWorkSpace())->where('created_by', '=', creatorId())->max('contract_id');
                $contract_id = $last_id + 1;
                $contract->contract_id = $contract_id;
             }
            $contract->subject     = $request->subject;
            $contract->user_id     = $request->user_id;
            $contract->project_id  = $request->project_id;
            $contract->value       = $request->value;
            $contract->contract_type = 'contract';
            $contract->type        = $request->type;
            $contract->start_date  = $request->start_date;
            $contract->end_date    = $request->end_date;
            $contract->notes       = $request->notes;
            $contract->workspace   = getActiveWorkSpace();
            $contract->created_by  = creatorId();
            $contract->save();

            if(module_is_active('CustomField'))
            {
                \Modules\CustomField\Entities\CustomField::saveData($contract, $request->customField);
            }

            event(new CopyContract($request,$contract));

            return redirect()->route('contract.index')->with('success', __('Contract successfully created!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function printContract($id)
    {
        $contract  = Contract::findOrFail($id);
        $contract_type = ContractType::where('id',$contract->type)->first();

        //Set your logo

        $dark_logo    = get_file(sidebar_logo());
        $img = (!empty($dark_logo) ? $dark_logo : get_file('uploads/logo/logo_dark.png'));
        $company_id=$contract->created_by;
        $workspace_id = $contract->workspace;
        return view('contract::contracts.contract_view', compact('contract','contract_type','img','company_id','workspace_id'));

    }


    public function pdffromcontract($contract_id)
    {
        $id = \Illuminate\Support\Facades\Crypt::decrypt($contract_id);

        $contract  = Contract::findOrFail($id);
        $contract_type = ContractType::where('id',$contract->type)->first();
        if(Auth::check())
        {
            $usr=Auth::user();
        }
        else
        {
            $usr=User::where('id',$contract->created_by)->first();
        }
        $dark_logo    = get_file(sidebar_logo());
        $img = (!empty($dark_logo) ? $dark_logo : get_file('uploads/logo/logo_dark.png'));
        $company_id=$contract->created_by;
        $workspace_id = $contract->workspace;
        return view('contract::contracts.template', compact('contract','usr','contract_type','img','company_id','workspace_id'));

    }

    public function signature($id)
    {
        $contract = Contract::find($id);
        return view('contract::contracts.signature', compact('contract'));
    }


    public function signatureStore(Request $request)
    {
        $contract              = Contract::find($request->contract_id);
        if(Auth::user()->type == 'company'){
            $contract->owner_signature       = $request->owner_signature;
        }
        else{

            $contract->client_signature       = $request->client_signature;
        }

        $contract->save();

        return response()->json(
            [
                'Success' => true,
                'message' => __('Contract Signed successfully'),
            ], 200
        );

    }

    public function sendmailContract($id,Request $request)
    {

        if(Auth::user()->type == 'company')
        {
            $company_settings = getCompanyAllSetting();
            if(!empty($company_settings['Contract']) && $company_settings['Contract']  == true){
                $contract              = Contract::find($id);
                $contractArr = [
                    'contract_id' => $contract->id,
                ];
                $client = User::find($contract->user_id);
                $estArr = [
                    'email' => $client->email,
                    'contract_subject' => $contract->subject,
                    'contract_client' => $client->name,
                    'contract_start_date' => $contract->start_date,
                    'contract_end_date' =>$contract->end_date ,
                ];
                // Send Email
                $resp = EmailTemplate::sendEmailTemplate('Contract', [$client->id => $client->email], $estArr);

                event(new SendMailContract($request,$contract));

                return redirect()->back()->with('success', __('Mail Send successfully!') . ((isset($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            else{

                return redirect()->back()->with('error', __('Contract notification is off'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }



    public function setting(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'contract_prefix' => 'required',
        ]);
        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        else
        {
            $contract = [];
            $contract['contract_prefix'] =  $request->contract_prefix;
            foreach ($contract as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkSpace(),
                    'created_by' => creatorId(),
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            comapnySettingCacheForget();
            return redirect()->back()->with('success', __('Contract Setting save successfully'));
        }
    }

    public function grid()
    {
        if(\Auth::user()->isAbleTo('contract manage'))
        {
            if(Auth::user()->type == 'company'){
                $contracts   = Contract::where('contract_type','contract')->where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->get();
                $curr_month  = Contract::where('created_by', '=', creatorId())->whereMonth('start_date', '=', date('m'))->get();
                $curr_week   = Contract::where('created_by', '=', creatorId())->whereBetween(
                    'start_date', [
                                    \Carbon\Carbon::now()->startOfWeek(),
                                    \Carbon\Carbon::now()->endOfWeek(),
                                ]
                )->get();
                $last_30days = Contract::where('created_by', '=', creatorId())->whereDate('start_date', '>', \Carbon\Carbon::now()->subDays(30))->get();

                // Contracts Summary
                $cnt_contract                = [];
                $cnt_contract['total']       = Contract::getContractSummary($contracts);
                $cnt_contract['this_month']  = Contract::getContractSummary($curr_month);
                $cnt_contract['this_week']   = Contract::getContractSummary($curr_week);
                $cnt_contract['last_30days'] = Contract::getContractSummary($last_30days);

                return view('contract::contracts.grid', compact('contracts', 'cnt_contract'));
            }
            else
            {
                $contracts   = Contract::where('user_id', '=', Auth::user()->id)->get();
                $curr_month  = Contract::where('user_id', '=', Auth::user()->id)->whereMonth('start_date', '=', date('m'))->get();
                $curr_week   = Contract::where('user_id', '=', Auth::user()->id)->whereBetween(
                    'start_date', [
                                    \Carbon\Carbon::now()->startOfWeek(),
                                    \Carbon\Carbon::now()->endOfWeek(),
                                ]
                )->get();
                $last_30days = Contract::where('user_id', '=', Auth::user()->id)->whereDate('start_date', '>', \Carbon\Carbon::now()->subDays(30))->get();

                // Contracts Summary
                $cnt_contract                = [];
                $cnt_contract['total']       = Contract::getContractSummary($contracts);
                $cnt_contract['this_month']  = Contract::getContractSummary($curr_month);
                $cnt_contract['this_week']   = Contract::getContractSummary($curr_week);
                $cnt_contract['last_30days'] = Contract::getContractSummary($last_30days);

                return view('contract::contracts.grid', compact('contracts', 'cnt_contract'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

}
