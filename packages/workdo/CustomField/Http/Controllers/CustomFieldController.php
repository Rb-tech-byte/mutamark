<?php

namespace Modules\CustomField\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CustomField\Entities\CustomField;
use Modules\CustomField\Entities\CustomFieldsModuleList;
use Modules\CustomField\Events\CreateCustomField;
use Modules\CustomField\Events\UpdateCustomField;
use Nwidart\Modules\Facades\Module;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('customfield manage')) {
            $custom_fields = CustomField::where('created_by', '=', creatorId())->get();
            return view('customfield::index', compact('custom_fields'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('customfield create'))
        {
            $modules = CustomFieldsModuleList::select('module', 'sub_module')->get();
            $module_custumefield = [];
            foreach ($modules as $module) {
                if(module_is_active($module->module) || $module->module == 'Base')
                {
                    $sub_module_custumefield = CustomFieldsModuleList::select('module', 'sub_module')->where('module', $module->module)->get();
                    $temp = [];
                    foreach ($sub_module_custumefield as $sb) {
                        $temp[strtolower($module->module) . '-' . strtolower($sb->sub_module)] = $sb->sub_module;
                    }
                    $module_custumefield[Module_Alias_Name($module->module)] = $temp;

                }
            }
            $types = CustomField::$fieldTypes;
            return view('customfield::create', compact('types', 'module_custumefield'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        if (Auth::user()->isAbleTo('customfield create')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:20',
                    'type' => 'required',
                    'module' => 'required',
                    'is_required' => 'required',
                ]
            );

            $module = explode('-', $request->module);
            if (!empty($module) && isset($module[0])) {
                $m_name = $module[0];
            }
            if (!empty($module) && isset($module[1])) {
                $subm_name = $module[1];
            }

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('custom-field.index')->with('error', $messages->first());
            }

            $custom_field = new CustomField();
            $custom_field->name         = $request->name;
            $custom_field->type         = $request->type;
            $custom_field->module       = $m_name;
            $custom_field->is_required  = $request->is_required;
            $custom_field->sub_module   = $subm_name;
            $custom_field->created_by   = creatorId();
            $custom_field->workspace_id = getActiveWorkSpace();
            $custom_field->save();

             event(new CreateCustomField($request,$custom_field));


            return redirect()->route('custom-field.index')->with('success', __('Custom Field successfully created!'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
        return view('customfield::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(CustomField $customField)
    {
        if (Auth::user()->isAbleTo('customfield edit')) {
            $types = CustomField::$fieldTypes;
            $modules = Module::getByStatus(1);
            $module_custumefield = [];
            foreach ($modules as $m) {
                $module_custumefield[] = $m;
            }

            return view('customfield::edit', compact('customField', 'types', 'modules'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, CustomField $customField)
    {
        if (Auth::user()->isAbleTo('customfield edit')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:20',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('custom-field.index')->with('error', $messages->first());
            }

            $customField->name = $request->name;
            $customField->save();

            event(new UpdateCustomField($request,$customField));

            return redirect()->route('custom-field.index')->with('success', __('Custom Field successfully updated!'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(CustomField $customField)
    {
        if (Auth::user()->isAbleTo('customfield edit')) {
            $customField->delete();

            return redirect()->route('custom-field.index')->with('success', __('Custom Field successfully deleted!'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function get_module_list($module_name)
    {
        $m = Module::find($module_name);
        $path = $m->getPath() . '/module.json';
        $json = json_decode(file_get_contents($path), true);

        if (isset($json['module_list']) && !empty($json['module_list'])) {
            return $json['module_list'];
        } else {
            return [];
        }
    }
}
