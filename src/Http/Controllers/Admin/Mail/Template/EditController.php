<?php

namespace Jiny\Mail\Http\Controllers\Admin\Mail\Template;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Models\AuthMailTemplate;

/**
 * 메일 템플릿 수정 폼 컨트롤러
 */
class EditController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $template = AuthMailTemplate::findOrFail($id);

        return view('jiny-mail::admin.mail.template.edit', [
            'template' => $template,
            'typeOptions' => AuthMailTemplate::getTypeOptions(),
        ]);
    }
}