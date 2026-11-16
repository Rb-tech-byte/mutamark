<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DigitalSignatureController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();
        if (
            !$user ||
            (!$user->can('manage-company-settings') &&
                !$user->can('manage-settings') &&
                $user->type !== 'company' &&
                $user->type !== 'superadmin')
        ) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'digital_signature_enabled' => 'required|in:on,off',
            'digital_signature_auto_sign' => 'required|in:on,off',
            'digital_signature_name' => 'nullable|string|max:120',
            'digital_signature_title' => 'nullable|string|max:120',
            'digital_signature_stamp_text' => 'nullable|string|max:160',
            'signature_file' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'stamp_file' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'signature_data_url' => 'nullable|string',
        ]);

        $companyId = creatorId();
        $disk = Storage::disk('public');
        $disk->makeDirectory('signatures');
        $disk->makeDirectory('stamps');

        $currentSettings = getCompanyAllSetting($companyId);
        $currentPath = $currentSettings['digital_signature_path'] ?? '';
        $currentStampPath = $currentSettings['digital_stamp_path'] ?? '';

        if ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
            $filename = "company_{$companyId}_sig_" . time() . ".{$ext}";
            $newPath = $file->storeAs('signatures', $filename, 'public');

            if ($currentPath && $disk->exists($currentPath)) {
                $disk->delete($currentPath);
            }
            setSetting('digital_signature_path', $newPath, $companyId, true);
        }

        if ($request->hasFile('stamp_file')) {
            $stampFile = $request->file('stamp_file');
            $stampExt = strtolower($stampFile->getClientOriginalExtension() ?: 'png');
            $stampFilename = "company_{$companyId}_stamp_" . time() . ".{$stampExt}";
            $newStampPath = $stampFile->storeAs('stamps', $stampFilename, 'public');

            if ($currentStampPath && $disk->exists($currentStampPath)) {
                $disk->delete($currentStampPath);
            }
            setSetting('digital_stamp_path', $newStampPath, $companyId, true);
        } elseif (!empty($validated['signature_data_url']) && str_starts_with($validated['signature_data_url'], 'data:image/png;base64,')) {
            $base64 = substr($validated['signature_data_url'], strlen('data:image/png;base64,'));
            $binary = base64_decode($base64, true);
            if ($binary === false || strlen($binary) < 50) {
                return back()->with('error', __('Invalid drawn signature image.'));
            }
            $filename = "company_{$companyId}_sig_" . time() . '.png';
            $newPath = "signatures/{$filename}";
            $disk->put($newPath, $binary);

            if ($currentPath && $disk->exists($currentPath)) {
                $disk->delete($currentPath);
            }
            setSetting('digital_signature_path', $newPath, $companyId, true);
        }

        if ($request->hasFile('stamp_file')) {
            $stampFile = $request->file('stamp_file');
            $stampExt = strtolower($stampFile->getClientOriginalExtension() ?: 'png');
            $stampFilename = "company_{$companyId}_stamp_" . time() . ".{$stampExt}";
            $newStampPath = $stampFile->storeAs('stamps', $stampFilename, 'public');

            if ($currentStampPath && $disk->exists($currentStampPath)) {
                $disk->delete($currentStampPath);
            }
            setSetting('digital_stamp_path', $newStampPath, $companyId, true);
        }

        setSetting('digital_signature_enabled', $validated['digital_signature_enabled'], $companyId, true);
        setSetting('digital_signature_auto_sign', $validated['digital_signature_auto_sign'], $companyId, true);
        setSetting('digital_signature_name', $validated['digital_signature_name'] ?? '', $companyId, true);
        setSetting('digital_signature_title', $validated['digital_signature_title'] ?? '', $companyId, true);
        setSetting('digital_signature_stamp_text', $validated['digital_signature_stamp_text'] ?? '', $companyId, true);

        return back()->with('success', __('Digital signature settings saved successfully.'));
    }

    public function destroy()
    {
        $user = Auth::user();
        if (
            !$user ||
            (!$user->can('manage-company-settings') &&
                !$user->can('manage-settings') &&
                $user->type !== 'company' &&
                $user->type !== 'superadmin')
        ) {
            return back()->with('error', __('Permission denied.'));
        }

        $companyId = creatorId();
        $disk = Storage::disk('public');
        $currentPath = getCompanyAllSetting($companyId)['digital_signature_path'] ?? '';

        if ($currentPath && $disk->exists($currentPath)) {
            $disk->delete($currentPath);
        }

        setSetting('digital_signature_path', '', $companyId, true);

        return back()->with('success', __('Stored digital signature removed.'));
    }
}

