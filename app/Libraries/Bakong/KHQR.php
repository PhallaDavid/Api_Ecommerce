<?php
namespace App\Libraries\Bakong;

class KHQR
{
    public function generateQR($accountId, $merchantName, $amount)
    {
        return [
            'qr_code' => 'sample-qrcode-data',
            'merchant' => $merchantName,
            'account' => $accountId,
            'amount' => $amount,
        ];
    }
}
