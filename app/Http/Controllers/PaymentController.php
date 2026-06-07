<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuySubscriptionRequest;
use App\Http\Requests\BuyTokenRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $service) {}

    // ════════════════════════════════════════════════════════════════════════
    // MUA LƯỢT ỨNG TUYỂN (Token)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * GET /payment/token — Trang chọn gói mua lượt ứng tuyển.
     */
    public function tokenPurchasePage()
    {
        $packages = BuyTokenRequest::PACKAGES;
        return view('payment.token', compact('packages'));
    }

    /**
     * POST /payment/token — Tạo link thanh toán VNPay mua token.
     */
    public function initiateTokenPurchase(BuyTokenRequest $request)
    {
        $candidate = auth()->user();
        $package   = (int) $request->validated('package');

        try {
            $url = $this->service->createTokenPurchaseUrl(
                candidate: $candidate,
                package:   $package,
                ipAddr:    $request->ip(),
            );

            return redirect()->away($url);

        } catch (\Throwable $e) {
            Log::error("Token purchase initiate failed: " . $e->getMessage());
            return back()->with('error', 'Không thể tạo link thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * GET /payment/token/callback — Callback VNPay cho giao dịch mua token.
     * (Người dùng được redirect về sau khi thanh toán)
     */
    public function tokenCallback(Request $request)
    {
        $result = $this->service->handleTokenCallback($request->all());

        if ($result['success']) {
            return redirect()
                ->route('candidate.history')
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('payment.token')
            ->with('error', $result['message']);
    }

    /**
     * POST /payment/token/ipn — IPN VNPay (server-to-server).
     * Phải trả về response JSON theo chuẩn VNPay.
     */
    public function tokenIpn(Request $request)
    {
        $result = $this->service->handleTokenCallback($request->all());

        return response()->json([
            'RspCode' => $result['success'] ? '00' : '99',
            'Message' => $result['message'],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // MUA GÓI PREMIUM (Subscription)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * GET /payment/subscription — Trang chọn gói Premium.
     */
    public function subscriptionPage()
    {
        $employer = auth()->user();
        $plans    = BuySubscriptionRequest::PLANS;
        $status   = $this->service->getSubscriptionStatus($employer);

        return view('payment.subscription', compact('plans', 'status'));
    }

    /**
     * POST /payment/subscription — Tạo link thanh toán VNPay mua gói Premium.
     */
    public function initiateSubscription(BuySubscriptionRequest $request)
    {
        $employer = auth()->user();
        $plan     = $request->validated('plan');

        try {
            $url = $this->service->createSubscriptionUrl(
                employer: $employer,
                plan:     $plan,
                ipAddr:   $request->ip(),
            );

            return redirect()->away($url);

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error("Subscription initiate failed: " . $e->getMessage());
            return back()->with('error', 'Không thể tạo link thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * GET /payment/subscription/callback — Callback VNPay cho giao dịch subscription.
     */
    public function subscriptionCallback(Request $request)
    {
        $result = $this->service->handleSubscriptionCallback($request->all());

        if ($result['success']) {
            return redirect()
                ->route('employer.subscription.status')
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('payment.subscription')
            ->with('error', $result['message']);
    }

    /**
     * POST /payment/subscription/ipn — IPN VNPay (server-to-server).
     */
    public function subscriptionIpn(Request $request)
    {
        $result = $this->service->handleSubscriptionCallback($request->all());

        return response()->json([
            'RspCode' => $result['success'] ? '00' : '99',
            'Message' => $result['message'],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TRẠNG THÁI GÓI DỊCH VỤ
    // ════════════════════════════════════════════════════════════════════════

    /**
     * GET /employer/subscription — Trạng thái gói Premium hiện tại.
     */
    public function subscriptionStatus()
    {
        $employer = auth()->user();
        $status   = $this->service->getSubscriptionStatus($employer);

        return view('payment.subscription-status', compact('status'));
    }
}
