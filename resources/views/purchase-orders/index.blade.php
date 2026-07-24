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
            font-family: Verdana;
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

        /*
         * Owns the Purchase Order table's outer border.
         *
         * A wrapper is more reliable than collapsed borders on colspan cells
         * when Chromium generates the stored PDF.
         */
        .item-table-frame {
            position: relative;

            width: 100%;

            border-left:
                .45px solid
                #4a4a4a;

            border-right:
                .45px solid
                #4a4a4a;

            border-bottom:
                1px solid
                var(--line);

            overflow: hidden;
        }

        .item-table {
            width: 100%;

            border-collapse: separate;
            border-spacing: 0;
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

        .item-table th:first-child,
.item-table td:first-child {
    border-left: 0;
}
.item-table th:last-child,
.item-table td:last-child {
    border-right: 0;
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

        /*
         * The final summary is rendered inside the same table as the items.
         * This keeps every outer and internal border physically connected.
         */
        /*
         * Only the final summary row reserves vertical space.
         * The zero-height closing row must remain zero-height.
         */
        .item-table tfoot .summary-row {
            height: 22mm;
        }

    .item-table tfoot td {
    height: 22mm;
    padding: 0;

    border-bottom: 0;

    vertical-align: top;
}

        /*
         * Keep the summary inside the same table so the outer left edge,
         * Description/Unit Cost divider and outer right edge remain continuous.
         */
        .item-table tfoot .final-summary-left-cell {
            position: relative;
            padding: 0;

            border-left: 0;
            border-right: .45px solid #4a4a4a;

            font-size: 7pt;
            overflow: hidden;
        }

        /*
         * The cell spans the first four columns (59% of the table).
         * The Description column starts after 29% of the whole table:
         *
         * 29 / 59 = 49.152542%
         *
         * Positioning a child is more reliable in PDF output than applying
         * percentage padding directly to a table cell.
         */
        .final-summary-description {
    position: absolute;

    top: 0;
    right: 1.2mm;
    bottom: 0;
    left: 49.152542%;

    display: grid;

    /*
     * Row 1 matches Discount.
     * Row 2 matches Total Amount.
     * Remaining rows contain reference information.
     */
    grid-template-rows:
        6mm
        6mm
        3.3mm
        3.3mm
        3.3mm;

    align-content: start;

    min-width: 0;
    overflow: hidden;
}

.pr-remarks-line,
.request-reference-line,
.delivery-line,
.nothing-follows {
    display: flex;
    align-items: center;
    min-width: 0;
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pr-remarks-line, .request-reference-line {
    font-size:7pt;
}

.delivery-line, .nothing-follows{
    font-size:7pt;
}
/*
 * Align PRremarks with the Total Amount row.
 */
.pr-remarks-line {
    grid-row: 2;
}

.request-reference-line {
    grid-row: 3;
}

.delivery-line {
    grid-row: 4;
}

.nothing-follows {
    grid-row: 5;
}

        .item-table tfoot .final-summary-right-cell {
            padding: 0;
            border-right: 0;
        }

        .nothing-follows {
            margin: 0;
            font-style: italic;
            white-space: nowrap;
        }

        .final-summary-right {
            display: grid;
            grid-template-columns:
                45.37%
                54.63%;
            align-content: start;
            width: 100%;
            margin-right:150px;
        }

        .summary-label,
        .summary-value {
            min-height: 6mm;
            padding: 1.1mm 1.5mm .8mm;
        }

        .summary-label {
            text-align: right;
            font-weight: 700;
            margin-left:45px;
            text-align:center;
            font-family:Verdana;
            font-size:8pt;
        }

        .summary-value {
            border-bottom: .65px solid var(--line);
            text-align: right;
            font-weight: 700;
        }

        .po-page-final {
    display: flex;
    flex-direction: column;
}

.po-page-final .po-header {
    flex: 0 0 auto;
}

.po-page-final .items-region {
    display: flex;
    flex: 1 1 auto;
    min-height: 0;
}

.po-page-final .item-table-frame {
    flex: 1 1 auto;
    min-height: 0;

    /*
     * The footer's top border will close the table.
     */
    border-bottom: 0;
}
    .final-footer {
    position: static;

    flex: 0 0 auto;

    width: 100%;
    margin: 0;
    padding: 0;

    background: #fff;
}

     .endorsement-heading {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    align-items: end;

    width: 100%;
    min-height: 5mm;

    margin: 0;
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

    margin-top: 2mm;

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
    display: flex;
    align-items: flex-end;
    justify-content: center;

    width: 100%;
    min-height: 3.8mm;

    margin: 0;
    padding:
        0
        1mm
        .25mm;

    border-bottom:
        .7px solid
        var(--line);

    font-size: 8pt;
    font-family:Tahoma;
    line-height: 1;

    text-align: center;
}

        .signature-title {
            padding-top: 1mm;
            line-height: 1.15;
            font-size:8pt;
            font-family:Verdana;
            text-transform: uppercase;
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
            font-size:7pt;
        }

        .conditions p {
            margin: 0;
            font-size:6pt;
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
            font-size: 7pt;
            line-height: 1.13;
        }

        .privacy-note strong {
            font-weight: 700;
        }

        /* =========================================================
   FONT OVERRIDES ONLY
   ========================================================= */

.document-title {
    font-family: Verdana, Arial, sans-serif;
    font-size: 9pt;
    font-weight: 700;
}

.form-code {
    font-family: Verdana, Arial, sans-serif;
    font-size: 8pt;
}

/* Header titles only */
.field-row .label {
    font-family: Verdana, Arial, sans-serif;
    font-size: 8pt;
}

/* PO number textbox */
.po-number-value {
    font-family: Tahoma, Arial, sans-serif;
    font-size: 12pt;
    font-weight: 700;
}

/* Date textbox */
.po-date-value {
    font-family: Tahoma, Arial, sans-serif;
    font-size: 8pt;
    font-weight: 400;
}

/* Table headings */
.item-table th {
    font-family: Verdana, Arial, sans-serif;
    font-size: 8pt;
}

/* Quantity, Unit and Item Code values */
.item-table tbody td:nth-child(1),
.item-table tbody td:nth-child(2),
.item-table tbody td:nth-child(3) {
    font-family: Verdana, Arial, sans-serif;
    font-size: 8pt;

    /*
     * Keeps the line box close to the original
     * 6.8pt × 1.18 rendering height.
     */
    line-height: 1;
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
    $prRemarks = trim(
    (string) (
        $purchaseOrder->PRremarks
        ?? ''
    )
);

/*
 * Prefer remarks because it contains values such as:
 * PRC#48466
 *
 * Use ReqNo when remarks is empty.
 */
$requestReference = trim(
    (string) (
        $purchaseOrder->remarks
        ?: $purchaseOrder->ReqNo
        ?: ''
    )
);

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
    <section
    class="
        po-page
        {{ $loop->last ? 'po-page-final' : '' }}
    "
>
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
                        <span class="value po-number-value">
    {{ $purchaseOrder->PONo }}
</span>
                    </div>

                    <div class="field-row">
                        <span class="label">DATE :</span>
                        <span class="value normal po-date-value">
    {{ $poDate }}
</span>
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
            <div class="item-table-frame">
                <table class="item-table">
                <colgroup>
                    <col class="qty">
                    <col class="unit">
                    <col class="code">
                    <col class="description">
                    <col class="unit-cost">
                    <col class="amount">
                </colgroup>

                @if ($loop->first)
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
                @endif

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

                @if ($loop->last)
                    <tfoot>
                        <tr class="summary-row">
                            <td
                                colspan="4"
                                class="final-summary-left-cell"
                            >
                               <div class="final-summary-description">
    @if ($prRemarks !== '')
        <div class="pr-remarks-line">
            {{ $prRemarks }}
        </div>
    @endif

    @if ($requestReference !== '')
        <div class="request-reference-line">
            {{ $requestReference }}
        </div>
    @endif

    <div class="delivery-line">
        EXPECTED DELIVERY DATE:

        @if (!empty($deliveryDate))
            <strong>{{ $deliveryDate }}</strong>
        @endif
    </div>

    <div class="nothing-follows">
        *** NOTHING FOLLOWS ***
    </div>
</div>
                            </td>

                            <td
                                colspan="2"
                                class="final-summary-right-cell"
                            >
                                <div class="final-summary-right">
                                    <div class="summary-label">Discount</div>
                                    <div class="summary-value">
                                        {{ $formatMoney($discount) }}
                                    </div>

                                    <div class="summary-label">Total Amount</div>
                                    <div class="summary-value">
                                        {{ $formatMoney($totalAmount) }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                @endif
                </table>
            </div>
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