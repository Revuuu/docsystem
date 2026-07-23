<?php

namespace App\Http\Controllers;

use App\Services\PurchaseOrders\PurchaseOrderDocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderDocumentService $purchaseOrders
    ) {
    }

    /**
     * Display a printable Purchase Order grouped by PONo.
     */
    public function show(int $poNo): View
    {
        try {
            $viewData = $this->purchaseOrders->build($poNo);
        } catch (ValidationException) {
            abort(404, 'Purchase order not found.');
        }

        return view('purchase-orders.index', [
            ...$viewData,
            'signatureSlots' => [],
            'serverPdf' => false,
        ]);
    }
}