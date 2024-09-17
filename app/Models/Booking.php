<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = ['day', 'period', 'booker_name','subject','table_id'];
    public function table(){
        return $this->belongsTo(Table::class);
    }
}
