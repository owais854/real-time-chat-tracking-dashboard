<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message',
        'from_admin',
        'files',
        'visitor_ip',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'from_admin' => 'boolean',
        'files' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}
