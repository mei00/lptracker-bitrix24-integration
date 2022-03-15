<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;

class LeadsQueue extends Model
{
    protected $table = 'leads_queue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lptracker_id', 'bitrix_id', 'name', 'phone', 'is_exported'
    ];

}
