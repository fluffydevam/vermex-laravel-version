<?php

namespace App\Models;

use App\Enums\PaymentType;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'contract_id',
        'user_id',
        'amount_paid',
        'payment_type',
        'payment_method',
        'reference_number',
        'remarks',
        'payment_date',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_type' => PaymentType::class,
        'payment_method' => PaymentMethod::class,
        'payment_date' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->hasOneThrough(Client::class, Contract::class);
    }
}
