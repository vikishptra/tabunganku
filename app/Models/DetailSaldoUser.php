<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailSaldoUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_user',
        'saldo'

    ];
}
