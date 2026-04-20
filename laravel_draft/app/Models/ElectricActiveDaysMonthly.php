<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectricActiveDaysMonthly extends Model
{
    protected $table = 'electric_active_days_monthly';

    protected $fillable = [
        'billing_month_date',
        'company_id',
        'active_days',
        'remarks',
        'source_file',
        'uploaded_by',
    ];

    protected $casts = [
        'billing_month_date' => 'date:Y-m-d',
        'active_days' => 'decimal:4',
    ];
}
