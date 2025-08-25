<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Payment;
use App\Services\BakongService;

class PaymentController extends Controller
{
    protected $bakong;

    public function __construct(BakongService $bakong)
    {
        $this->bakong = $bakong;
    }

    // Create Payment (Generate QR)
    public function create(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'amount'   => 'required|numeric|min:0.01'
        ]);

        $accountId    = env('BAKONG_ACCOUNT_ID', 'merchant@bakong');
        $merchantName = env('BAKONG_MERCHANT_NAME', 'Demo Shop');
        $amount       = $request->amount;

        // Generate QR using the dummy KHQR class
        $qr = $this->bakong->generateQR($accountId, $merchantName, $amount);

        $md5 = Str::uuid()->toString();

        $payment = Payment::create([
            'order_id'  => $request->order_id,
            'amount'    => $amount,
            'qr_string' => $qr['qr_code'],  // <-- updated key
            'md5_hash'  => $md5,
            'status'    => 'PENDING',
        ]);

        return response()->json([
            'success'   => true,
            'md5_hash'  => $payment->md5_hash,
            'qr_string' => $payment->qr_string,
            'amount'    => $payment->amount,
            'status'    => $payment->status,
        ]);
    }

    // Check Payment Status
    public function checkStatus($md5)
    {
        $payment = Payment::where('md5_hash', $md5)->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        return response()->json([
            'success' => true,
            'status'  => $payment->status
        ]);
    }
}
