<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public const TYPES = ['new_offer', 'bonus', 'info', 'payments', 'other'];

    protected $fillable = [
        'author_id', 'title', 'type', 'body', 'attachment_disk', 'attachment_path',
        'attachment_name', 'attachment_mime', 'attachment_size', 'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'attachment_size' => 'integer',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'idrep');
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment_disk) && filled($this->attachment_path);
    }

    public function typeLabel(): string
    {
        return [
            'new_offer' => 'New Offer', 'bonus' => 'Bonus', 'info' => 'Info',
            'payments' => 'Payments', 'other' => 'Other',
        ][$this->type] ?? 'Info';
    }
}
