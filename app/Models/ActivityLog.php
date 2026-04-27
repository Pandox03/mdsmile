<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';

    public const SUBJECT_FACTURE = 'facture';
    public const SUBJECT_TRAVAIL = 'travail';
    public const SUBJECT_STOCK = 'stock';
    public const SUBJECT_FOURNISSEUR = 'fournisseur';
    public const SUBJECT_CAISSE = 'caisse';
    public const SUBJECT_CLIENT = 'client';
    public const SUBJECT_USER = 'user';

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
    ];

    protected $casts = [
        'subject_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity. Call from controllers after create/update/delete.
     */
    public static function log(string $action, string $subjectType, ?int $subjectId, ?string $description = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
        ]);
    }

    public static function subjectTypeLabels(): array
    {
        return [
            self::SUBJECT_FACTURE => 'Facture',
            self::SUBJECT_TRAVAIL => 'Travail',
            self::SUBJECT_STOCK => 'Stock',
            self::SUBJECT_FOURNISSEUR => 'Fournisseur',
            self::SUBJECT_CAISSE => 'Caisse',
            self::SUBJECT_CLIENT => 'Client',
            self::SUBJECT_USER => 'Utilisateur',
        ];
    }

    public static function actionLabels(): array
    {
        return [
            self::ACTION_CREATED => 'Création',
            self::ACTION_UPDATED => 'Modification',
            self::ACTION_DELETED => 'Suppression',
        ];
    }

    public function getSubjectTypeLabelAttribute(): string
    {
        return self::subjectTypeLabels()[$this->subject_type] ?? $this->subject_type;
    }

    public function getActionLabelAttribute(): string
    {
        return self::actionLabels()[$this->action] ?? $this->action;
    }
}
