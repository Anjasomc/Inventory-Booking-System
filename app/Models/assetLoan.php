<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class assetLoan extends Model
{
    use HasFactory;

    protected $table = 'asset_loan';
    public $timestamps = false;
}
