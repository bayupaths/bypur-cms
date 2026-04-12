<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionCondition extends Model
{
    protected $fillable = [
        'permission_id',
        'attribute',
        'operator',
        'value',
        'description',
    ];

    // Operator yang didukung
    const OPERATORS = ['=', '!=', '>', '>=', '<', '<=', 'in', 'not_in'];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    // -------------------------------------------------------------------------
    // ABAC evaluation
    // -------------------------------------------------------------------------

    /**
     * Evaluasi kondisi terhadap konteks yang diberikan.
     *
     * @param  array<string, mixed>  $context  e.g. ['user.department' => 'IT']
     */
    public function evaluate(array $context): bool
    {
        $actual = data_get($context, $this->attribute);

        if ($actual === null) {
            return false;
        }

        return match ($this->operator) {
            '='      => $actual == $this->value,
            '!='     => $actual != $this->value,
            '>'      => $actual > $this->value,
            '>='     => $actual >= $this->value,
            '<'      => $actual < $this->value,
            '<='     => $actual <= $this->value,
            'in'     => in_array($actual, explode(',', $this->value)),
            'not_in' => ! in_array($actual, explode(',', $this->value)),
            default  => false,
        };
    }
}
