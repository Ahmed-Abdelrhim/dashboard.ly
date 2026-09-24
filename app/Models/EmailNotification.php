<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    use HasFactory;

    /**
     * Table name.
     */
    protected $table = 'email_notifications';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'type',
        'email',
    ];
}
