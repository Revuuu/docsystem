<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order {{ $purchaseOrder->PONo }}</title>

    <style>
        @page {
            size: Letter portrait;
            margin: 0;
        }

        :root {
            --page-width: 215.9mm;
            --page-height: 279.4mm;
            --ink: #111;
            --line: #222;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            background: #e8e8e8;
        }

        body {
            font-size: 8pt;
        }

        .print-actions {
            position: fixed;
            top: 16px;
            right: 18px;
            z-index: 1000;
        }

        .print-actions button {
            border: 0;
            border-radius: 6px;
            padding: 10px 16px;
            color: #fff;
            background: #166534;
            font: 700 14px Arial, Helvetica, sans-serif;
            cursor: pointer;
            box-shadow: 0 2px 8px rgb(0 0 0 / 18%);
        }

        .po-page {
            position: relative;
            display: block;
            width: var(--page-width);
            height: var(--page-height);
            margin: 8mm auto;
            padding: 5.5mm 7.5mm 4.5mm;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 12px rgb(0 0 0 / 20%);
            break-after: page;
            page-break-after: always;
        }

        .po-page:last-of-type {
            break-after: auto;
            page-break-after: auto;
        }

        .po-header {
            display: block;
        }

        .brand {
            height: 25mm;
            text-align: center;
        }

        .brand img {
            display: inline-block;
            width: 112mm;
            max-height: 22mm;
            object-fit: contain;
        }

        .brand-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 112mm;
            height: 17mm;
            color: #fff;
            background: #087a3b;
            font-size: 13pt;
            font-weight: 700;
        }

        .document-title {
            margin-top: -1.5mm;
            text-align: center;
            letter-spacing: 3.2px;
            font-size: 12pt;
            font-weight: 700;
            line-height: 1;
        }

        .form-code {
            margin-top: 1mm;
            text-align: center;
            font-size: 6.8pt;
            line-height: 1;
        }

        .header-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 48mm;
            column-gap: 8mm;
            margin-top: 2.5mm;
        }

        .field-row {
            display: grid;
            grid-template-columns: max-content minmax(0, 1fr);
            align-items: end;
            min-height: 5.2mm;
            column-gap: 2mm;
        }

        .field-row .label {
            white-space: nowrap;
            font-size: 7.2pt;
            line-height: 1;
        }

        .field-row .value {
            min-height: 4mm;
            padding: 0 1.5mm .55mm;
            border-bottom: .7px solid var(--line);
            font-size: 7.2pt;
            font-weight: 700;
            line-height: 1.15;
            overflow-wrap: anywhere;
        }

        .field-row .value.normal {
            font-weight: 400;
        }

        .po-meta .field-row {
            grid-template-columns: 12mm minmax(0, 1fr);
        }

        .po-meta .value {
            text-align: center;
        }

        .supplier-bottom {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 59mm;
            column-gap: 7mm;
        }

        .terms-row {
            grid-template-columns: 14mm minmax(0, 1fr);
        }

        .table-rule {
            height: 0;
            margin-top: 2.2mm;
            border-top: .8px solid var(--line);
        }

        .items-region {
            display: block;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7pt;
        }

        .item-table col.qty { width: 8%; }
        .item-table col.unit { width: 8%; }
        .item-table col.code { width: 13%; }
        .item-table col.description { width: 30%; }
        .item-table col.unit-cost { width: 15%; }
        .item-table col.amount { width: 26%; }

        .item-table th {
            height: 5mm;
            padding: .8mm .8mm .65mm;
            border-bottom: .8px solid var(--line);
            border-right: .55px solid var(--line);
            font-size: 7.4pt;
            font-weight: 400;
            line-height: 1;
            text-align: center;
        }

        .item-table th:first-child {
            border-left: .55px solid var(--line);
        }

        .item-table td {
            min-height: 4.1mm;
            padding: .65mm .9mm;
            border-right: .45px solid #4a4a4a;
            font-size: 6.8pt;
            line-height: 1.18;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        .item-table td:first-child {
            border-left: .45px solid #4a4a4a;
        }

        .item-table tbody tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .item-table .center {
            text-align: center;
        }

        .item-table .number {
            text-align: right;
            white-space: nowrap;
        }

        .item-table .description-cell {
            padding-left: 1.2mm;
        }

        .items-bottom-line {
            border-bottom: .75px solid var(--line);
        }

        .final-summary {
            display: grid;
            grid-template-columns: 60% 40%;
            min-height: 22mm;
            border-bottom: .75px solid var(--line);
        }

        .final-summary-left {
            padding: 3mm 1.2mm 1.5mm 30%;
            border-left: .45px solid #4a4a4a;
            border-right: .45px solid #4a4a4a;
            font-size: 7pt;
        }

        .delivery-line {
            margin-bottom: 6mm;
        }

        .nothing-follows {
            font-style: italic;
            white-space: nowrap;
        }

        .final-summary-right {
            display: grid;
            grid-template-columns: 44% 56%;
            align-content: start;
            border-right: .45px solid #4a4a4a;
            font-size: 7.2pt;
        }

        .summary-label,
        .summary-value {
            min-height: 6mm;
            padding: 1.1mm 1.5mm .8mm;
        }

        .summary-label {
            text-align: right;
            font-weight: 700;
        }

        .summary-value {
            border-bottom: .65px solid var(--line);
            text-align: right;
            font-weight: 700;
        }

        .final-footer {
            position: absolute;
            right: 7.5mm;
            bottom: 4.5mm;
            left: 7.5mm;
            padding-top: 2mm;
            background: #fff;
        }

        .endorsement-heading {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            align-items: end;
            min-height: 5mm;
            padding: 0 2.5mm;
            border-top: .75px solid var(--line);
            font-size: 6.7pt;
        }

        .endorsement-heading .hospital-name {
            text-align: right;
        }

        .signatures-top {
            display: grid;
            grid-template-columns: 1fr 1fr 1.5fr;
            gap: 6mm;
            margin-top: 3.5mm;
            padding: 0 3mm;
        }

        .signature-block {
            text-align: center;
            font-size: 6.8pt;
        }

        .signature-image-space {
            height: 7mm;
            position: relative;
        }

        .signature-image-space::after {
            content: '';
            position: absolute;
            right: 5%;
            bottom: .6mm;
            left: 5%;
            border-bottom: .45px dashed transparent;
        }

        .signature-name {
            min-height: 5mm;
            padding: 0 1mm .9mm;
            border-bottom: .7px solid var(--line);
            font-size: 7.4pt;
            line-height: 1.05;
        }

        .signature-title {
            padding-top: 1mm;
            line-height: 1.15;
        }

        .footer-lower {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 10mm;
            margin-top: 2.5mm;
            padding: 0 3mm;
        }

        .conditions {
            font-size: 5.7pt;
            line-height: 1.12;
        }

        .conditions-title {
            margin-bottom: .6mm;
            text-align: center;
            font-weight: 700;
        }

        .conditions p {
            margin: 0;
        }

        .approvals {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 28mm;
        }

        .admin-signature {
            align-self: end;
            width: 68%;
        }

        .approved-by {
            margin-left: 4mm;
            font-size: 6.6pt;
            font-weight: 700;
        }

        .ceo-signature {
            align-self: center;
            width: 68%;
        }

        .privacy-note {
            margin-top: 2.2mm;
            text-align: center;
            font-size: 5.8pt;
            line-height: 1.13;
        }

        .privacy-note strong {
            font-weight: 700;
        }

        @media print {
    @page {
        size: Letter portrait;
        margin: 0;
    }

    html,
    body {
        width: 215.9mm;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .print-actions {
        display: none !important;
    }

    .po-page {
        width: 215.9mm;
        height: 278.5mm;
        min-height: 278.5mm;
        max-height: 278.5mm;

        margin: 0 !important;
        box-shadow: none !important;

        overflow: hidden;

        break-inside: avoid !important;
        page-break-inside: avoid !important;

        break-after: page;
        page-break-after: always;
    }

    .po-page:last-of-type {
        break-after: auto;
        page-break-after: auto;
    }
}
    </style>
</head>
<body>
@php
    $supplierName = trim((string) ($purchaseOrder->fullname ?: $purchaseOrder->GuarantorName));
    $telephone = trim((string) ($purchaseOrder->prtelno ?: $purchaseOrder->mobilephone));
    $terms = trim((string) ($purchaseOrder->Terms ?? ''));

    $formatMoney = static fn ($value) => number_format((float) ($value ?? 0), 2, '.', ',');

    $formatQuantity = static function ($value): string {
        $number = (float) ($value ?? 0);

        if (floor($number) === $number) {
            return number_format($number, 0, '.', ',');
        }

        return rtrim(rtrim(number_format($number, 4, '.', ','), '0'), '.');
    };

    $logoFile = public_path('images/purchase-order/po-header-logo.png');
    $logoSource = file_exists($logoFile)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
        : null;

    $signatureSlotDefaults = [
        1 => [
            'name' => 'JEROME P. SEÑORA',
            'title' => 'PURCHASING MANAGER',
        ],
        2 => [
            'name' => 'PEARL SAN JUAN',
            'title' => 'FINANCE DIRECTOR',
        ],
        3 => [
            'name' => 'DR. ADRIEN R. QUIDLAT / DR. AMELIA T. MENDOZA',
            'title' => 'MEDICAL DIRECTOR / NURSING DIRECTOR',
        ],
        4 => [
            'name' => 'NORMAN R. BIOLA',
            'title' => 'ADMINISTRATION DIRECTOR',
        ],
        5 => [
            'name' => 'RAMTA / DMT / ALTA',
            'title' => 'PRESIDENT / VICE CHAIR / CEO',
        ],
    ];

    $providedSignatureSlots = collect($signatureSlots ?? [])
        ->keyBy(
            static fn (array $slot): int =>
                (int) ($slot['sequence'] ?? 0)
        );

    $resolveSignatureSlot = static function (int $sequence) use (
        $providedSignatureSlots,
        $signatureSlotDefaults
    ): array {
        $provided = $providedSignatureSlots->get($sequence, []);

        return [
            'sequence' => $sequence,
            'name' => trim((string) (
                $provided['name']
                ?? $signatureSlotDefaults[$sequence]['name']
            )),
            'title' => trim((string) (
                $provided['title']
                ?? $signatureSlotDefaults[$sequence]['title']
            )),
        ];
    };
@endphp

@unless ($serverPdf ?? false)
    <div class="print-actions">
        <button type="button" onclick="window.print()">Print Purchase Order</button>
    </div>
@endunless

@foreach ($pages as $pageItems)
    <section class="po-page">
        <header class="po-header">
            <div class="brand">
                @if ($logoSource)
                    <img src="{{ $logoSource }}" alt="Perpetual Help Medical Center - Las Piñas">
                @else
                    <div class="brand-fallback">PERPETUAL HELP MEDICAL CENTER - LAS PIÑAS</div>
                @endif
            </div>

            <div class="document-title">PURCHASE ORDER</div>
            <div class="form-code">FM-PUR-004-1/5 07-11-2023</div>

            <div class="header-grid">
                <div class="supplier-fields">
                    <div class="field-row">
                        <span class="label">SUPPLIER'S NAME :</span>
                        <span class="value">{{ $supplierName }}</span>
                    </div>

                    <div class="field-row">
                        <span class="label">CONTACT PERSON :</span>
                        <span class="value">{{ trim((string) ($purchaseOrder->prcontactperson ?? '')) }}</span>
                    </div>
                </div>

                <div class="po-meta">
                    <div class="field-row">
                        <span class="label">No. :</span>
                        <span class="value">{{ $purchaseOrder->PONo }}</span>
                    </div>

                    <div class="field-row">
                        <span class="label">DATE :</span>
                        <span class="value normal">{{ $poDate }}</span>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <span class="label">ADDRESS :</span>
                <span class="value">{{ trim((string) ($purchaseOrder->praddress ?? '')) }}</span>
            </div>

            <div class="supplier-bottom">
                <div class="field-row">
                    <span class="label">TEL. NO. :</span>
                    <span class="value">{{ $telephone }}</span>
                </div>

                <div class="field-row terms-row">
                    <span class="label">TERMS :</span>
                    <span class="value normal">{{ $terms }}</span>
                </div>
            </div>

            <div class="table-rule"></div>
        </header>

        <main class="items-region">
            <table class="item-table">
                <colgroup>
                    <col class="qty">
                    <col class="unit">
                    <col class="code">
                    <col class="description">
                    <col class="unit-cost">
                    <col class="amount">
                </colgroup>

                <thead>
                    <tr>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Item Code</th>
                        <th>Description</th>
                        <th>Unit Cost</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pageItems as $item)
                        <tr>
                            <td class="center">{{ $formatQuantity($item->qty) }}</td>
                            <td class="center">{{ strtoupper(trim((string) ($item->unit ?? ''))) }}</td>
                            <td class="center">{{ trim((string) ($item->ItemId ?? '')) }}</td>
                            <td class="description-cell">{{ trim((string) ($item->itemdesc ?? '')) }}</td>
                            <td class="number">{{ $formatMoney($item->price) }}</td>
                            <td class="number">{{ $formatMoney($item->Amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($loop->last)
                <div class="final-summary">
                    <div class="final-summary-left">
                        <div class="delivery-line">
                            EXPECTED DELIVERY DATE:
                            <strong>{{ $deliveryDate }}</strong>
                        </div>
                        <div class="nothing-follows">*** NOTHING FOLLOWS ***</div>
                    </div>

                    <div class="final-summary-right">
                        <div class="summary-label">Discount</div>
                        <div class="summary-value">{{ $formatMoney($discount) }}</div>

                        <div class="summary-label">Total Amount</div>
                        <div class="summary-value">{{ $formatMoney($totalAmount) }}</div>
                    </div>
                </div>
            @else
                <div class="items-bottom-line"></div>
            @endif
        </main>

        @if ($loop->last)
            <footer class="final-footer">
                <div class="endorsement-heading">
                    <div>ENDORSED BY:</div>
                    <div class="hospital-name">UNIVERSITY OF PERPETUAL HELP DALTA MEDICAL CENTER</div>
                </div>

                <div class="signatures-top">
                    @foreach ([1, 2, 3] as $signatureOrder)
                        @php($signatureSlot = $resolveSignatureSlot($signatureOrder))

                        <div
                            class="signature-block document-signature-slot"
                            data-signature-order="{{ $signatureOrder }}"
                        >
                            <div class="signature-image-space" aria-hidden="true"></div>
                            <div class="signature-name">{{ $signatureSlot['name'] }}</div>
                            <div class="signature-title">{{ $signatureSlot['title'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="footer-lower">
                    <div class="conditions">
                        <div class="conditions-title">CONDITIONS:</div>
                        <p>1. Goods are subject to our inspection upon arrival. Goods delivered not in accordance with specifications will be returned and are not to be replaced unless instructed to do so.</p>
                        <p>2. NO ACCOUNT WILL BE PAID UNLESS ORIGINAL COPIES OF THE COVERING INVOICE AND PURCHASE ORDER ARE PRESENTED.</p>
                        <p>3. Failure of the seller to deliver up to the deadline, the seller shall be liable for liquidated damages equivalent to one-tenth (1/10) of one percent (1%) of the cost of goods for every day of delay.</p>
                        <p>For service contracts this liquidated damages based on the unperformed portion shall be deducted from the contract price.</p>
                        <p>NOTE:</p>
                        <p>Please indicate P.O. No. on all invoices.</p>
                    </div>

                    <div class="approvals">
                        @php($signatureSlot = $resolveSignatureSlot(4))
                        <div
                            class="signature-block admin-signature document-signature-slot"
                            data-signature-order="4"
                        >
                            <div class="signature-image-space" aria-hidden="true"></div>
                            <div class="signature-name">{{ $signatureSlot['name'] }}</div>
                            <div class="signature-title">{{ $signatureSlot['title'] }}</div>
                        </div>

                        <div class="approved-by">APPROVED BY:</div>

                        @php($signatureSlot = $resolveSignatureSlot(5))
                        <div
                            class="signature-block ceo-signature document-signature-slot"
                            data-signature-order="5"
                        >
                            <div class="signature-image-space" aria-hidden="true"></div>
                            <div class="signature-name">{{ $signatureSlot['name'] }}</div>
                            <div class="signature-title">{{ $signatureSlot['title'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="privacy-note">
                    <strong>NOTE:</strong>
                    In compliance with the Data Privacy Act of 2012 (RA 10173), all data / information gathered shall be treated as confidential and shall be managed by PHMC and SUPPLIERS with utmost confidentiality.
                </div>
            </footer>
        @endif

    </section>
@endforeach
</body>
</html>