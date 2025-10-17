<?php

namespace Jiny\Mail\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMail extends Model
{
    use HasFactory;

    protected $table = 'user_mail';

    protected $fillable = [
        'user_id',
        'from_email',
        'to_email',
        'subject',
        'body',
        'label_id',
        'is_read',
        'is_starred',
        'is_important',
        'sent_at',
        'attachments',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_important' => 'boolean',
        'sent_at' => 'datetime',
        'attachments' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function label()
    {
        return $this->belongsTo(UserMailLabel::class, 'label_id');
    }
}