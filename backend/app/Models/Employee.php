<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'tbl_employee';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'company_id',
        'emp_id',
        'emp_name',
        'designation',
        'emp_desig',
        'Gender',
        'Date_of_join',
        'evaluation',
        'remarks',
        'present_grade',
        'present_salary',
        'card_id',
        'pin',
        'status',
        'emp_type',
        'created_by',
        'entry_by',
    ];

    protected $casts = [
        'Date_of_join' => 'date',
        'evaluation' => 'date',
        'status' => 'integer',
        'present_salary' => 'decimal:2',
    ];

    /**
     * Relationship to attendance logs.
     * Matches on either id or emp_id string.
     */
    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'emp_id', 'id');
    }
}
