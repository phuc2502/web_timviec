<?php

namespace App\Services;

use App\Events\PaymentSucceeded;
use App\Http\Requests\BuySubscriptionRequest;
use App\Http\Requests\BuyTokenRequest;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(private readonly VNPayService $vnpay) {}

    // ════════════════════════════════════════════════════════════════════════
    // MUA LƯỢT ỨNG TUYỂN (Token) — Dành cho Candidate
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Tạo link thanh toán VNPay để mua lượt ứng tuyển.
     *
     * @return string  URL redirect sang VNPay
     */
    public function createTokenPurchaseUrl(User $candidate, int $package, string $ipAddr): string
    {
        if (!array_key_exists($package, BuyTokenRequest::PACKAGES)) {
            throw new \InvalidArgumentException('Gói lượt ứng tuyển không hợp lệ.');
        }

        $amount   = BuyTokenRequest::PACKAGES[$package];
        $txnRef   = $this->vnpay->generateTxnRef();

        // Tạo bản ghi payment pending
        Payment::create([
            'user_id'       => $candidate->id,
            'type'          => 'token',
            'amount'        => $amount,
            'status'        => 'pending',
            'vnpay_txn_ref' => $txnRef,
            'token_amount'  => $package,
        ]);

        return $this->vnpay->createPaymentUrl(
            txnRef:    $txnRef,
            amount:    $amount,
            orderInfo: "Mua {$package} luot ung tuyen",
            returnUrl: route('payment.token.callback'),
            ipAddr:    $ipAddr,
        );
    }

    /**
     * Xử lý IPN/Callback VNPay cho giao dịch mua token.
     *
     * @param  array $callbackData  Toàn bộ query params từ VNPay callback
     * @return array  ['success' => bool, 'message' => string]
     */
    public function handleTokenCallback(array $callbackData): array
    {
        // ── 1. Xác thực chữ ký ────────────────────────────────────────────
        if (!$this->vnpay->verifySignature($callbackData)) {
            Log::warning('VNPay Token callback: Chữ ký không hợp lệ', $callbackData);
            return ['success' => false, 'message' => 'Chữ ký không hợp lệ.'];
        }

        $txnRef  = $callbackData['vnp_TxnRef'] ?? '';
        $payment = Payment::where('vnpay_txn_ref', $txnRef)
                           ->where('type', 'token')
                           ->first();

        if (!$payment) {
            Log::error("VNPay Token callback: Không tìm thấy payment với txnRef={$txnRef}");
            return ['success' => false, 'message' => 'Không tìm thấy giao dịch.'];
        }

        // Idempotency: bỏ qua nếu đã xử lý
        if ($payment->status !== 'pending') {
            return ['success' => true, 'message' => 'Giao dịch đã được xử lý.'];
        }

        // ── 2. Kiểm tra kết quả giao dịch ────────────────────────────────
        if (!$this->vnpay->isSuccess($callbackData)) {
            $payment->update([
                'status'          => 'failed',
                'vnpay_response'  => $callbackData,
            ]);
            return ['success' => false, 'message' => 'Thanh toán thất bại hoặc bị huỷ.'];
        }

        // ── 3. Cộng lượt ứng tuyển (Transaction) ─────────────────────────
        DB::transaction(function () use ($payment, $callbackData) {
            $payment->update([
                'status'         => 'success',
                'vnpay_response' => $callbackData,
            ]);

            UserToken::updateOrCreate(
                ['user_id' => $payment->user_id],
                ['balance' => DB::raw("balance + {$payment->token_amount}")]
            );
        });

        PaymentSucceeded::dispatch($payment->fresh());

        Log::info("Token purchase success: user={$payment->user_id}, tokens={$payment->token_amount}");

        return ['success' => true, 'message' => "Thanh toán thành công. Đã cộng {$payment->token_amount} lượt ứng tuyển."];
    }

    // ════════════════════════════════════════════════════════════════════════
    // MUA GÓI PREMIUM (Subscription) — Dành cho Employer
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Tạo link thanh toán VNPay để mua gói Premium.
     *
     * @throws \RuntimeException  Nếu employer đang có gói active
     */
    public function createSubscriptionUrl(User $employer, string $plan, string $ipAddr): string
    {
        if (!array_key_exists($plan, BuySubscriptionRequest::PLANS)) {
            throw new \InvalidArgumentException('Gói dịch vụ không hợp lệ.');
        }

        // Chặn mua trùng khi đang có gói active
        $activeSub = Subscription::where('user_id', $employer->id)
                                  ->where('status', 'active')
                                  ->where('billing_ends', '>', now())
                                  ->first();

        if ($activeSub) {
            throw new \RuntimeException(
                "Bạn đang sử dụng gói {$activeSub->plan} (còn {$activeSub->daysRemaining()} ngày). Vui lòng chờ hết hạn."
            );
        }

        $amount = BuySubscriptionRequest::PLANS[$plan];
        $txnRef = $this->vnpay->generateTxnRef();

        Payment::create([
            'user_id'       => $employer->id,
            'type'          => 'subscription',
            'amount'        => $amount,
            'status'        => 'pending',
            'vnpay_txn_ref' => $txnRef,
            'plan'          => $plan,
        ]);

        return $this->vnpay->createPaymentUrl(
            txnRef:    $txnRef,
            amount:    $amount,
            orderInfo: "Mua goi Premium {$plan}",
            returnUrl: route('payment.subscription.callback'),
            ipAddr:    $ipAddr,
        );
    }

    /**
     * Xử lý callback VNPay cho giao dịch mua subscription.
     */
    public function handleSubscriptionCallback(array $callbackData): array
    {
        if (!$this->vnpay->verifySignature($callbackData)) {
            Log::warning('VNPay Subscription callback: Chữ ký không hợp lệ', $callbackData);
            return ['success' => false, 'message' => 'Chữ ký không hợp lệ.'];
        }

        $txnRef  = $callbackData['vnp_TxnRef'] ?? '';
        $payment = Payment::where('vnpay_txn_ref', $txnRef)
                           ->where('type', 'subscription')
                           ->first();

        if (!$payment) {
            Log::error("VNPay Subscription callback: Không tìm thấy payment txnRef={$txnRef}");
            return ['success' => false, 'message' => 'Không tìm thấy giao dịch.'];
        }

        if ($payment->status !== 'pending') {
            return ['success' => true, 'message' => 'Giao dịch đã được xử lý.'];
        }

        if (!$this->vnpay->isSuccess($callbackData)) {
            $payment->update([
                'status'         => 'failed',
                'vnpay_response' => $callbackData,
            ]);
            return ['success' => false, 'message' => 'Thanh toán thất bại hoặc bị huỷ.'];
        }

        DB::transaction(function () use ($payment, $callbackData) {
            $payment->update([
                'status'         => 'success',
                'vnpay_response' => $callbackData,
            ]);

            $billingEnds = match ($payment->plan) {
                'yearly'  => now()->addYear(),
                default   => now()->addMonth(),
            };

            Subscription::create([
                'user_id'     => $payment->user_id,
                'plan'        => $payment->plan,
                'status'      => 'active',
                'billing_ends' => $billingEnds,
            ]);
        });

        PaymentSucceeded::dispatch($payment->fresh());

        Log::info("Subscription success: user={$payment->user_id}, plan={$payment->plan}");

        return ['success' => true, 'message' => "Đăng ký gói {$payment->plan} thành công."];
    }

    // ════════════════════════════════════════════════════════════════════════
    // TRẠNG THÁI GÓI DỊCH VỤ
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Trả về thông tin gói subscription hiện tại của employer.
     *
     * @return array{
     *   has_active: bool,
     *   plan: string|null,
     *   status: string|null,
     *   billing_ends: string|null,
     *   days_remaining: int,
     *   suggest_renew: bool,
     * }
     */
    public function getSubscriptionStatus(User $employer): array
    {
        $sub = Subscription::where('user_id', $employer->id)
                            ->latest()
                            ->first();

        if (!$sub) {
            return [
                'has_active'    => false,
                'plan'          => null,
                'status'        => null,
                'billing_ends'  => null,
                'days_remaining' => 0,
                'suggest_renew' => true,
            ];
        }

        // Tự động cập nhật status nếu hết hạn
        if ($sub->status === 'active' && $sub->billing_ends?->isPast()) {
            $sub->update(['status' => 'expired']);
        }

        return [
            'has_active'    => $sub->isActive(),
            'plan'          => $sub->plan,
            'status'        => $sub->status,
            'billing_ends'  => $sub->billing_ends?->toDateString(),
            'days_remaining' => $sub->daysRemaining(),
            'suggest_renew' => !$sub->isActive(),
        ];
    }
}
