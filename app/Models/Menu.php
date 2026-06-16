<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
    ];

    // ── Scopes ─────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ── Relations ──────────────────────────────────────────
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Accessors ──────────────────────────────────────────
    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::url($this->image);
        }
        return asset('images/menu-placeholder.png');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'espresso'  => 'Espresso Based',
            'manual'    => 'Manual Brewed',
            'noncoffee' => 'Non Coffee',
            default     => $this->category,
        };
    }

    // ── Helpers ────────────────────────────────────────────
    public static function groupedByCategory(): array
    {
        return static::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category')
            ->toArray();
    }

    public static function totalSoldCount(int $menuId, ?string $period = 'today'): int
    {
        $query = OrderItem::where('menu_id', $menuId)
            ->whereHas('order', fn ($q) => $q->where('status', 'completed'));

        return match ($period) {
            'today'   => $query->whereDate('created_at', today())->sum('quantity'),
            'week'    => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('quantity'),
            'month'   => $query->whereMonth('created_at', now()->month)->sum('quantity'),
            default   => $query->sum('quantity'),
        };
    }
}
