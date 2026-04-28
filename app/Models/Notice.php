<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'created_by','branch_id','title','body','type','audience',
        'department_id','published_at','expires_at','is_published'
    ];
    protected $casts = ['published_at'=>'datetime','expires_at'=>'datetime','is_published'=>'boolean'];
    public function creator()    { return $this->belongsTo(User::class,'created_by'); }
    public function branch()     { return $this->belongsTo(Branch::class); }
    public function department() { return $this->belongsTo(Department::class); }
}
