<?php

namespace App\Service;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

class QRCodeService
{
    public function generateTicketQRCode(string $data): string
    {
        $result = Builder::create()
            ->writer(new SvgWriter())   // <‑‑ plus de PNG, plus de GD
            ->data($data)
            ->size(300)
            ->margin(10)
            ->build();

        return 'data:image/svg+xml;base64,' . base64_encode($result->getString());
    }
}
