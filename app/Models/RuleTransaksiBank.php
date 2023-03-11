<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class RuleTransaksiBank extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'bank_code',
        'rule_transaksi',
    ];

    public $incrementing = false;
    
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }
}

