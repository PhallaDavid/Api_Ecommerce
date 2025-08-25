<?php

namespace App\Services;

use App\Libraries\Bakong\KHQR;

class BakongService
{
    protected $khqr;

    public function __construct()
    {
        $this->khqr = new KHQR();
    }

    public function generateQR($accountId, $merchantName, $amount)
    {
        return $this->khqr->generateQR($accountId, $merchantName, $amount);
    }
}
