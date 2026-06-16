<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_id',
        'menu_name',
        'menu_price',
        'quantity',
        'sugar_level',
        'ice_level',
        'serve_type',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'menu_price' => 'integer',
        'quantity'   => 'integer',
        'subtotal'   => 'integer',
    ];

    // ── Relations ──────────────────────────────────────────
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    // ── Accessors ──────────────────────────────────────────
    public function getSugarLabelAttribute(): string
    {
        return match ($this->sugar_level) {
            'less'   => 'Less Sweet',
            'extra'  => 'Extra Sweet',
            default  => 'Normal',
        };
    }

    public function getIceLabelAttribute(): string
    {
        return match ($this->ice_level) {
            'no_ice' => 'No Ice',
            'less'   => 'Less Ice',
            default  => 'Normal Ice',
        };
    }

    /**
     * Label sajian: 'Hot' untuk cappuccino hot, atau label ice level.
     */
    public function getServeLabelAttribute(): string
    {
        if ($this->serve_type === 'hot') return 'Hot';
        return $this->ice_label;
    }

    /**
     * Apakah item ini memiliki opsi gula (sugar level).
     * Kategori maincourse dan snack tidak memiliki sugar level.
     */
    public function getHasSugarAttribute(): bool
    {
        // Cek kategori menu terlebih dahulu
        if ($this->menu && in_array($this->menu->category, ['maincourse', 'snack'])) {
            return false;
        }
        $noSugar = [
            'espresso', 'americano', 'capuccino', 'french press', 'v60',
            'vietnam drip', 'tubruk', 'japanese', 'chocolate', 'red velvet',
            'taro', 'matcha', 'lemon tea', 'orange yakults', 'mango yakults', 'blue sparkling',
        ];
        return !in_array(strtolower($this->menu_name), $noSugar);
    }

    /**
     * Apakah item ini memiliki opsi sajian (ice level / hot-ice).
     * Kategori maincourse dan snack tidak memiliki ice level.
     */
    public function getHasServeAttribute(): bool
    {
        // Cek kategori menu terlebih dahulu
        if ($this->menu && in_array($this->menu->category, ['maincourse', 'snack'])) {
            return false;
        }
        $noIce = ['espresso', 'french press', 'v60', 'vietnam drip', 'tubruk', 'japanese'];
        return !in_array(strtolower($this->menu_name), $noIce);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    // ── Helpers ────────────────────────────────────────────
    public static function topSelling(int $limit = 5, string $period = 'today'): array
    {
        $query = static::selectRaw('menu_id, menu_name, SUM(quantity) as total_sold')
            ->whereHas('order', fn ($q) => $q->where('status', 'completed'));

        match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'week'  => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month),
            default => null,
        };

        return $query
            ->groupBy('menu_id', 'menu_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
