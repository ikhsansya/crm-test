<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\FilterSortTrait;

class Company extends Model
{
    use SoftDeletes, HasRoles, FilterSortTrait;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
    ];

    public function employees() {
        $this->hasMany(User::class, 'company_id');
    }
}
