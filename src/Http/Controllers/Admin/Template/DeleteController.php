<?php

namespace Jiny\Mail\Http\Controllers\Admin\Template;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Models\AuthMailTemplate;

/**
 * 메일 템플릿 삭제 컨트롤러
 */
class DeleteController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $template = AuthMailTemplate::findOrFail($id);

        $templateName = $template->name;
        $template->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "'{$templateName}' 템플릿이 삭제되었습니다.",
            ]);
        }

        return redirect()
            ->route('admin.mail.templates.index')
            ->with('success', "'{$templateName}' 템플릿이 삭제되었습니다.");
    }
}


