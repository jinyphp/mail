<?php

namespace Jiny\Mail\Http\Controllers\Admin\Mail\Template;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Models\AuthMailTemplate;

/**
 * 메일 템플릿 저장 컨트롤러
 */
class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $adminUser = auth()->user();

        $template = AuthMailTemplate::create([
            'name' => $request->name,
            'type' => $request->type,
            'subject' => $request->subject,
            'message' => $request->message,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'admin_user_id' => $adminUser->id ?? null,
            'admin_user_name' => $adminUser->name ?? 'System',
        ]);

        return redirect()
            ->route('admin.auth.mail.templates.show', $template->id)
            ->with('success', '메일 템플릿이 성공적으로 등록되었습니다.');
    }
}