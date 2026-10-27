<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAboutTitleSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'story_title' => 'nullable|string|max:255',
            'story_description' => 'nullable|string|max:1000',
            'story_image' => 'nullable|string',
            'about_cta_title' => 'nullable|string|max:255',
            'about_cta_description' => 'nullable|string|max:1000',
            'mission_title' => 'nullable|string|max:255',
            'mission_description' => 'nullable|string|max:1000',
            'missions' => 'nullable|array',
            'missions.*.icon' => 'nullable|string',
            'missions.*.title' => 'nullable|string|max:255',
            'missions.*.description' => 'nullable|string|max:500',
            'team_title' => 'nullable|string|max:255',
            'team_description' => 'nullable|string|max:1000',
            'team_members' => 'nullable|array',
            'team_members.*.user_id' => 'nullable|exists:users,id',
            'team_members.*.experience' => 'nullable|string|max:255',
            'team_members.*.position' => 'nullable|string|max:255',
            'team_members.*.description' => 'nullable|string|max:500',
        ];
    }
}