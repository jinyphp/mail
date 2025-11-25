<?php

namespace Jiny\Mail\Http\Controllers\Admin\Template;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Models\AuthMailTemplate;

/**
 * 메일 템플릿 업데이트 컨트롤러
 */
class UpdateController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $template = AuthMailTemplate::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $template->update([
            'name' => $request->name,
            'type' => $request->type,
            'subject' => $request->subject,
            'message' => $request->message,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.mail.templates.show', $template->id)
            ->with('success', '메일 템플릿이 성공적으로 수정되었습니다.');
    }
}


