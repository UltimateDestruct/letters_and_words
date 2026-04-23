<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    public $timestamps = false;

    protected $table = 'errors';

    /** @var list<string> */
    protected $fillable = ['http_code', 'content', 'timestamp'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
        ];
    }
}
