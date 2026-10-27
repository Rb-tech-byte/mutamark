<?php

namespace Modules\Appointment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Appointment\Entities\Question;
use Modules\Appointment\Events\CreateQuestion;
use Modules\Appointment\Events\DestroyQuestion;
use Modules\Appointment\Events\UpdateQuestion;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('question manage')) {
            $question = Question::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())->get();
            return view('appointment::question.index', compact('question'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleto('question create')) {
            $question = Question::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())->get();
            $question_type = Question::$question_type;
            return view('appointment::question.create', compact('question', 'question_type'));
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
        if (Auth::user()->isAbleto('question create')) {

            $validation = [
                'question' => ['required', 'string', 'max:255'],
                'question_type' => ['required'],
                // 'available_answer' => ['required'],
                'is_required' => ['required'],
                'is_enabled' => ['required'],
            ];
            $validator = \Validator::make(
                $request->all(),
                $validation
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->withInput()->with('error', $messages->first());
            }
            $post = [
                'question' => $request->question,
                'question_type' => $request->question_type,
                'available_answer' => !empty($request->available_answer) ? implode(', ', $request->available_answer) : '',
                'is_required' => $request->is_required,
                'is_enabled' => $request->is_enabled,
                'workspace' => getActiveWorkSpace(),
                'created_by' => creatorId(),
            ];

            Question::create($post);

            event(new CreateQuestion($request, $post));

            return redirect()->route('questions.index')->with('success',  __('Question created successfully'));
        } else {
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
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (Auth::user()->isAbleto('question edit')) {
            $question = Question::find($id);
            if ($question->created_by == creatorId() && $question->workspace == getActiveWorkSpace()) {
                $questions = explode(',', $question->available_answer);
                $question_type = Question::$question_type;
                return view('appointment::question.edit', compact('question', 'questions', 'question_type'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
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
    public function update(Request $request, Question $question)
    {
        if (Auth::user()->isAbleTo('question edit')) {
            if ($question->created_by == creatorId() && $question->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'question' => 'required',
                        'question_type' => 'required',
                        // 'available_answer' => 'required',
                        'is_required' => 'required',
                        'is_enabled' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('question.index')->with('error', $messages->first());
                }

                $question->question = $request->question;
                $question->question_type = $request->question_type;
                $question->available_answer = !empty($request->available_answer) ? implode(', ', $request->available_answer) : '';
                $question->is_required = $request->is_required;
                $question->is_enabled = $request->is_enabled;
                $question->save();

                event(new UpdateQuestion($request, $question));

                return redirect()->route('questions.index')->with('success',  __('Question Updated successfully'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('question delete')) {
            $question = Question::find($id);
            if ($question->created_by == creatorId() && $question->workspace == getActiveWorkSpace()) {

                event(new DestroyQuestion($question));

                $question->delete();
                return redirect()->route('questions.index')->with('success', __('Question deleted successfully'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
