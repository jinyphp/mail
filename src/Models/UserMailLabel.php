<?php

namespace Jiny\Mail\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMailLabel extends Model
{
    use HasFactory;

    protected $table = 'user_mail_label';

    protected $fillable = [
        'user_id',
        'label_name',
        'label_color',
        'label_icon',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function mails()
    {
        return $this->hasMany(UserMail::class, 'label_id');
    }
}