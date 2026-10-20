<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $table = 'tbl_attendance_log';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'company_id',
        'Date',
        'rfid',
        'emp_id',
        'date_time',
        'terminal_id',
        'in_out',
        'attendance_type',
        'status',
    ];

    protected $casts = [
        'Date' => 'date:Y-m-d',
        'date_time' => 'datetime:Y-m-d H:i:s',
        'status' => 'integer',
    ];

    /**
     * Relationship to employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'id');
    }
}
