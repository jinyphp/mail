<?php

namespace Jiny\Mail\Http\Controllers\Admin\Template;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Models\AuthMailTemplate;

/**
 * 메일 템플릿 생성 폼 컨트롤러
 */
class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jiny-mail::admin.mail.template.create', [
            'typeOptions' => AuthMailTemplate::getTypeOptions(),
        ]);
    }
}


