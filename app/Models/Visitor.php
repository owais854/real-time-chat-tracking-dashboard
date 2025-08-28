<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id','ip_address','country','city','region','user_agent','device_type',
        'browser','os','referrer','current_url','last_activity','is_active'
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
