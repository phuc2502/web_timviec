<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Transaction extends Model
{
    /**
     * AdminController dùng Transaction model để hiển thị các giao dịch thanh toán.
     * Ở đây ta map trực tiếp tới bảng 'payments' của hệ thống.
     */
    protected $table = 'payments';

    protected $guarded = [];

    protected $casts = [
        'vnpay_response' => 'array',
        'amount'         => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor vnp_txn_ref -> trỏ tới cột vnpay_txn_ref của payments table
     */
    public function getVnpTxnRefAttribute()
    {
        return $this->vnpay_txn_ref;
    }

    /**
     * Accessor vnp_transaction_no -> trích xuất từ vnpay_response của payments table
     */
    public function getVnpTransactionNoAttribute()
    {
        if (is_array($this->vnpay_response) && isset($this->vnpay_response['vnp_TransactionNo'])) {
            return $this->vnpay_response['vnp_TransactionNo'];
        }
        return null;
    }

    /**
     * Accessor status -> đồng bộ 'success' của payments thành 'paid' cho View hiển thị
     */
    public function getStatusAttribute($value)
    {
        if ($value === 'success') {
            return 'paid';
        }
        return $value;
    }

    /**
     * Accessor paid_at -> ngày thanh toán thành công
     */
    public function getPaidAtAttribute()
    {
        if ($this->status === 'paid' || $this->status === 'success') {
            if (is_array($this->vnpay_response) && isset($this->vnpay_response['vnp_PayDate'])) {
                try {
                    return Carbon::createFromFormat('YmdHis', $this->vnpay_response['vnp_PayDate']);
                } catch (\Exception $e) {
                    // ignore and fallback
                }
            }
            return $this->updated_at;
        }
        return null;
    }
}
