<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'order_type',
        'table_number',
        'total_price',
        'payment_method',
        'status',
        'customer_note',
        'processed_at',
        'completed_at',
        'snap_token',
        'snap_url',
        'midtrans_transaction_id',
    ];

    protected $casts = [
        'total_price'   => 'integer',
        'table_number'  => 'integer',
        'processed_at'  => 'datetime',
        'completed_at'  => 'datetime',
    ];

    // ── Boot ───────────────────────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= static::generateOrderNumber();
        });
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopePending($query)      { return $query->where('status', 'pending'); }
    public function scopeProcessing($query)   { return $query->where('status', 'processing'); }
    public function scopeCompleted($query)    { return $query->where('status', 'completed'); }
    public function scopeActiveOrders($query) 
    { 
        return $query->whereIn('status', ['pending', 'processing']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ── Relations ──────────────────────────────────────────
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Accessors ──────────────────────────────────────────
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Menunggu',
            'processing' => 'Diproses',
            'completed'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'yellow',
            'processing' => 'blue',
            'completed'  => 'green',
            'cancelled'  => 'red',
            default      => 'gray',
        };
    }

    // ── Helpers ────────────────────────────────────────────
    public static function generateOrderNumber(): string
    {
        $prefix = 'ZC-' . now()->format('Ymd');
        $last   = static::where('order_number', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->value('order_number');

        $seq = $last ? (int) substr($last, -3) + 1 : 1;

        return $prefix . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status'       => 'processing',
            'processed_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
    }

    // ── Statistics ─────────────────────────────────────────
    public static function todayRevenue(): int
    {
        return (int) static::completed()->today()->sum('total_price');
    }

    public static function todayCount(): int
    {
        return static::today()->count();
    }

    public static function weeklyRevenue(): array
    {
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $result[] = [
                'date'    => $date->format('d/m'),
                'day'     => $date->isoFormat('ddd'),
                'revenue' => (int) static::completed()
                    ->whereDate('created_at', $date)
                    ->sum('total_price'),
                'count'   => static::whereDate('created_at', $date)->count(),
            ];
        }
        return $result;
    }
}
