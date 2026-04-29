<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quote;
use App\Models\SalesOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Contracts\Cache\LockTimeoutException;

class MisaMeInvoiceService
{
    public function getPublishedViewUrl(Invoice $invoice): string
    {
        $transactionId = trim((string) ($invoice->misa_transaction_id ?? ''));
        if ($transactionId === '') {
            throw new \RuntimeException('Hóa đơn chưa có TransactionID để mở trên MISA.');
        }

        $token = $this->getToken();
        $response = $this->webappRequest($token)
            ->post('/invoice/publishview', [$transactionId]);

        $body = (string) $response->body();
        $json = null;
        try {
            $json = $response->json();
        } catch (\Throwable $e) {
            $json = null;
        }

        if (!$response->successful()) {
            throw new \RuntimeException('MISA mở bản hóa đơn trả HTTP ' . $response->status() . '. Body: ' . $this->shortenResponse($body));
        }

        $payload = is_array($json) ? $json : [];
        $success = Arr::get($payload, 'success', Arr::get($payload, 'Success', null));
        $errorCode = (string) Arr::get($payload, 'errorCode', Arr::get($payload, 'ErrorCode', ''));
        $viewUrl = (string) Arr::get($payload, 'data', Arr::get($payload, 'Data', ''));

        if ($success !== true || $errorCode !== '' || trim($viewUrl) === '') {
            throw new \RuntimeException('MISA không trả link xem hóa đơn hợp lệ. Body: ' . $this->shortenResponse($body));
        }

        return $viewUrl;
    }

    public function verifyIssuedInvoice(Invoice $invoice): array
    {
        $refId = trim((string) ($invoice->misa_ref_id ?? ''));
        if ($refId === '') {
            throw new \RuntimeException('Hóa đơn chưa có RefID để đối soát MISA.');
        }

        $token = $this->getToken();

        $invoiceWithCode = $this->detectInvoiceWithCodeFromSeries((string) ($invoice->misa_inv_series ?? ''));

        $response = $this->webappRequest($token)
            ->post('/invoice/status?invoiceWithCode=' . ($invoiceWithCode ? 'true' : 'false') . '&inputType=2', [$refId]);

        $body = (string) $response->body();
        $json = null;
        try {
            $json = $response->json();
        } catch (\Throwable $e) {
            $json = null;
        }

        $attempt = [
            'name' => 'webapp_getlist_by_refid',
            'endpoint' => '/webapp/getlist',
            'query' => [],
            'request_body' => [$refId],
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $body,
            'json' => $json,
        ];

        return [
            'transaction_id' => (string) ($invoice->misa_transaction_id ?? ''),
            'ref_id' => $refId,
            'best' => $attempt,
            'attempts' => [$attempt],
        ];
    }

    public function createDraftFromSalesOrder(SalesOrder $salesOrder, ?Quote $quote = null, array $overrides = []): array
    {
        $token = $this->getToken();

        $template = null;
        try {
            $template = $this->getPreferredTemplate($token);
        } catch (\Throwable $e) {
            logger()->warning('MISA get template failed when creating draft, fallback to configured inv_series', [
                'sales_order_id' => $salesOrder->id,
                'message' => $e->getMessage(),
            ]);
        }

        $invoiceData = $this->makeInvoiceData($salesOrder, $quote, $template, $overrides);
        $this->validateInvoiceData($invoiceData);

        logger()->info('MISA create draft request', [
            'sales_order_id' => $salesOrder->id,
            'endpoint' => $this->baseUrl() . '/webapp/insert',
            'ref_id' => (string) Arr::get($invoiceData, 'RefID', ''),
            'inv_series' => (string) Arr::get($invoiceData, 'InvSeries', ''),
        ]);

        $response = $this->webappRequest($token)->post('/webapp/insert', $invoiceData);
        $responseBody = (string) $response->body();

        $responseJson = null;
        try {
            $responseJson = $response->json();
        } catch (\Throwable $e) {
            $responseJson = null;
        }

        $responseData = is_array($responseJson) ? $responseJson : [];

        if (!$response->successful()) {
            logger()->error('MISA draft create HTTP error', [
                'endpoint' => $this->baseUrl() . '/webapp/insert',
                'request' => $invoiceData,
                'status' => $response->status(),
                'response_body' => $responseBody,
                'response_json' => $responseData,
            ]);

            throw new \RuntimeException('MISA tạo hóa đơn nháp trả HTTP ' . $response->status() . '. Body: ' . $this->shortenResponse($responseBody));
        }

        $errorCode = (string) Arr::get($responseData, 'ErrorCode', Arr::get($responseData, 'errorCode', ''));
        if ($errorCode !== '') {
            $errorMessage = (string) Arr::get($responseData, 'DescriptionErrorCode', Arr::get($responseData, 'descriptionErrorCode', ''));
            throw new \RuntimeException('MISA tạo hóa đơn nháp thất bại: ' . $errorCode . ($errorMessage !== '' ? (' - ' . $errorMessage) : '') . '.');
        }

        $refId = (string) Arr::get($invoiceData, 'RefID', '');

        return [
            'request' => $invoiceData,
            'response' => $responseData,
            'template' => $template,
            'invoice_data' => $invoiceData,
            'created' => [[
                'RefID' => $refId,
                'TransactionID' => (string) (
                    Arr::get($responseData, 'TransactionID')
                    ?? Arr::get($responseData, 'transactionID')
                    ?? Arr::get($responseData, 'Data.TransactionID')
                    ?? Arr::get($responseData, 'data.TransactionID')
                    ?? $refId
                ),
                'InvoiceCode' => (string) (
                    Arr::get($responseData, 'InvoiceCode')
                    ?? Arr::get($responseData, 'invoiceCode')
                    ?? Arr::get($responseData, 'InvNo')
                    ?? Arr::get($responseData, 'invNo')
                    ?? ''
                ),
            ]],
            'published' => [],
            'draft_only' => true,
        ];
    }

    public function issueFromSalesOrder(SalesOrder $salesOrder, ?Quote $quote = null, array $overrides = [], bool $draftOnly = false): array
    {
        $token = $this->getToken();

        $template = null;
        try {
            $template = $this->getPreferredTemplate($token);
        } catch (\Throwable $e) {
            logger()->warning('MISA get template failed, fallback to configured inv_series', [
                'sales_order_id' => $salesOrder->id,
                'message' => $e->getMessage(),
            ]);
        }

        $invoiceData = $this->makeInvoiceData($salesOrder, $quote, $template, $overrides);
        $this->validateInvoiceData($invoiceData);

        $signType = $draftOnly ? 1 : (int) $this->settings('sign_type', 2);
        if (!$draftOnly && !in_array($signType, [2, 5], true)) {
            throw new \RuntimeException('SignType không hợp lệ cho phát hành trực tiếp. Vui lòng cấu hình sign_type = 2 (HSM) hoặc 5 (không ký cho MTT).');
        }

        $payload = [
            'SignType' => $signType,
            'InvoiceData' => [$invoiceData],
            'PublishInvoiceData' => null,
        ];

        if ($signType === 2) {
            $certificateSn = trim((string) $this->settings('certificate_sn', ''));
            if ($certificateSn === '') {
                throw new \RuntimeException('Thiếu CertificateSN cho SignType=2. Vui lòng cấu hình chứng thư số trong phần cài đặt MISA.');
            }
            $payload['CertificateSN'] = $certificateSn;
        }

        $invSeries = (string) Arr::get($invoiceData, 'InvSeries', '');
        $invoiceWithCode = $this->detectInvoiceWithCodeFromSeries($invSeries);
        $lock = Cache::lock('misa:draft:inv_series:' . $invSeries, 20);

        try {
            $lock->block(10);
        } catch (LockTimeoutException $e) {
            throw new \RuntimeException('Hệ thống đang đồng bộ hóa đơn khác cùng ký hiệu ' . $invSeries . '. Vui lòng thử lại sau vài giây.');
        }

        try {
            $previewResponse = $this->webappRequest($token)
                ->post('/invoice/unpublishview', $invoiceData);

            $previewBody = (string) $previewResponse->body();
            $previewJson = null;
            try {
                $previewJson = $previewResponse->json();
            } catch (\Throwable $e) {
                $previewJson = null;
            }

            $previewSuccess = is_array($previewJson)
                ? Arr::get($previewJson, 'success', Arr::get($previewJson, 'Success', null))
                : null;
            $previewErrorCodeRaw = is_array($previewJson)
                ? Arr::get($previewJson, 'errorCode', Arr::get($previewJson, 'ErrorCode', ''))
                : '';
            $previewErrorCode = is_array($previewErrorCodeRaw)
                ? (json_encode($previewErrorCodeRaw, JSON_UNESCAPED_UNICODE) ?: '')
                : (string) $previewErrorCodeRaw;

            if (!$previewResponse->successful() || $previewSuccess !== true || $previewErrorCode !== '') {
                logger()->error('MISA preview invoice error', [
                    'endpoint' => $this->baseUrl() . '/invoice/unpublishview',
                    'request' => $invoiceData,
                    'status' => $previewResponse->status(),
                    'response_body' => $previewBody,
                    'response_json' => is_array($previewJson) ? $previewJson : null,
                ]);

                throw new \RuntimeException('MISA preview hóa đơn lỗi. HTTP ' . $previewResponse->status() . '. Body: ' . $this->shortenResponse($previewBody));
            }

            $response = $this->webappRequest($token)
                ->post('/invoice', $payload);

            $responseBody = (string) $response->body();
            $responseJson = null;
            try {
                $responseJson = $response->json();
            } catch (\Throwable $e) {
                $responseJson = null;
            }

            $responseData = is_array($responseJson) ? $responseJson : [];

            if (!$response->successful()) {
                logger()->error('MISA draft sync HTTP error', [
                    'endpoint' => $this->baseUrl() . '/invoice',
                    'request' => $payload,
                    'status' => $response->status(),
                    'response_body' => $responseBody,
                    'response_json' => $responseData,
                ]);

                if ($response->status() >= 500) {
                    $refId = (string) Arr::get($invoiceData, 'RefID', '');
                    if ($refId !== '') {
                        $probeResponse = $this->webappRequest($token)
                            ->post('/invoice/status?invoiceWithCode=' . ($invoiceWithCode ? 'true' : 'false') . '&inputType=2', [$refId]);

                        $probeJson = null;
                        try {
                            $probeJson = $probeResponse->json();
                        } catch (\Throwable $e) {
                            $probeJson = null;
                        }

                        $probeData = is_array($probeJson)
                            ? Arr::get($probeJson, 'data', Arr::get($probeJson, 'Data', []))
                            : [];
                        if (is_string($probeData)) {
                            $decodedProbeData = json_decode($probeData, true);
                            $probeData = is_array($decodedProbeData) ? $decodedProbeData : [];
                        }

                        if ($probeResponse->successful() && is_array($probeData) && !empty($probeData)) {
                            logger()->warning('MISA insert HTTP500 but getlist found invoice by RefID', [
                                'ref_id' => $refId,
                                'probe_status' => $probeResponse->status(),
                                'probe_data' => $probeData,
                            ]);

                            $first = Arr::first($probeData) ?: [];

                            return [
                                'request' => $payload,
                                'response' => $responseData,
                                'template' => $template,
                                'invoice_data' => $invoiceData,
                                'created' => [[
                                    'RefID' => $refId,
                                    'TransactionID' => (string) (Arr::get($first, 'TransactionID', Arr::get($first, 'transactionID', $refId))),
                                    'InvoiceCode' => (string) (Arr::get($first, 'InvoiceCode', Arr::get($first, 'invoiceCode', Arr::get($first, 'InvNo', '')))),
                                ]],
                                'published' => [],
                            ];
                        }
                    }
                }

                throw new \RuntimeException('MISA phát hành trả HTTP ' . $response->status() . '. Body: ' . $this->shortenResponse($responseBody));
            }

            $success = Arr::get($responseData, 'Success', Arr::get($responseData, 'success', null));
            $apiErrorCodeRaw = Arr::get($responseData, 'ErrorCode', Arr::get($responseData, 'errorCode', ''));
            $apiDescriptionRaw = Arr::get($responseData, 'DescriptionErrorCode', Arr::get($responseData, 'descriptionErrorCode', ''));
            $errorRaw = Arr::get($responseData, 'error', '');

            if (is_array($errorRaw) && !empty($errorRaw)) {
                $firstItemError = Arr::first($errorRaw) ?: [];
                $itemCode = (string) Arr::get($firstItemError, 'ErrorCode', Arr::get($firstItemError, 'errorCode', ''));
                $itemMessage = (string) Arr::get($firstItemError, 'ErrorMessage', Arr::get($firstItemError, 'errorMessage', ''));

                throw new \RuntimeException('MISA item error: ' . ($itemCode !== '' ? $itemCode : 'UnknownItemError') . ($itemMessage !== '' ? (' - ' . $itemMessage) : '') . '. Body: ' . $this->shortenResponse($responseBody));
            }

            $apiErrorCode = is_array($apiErrorCodeRaw)
                ? (json_encode($apiErrorCodeRaw, JSON_UNESCAPED_UNICODE) ?: '')
                : (string) $apiErrorCodeRaw;
            $apiDescription = is_array($apiDescriptionRaw)
                ? (json_encode($apiDescriptionRaw, JSON_UNESCAPED_UNICODE) ?: '')
                : (string) $apiDescriptionRaw;

            if ($success === false || $apiErrorCode !== '') {
                $errorText = trim(implode(' | ', array_filter([
                    $apiErrorCode !== '' ? $apiErrorCode : null,
                    $apiDescription !== '' ? $apiDescription : null,
                ])));

                throw new \RuntimeException('MISA phát hành thất bại: ' . ($errorText !== '' ? $errorText : 'UnknownError') . '. Body: ' . $this->shortenResponse($responseBody));
            }

            $createResultRaw = Arr::get($responseData, 'createInvoiceResult', Arr::get($responseData, 'CreateInvoiceResult', []));
            if (is_string($createResultRaw)) {
                $decodedCreate = json_decode($createResultRaw, true);
                $createResultRaw = is_array($decodedCreate) ? $decodedCreate : [];
            }
            $created = Arr::first((array) Arr::wrap($createResultRaw)) ?: [];

            $publishResultRaw = Arr::get($responseData, 'publishInvoiceResult', Arr::get($responseData, 'PublishInvoiceResult', []));
            if (is_string($publishResultRaw)) {
                $decodedPublish = json_decode($publishResultRaw, true);
                $publishResultRaw = is_array($decodedPublish) ? $decodedPublish : [];
            }
            if (is_array($publishResultRaw) && Arr::isAssoc($publishResultRaw)) {
                $published = $publishResultRaw;
            } else {
                $published = Arr::first((array) Arr::wrap($publishResultRaw)) ?: [];
            }

            $createErrorCode = (string) (Arr::get($created, 'ErrorCode', Arr::get($created, 'errorCode', '')));
            if ($createErrorCode !== '') {
                $createErrorMessage = (string) Arr::get($created, 'DescriptionErrorCode', Arr::get($created, 'descriptionErrorCode', ''));
                throw new \RuntimeException('MISA tạo dữ liệu hóa đơn thất bại: ' . $createErrorCode . ($createErrorMessage !== '' ? (' - ' . $createErrorMessage) : ''));
            }

            $publishErrorCode = (string) (Arr::get($published, 'ErrorCode', Arr::get($published, 'errorCode', '')));
            if ($publishErrorCode !== '') {
                $publishErrorMessage = (string) Arr::get($published, 'DescriptionErrorCode', Arr::get($published, 'descriptionErrorCode', ''));
                throw new \RuntimeException('MISA phát hành thất bại: ' . $publishErrorCode . ($publishErrorMessage !== '' ? (' - ' . $publishErrorMessage) : ''));
            }

            logger()->info('MISA issue response accepted', [
                'sales_order_id' => $salesOrder->id,
                'status' => $response->status(),
                'response' => $responseData,
                'created' => $created,
                'published' => $published,
            ]);

            return [
                'request' => $payload,
                'response' => $responseData,
                'template' => $template,
                'invoice_data' => $invoiceData,
                'created' => $created,
                'published' => $published,
                'draft_only' => $draftOnly,
            ];
        } finally {
            optional($lock)->release();
        }
    }

    public function persistIssuedInvoice(SalesOrder $salesOrder, array $misaResult): Invoice
    {
        $invoiceData = (array) ($misaResult['invoice_data'] ?? []);

        $createdRaw = $misaResult['created']
            ?? Arr::get($misaResult['response'], 'data', Arr::get($misaResult['response'], 'Data', Arr::get($misaResult['response'], 'createInvoiceResult', Arr::get($misaResult['response'], 'CreateInvoiceResult', []))));
        if (is_string($createdRaw)) {
            $decodedCreatedRaw = json_decode($createdRaw, true);
            $createdRaw = is_array($decodedCreatedRaw) ? $decodedCreatedRaw : [];
        }
        if (is_array($createdRaw) && Arr::isAssoc($createdRaw)) {
            $created = $createdRaw;
        } else {
            $created = Arr::first((array) Arr::wrap($createdRaw)) ?: [];
        }

        $publishedRaw = $misaResult['published'] ?? Arr::get($misaResult['response'], 'publishInvoiceResult', Arr::get($misaResult['response'], 'PublishInvoiceResult', []));
        if (is_string($publishedRaw)) {
            $decodedPublishedRaw = json_decode($publishedRaw, true);
            $publishedRaw = is_array($decodedPublishedRaw) ? $decodedPublishedRaw : [];
        }
        if (is_array($publishedRaw) && Arr::isAssoc($publishedRaw)) {
            $published = $publishedRaw;
        } else {
            $published = Arr::first((array) Arr::wrap($publishedRaw)) ?: [];
        }

        $draftOnly = (bool) ($misaResult['draft_only'] ?? false);

        $transactionId = (string) (
            Arr::get($created, 'TransactionID')
            ?? Arr::get($created, 'transactionID')
            ?? Arr::get($published, 'TransactionID')
            ?? Arr::get($published, 'transactionID')
            ?? Arr::get($invoiceData, 'TransactionID', '')
        );
        $invoiceCode = (string) (
            Arr::get($published, 'InvNo')
            ?? Arr::get($published, 'invNo')
            ?? Arr::get($published, 'InvoiceCode')
            ?? Arr::get($published, 'invoiceCode')
            ?? Arr::get($created, 'InvNo')
            ?? Arr::get($created, 'invNo')
            ?? Arr::get($created, 'InvoiceCode')
            ?? Arr::get($created, 'invoiceCode')
            ?? Arr::get($misaResult['response'], 'InvNo')
            ?? Arr::get($misaResult['response'], 'invNo')
            ?? Arr::get($misaResult['response'], 'InvoiceCode')
            ?? Arr::get($misaResult['response'], 'invoiceCode', '')
        );
        $misaInvSeries = (string) Arr::get($invoiceData, 'InvSeries', '');
        $refId = (string) (Arr::get($invoiceData, 'RefID', Arr::get($created, 'RefID', '')));
        $syncedAt = now();

        $orderId = (int) ($salesOrder->source_order_id ?? 0);
        if ($orderId <= 0) {
            $salesOrder->loadMissing('quote');
            $orderId = (int) ($salesOrder->quote?->source_order_id ?? 0);
        }

        if ($orderId <= 0) {
            throw new \RuntimeException('Không thể lưu hóa đơn nội bộ: thiếu liên kết order_id hợp lệ. SalesOrder chưa có source_order_id và Quote liên quan cũng không có source_order_id.');
        }

        $salesOrder->loadMissing('items');

        $invoice = Invoice::create([
            'order_id' => $orderId,
            'sales_order_id' => $salesOrder->id,
            'invoice_code' => $invoiceCode !== '' ? $invoiceCode : ($draftOnly ? ('MISA-DRAFT-' . $transactionId) : ('MISA-' . $transactionId)),
            'status' => $draftOnly ? 'draft' : 'issued',
            'issued_at' => $syncedAt,
            'vat_percent' => (float) ($salesOrder->vat_percent ?? 0),
            'discount_percent' => (float) ($salesOrder->discount_percent ?? 0),
            'sub_total' => (float) $salesOrder->items->sum(fn ($item) => (float) ($item->unit_price ?? 0) * (int) ($item->quantity ?? 0)),
            'vat_amount' => (float) Arr::get($invoiceData, 'TotalVATAmountOC', 0),
            'total_amount' => (float) Arr::get($invoiceData, 'TotalAmountOC', 0),
            'note' => $draftOnly ? 'Đã tạo hóa đơn nháp trên MISA (chưa phát hành)' : 'Đã phát hành trực tiếp lên MISA',
            'misa_ref_id' => $refId,
            'misa_transaction_id' => $transactionId,
            'misa_inv_series' => $misaInvSeries,
            'misa_invoice_code' => $invoiceCode,
            'misa_request_payload' => $misaResult['request'] ?? [],
            'misa_response_payload' => $misaResult['response'] ?? [],
            'misa_error_message' => null,
            'misa_issued_at' => null,
        ]);

        foreach ($salesOrder->items as $item) {
            $qty = (int) ($item->quantity ?? 0);
            $unitPrice = (float) ($item->unit_price ?? 0);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'order_item_id' => null,
                'product_id' => (int) ($item->product_id ?? 0),
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $qty * $unitPrice,
                'unit' => (string) ($item->unit ?? ''),
            ]);
        }

        return $invoice;
    }

    protected function getToken(): string
    {
        $response = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->timeout((int) $this->settings('timeout', 30))
            ->post('/auth/token', [
                'appid' => $this->settings('app_id'),
                'taxcode' => $this->settings('tax_code'),
                'username' => $this->settings('username'),
                'password' => $this->settings('password'),
            ]);

        $status = $response->status();
        $body = (string) $response->body();
        $json = null;
        try {
            $json = $response->json();
        } catch (\Throwable $e) {
            $json = null;
        }

        if (!$response->successful()) {
            throw new \RuntimeException('MISA trả HTTP ' . $status . '. Body: ' . $this->shortenResponse($body));
        }

        $payload = is_array($json) ? $json : [];
        $success = Arr::get($payload, 'Success', Arr::get($payload, 'success', null));
        if ($success === false) {
            $message = (string) (Arr::get($payload, 'Errors', '') ?: Arr::get($payload, 'ErrorCode', '') ?: Arr::get($payload, 'descriptionErrorCode', ''));
            throw new \RuntimeException('MISA từ chối cấp token: ' . ($message !== '' ? $message : 'không rõ nguyên nhân') . '. Body: ' . $this->shortenResponse($body));
        }

        $token = (string) (
            Arr::get($payload, 'data.access_token')
            ?? Arr::get($payload, 'Data.access_token')
            ?? Arr::get($payload, 'Data')
            ?? Arr::get($payload, 'data')
            ?? Arr::get($payload, 'Token')
            ?? Arr::get($payload, 'token')
            ?? ''
        );

        if ($token === '') {
            throw new \RuntimeException('MISA không trả về token hợp lệ. Body: ' . $this->shortenResponse($body));
        }

        return $token;
    }

    protected function webappRequest(string $token)
    {
        logger()->debug('MISA token in use', [
            'token_masked' => $this->maskToken($token),
        ]);

        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->withToken($token)
            ->withHeaders([
                'TaxCode' => (string) $this->settings('tax_code', ''),
            ])
            ->timeout((int) $this->settings('timeout', 30));
    }

    protected function shortenResponse(string $body, int $limit = 1200): string
    {
        $body = trim($body);
        if ($body === '') {
            return 'empty response';
        }

        if (mb_strlen($body) > $limit) {
            return mb_substr($body, 0, $limit) . '...';
        }

        return $body;
    }

    protected function getPreferredTemplate(string $token): ?array
    {
        $invoiceWithCode = $this->detectInvoiceWithCodeFromSeries((string) $this->settings('inv_series', ''));

        $response = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->withToken($token)
            ->timeout((int) $this->settings('timeout', 30))
            ->get('/invoice/templates', [
                'invoiceWithCode' => $invoiceWithCode,
                'ticket' => false,
            ]);

        $body = (string) $response->body();
        $json = null;
        try {
            $json = $response->json();
        } catch (\Throwable $e) {
            $json = null;
        }

        if (!$response->successful()) {
            throw new \RuntimeException('MISA lấy mẫu hóa đơn trả HTTP ' . $response->status() . '. Body: ' . $this->shortenResponse($body));
        }

        $payload = is_array($json) ? $json : [];
        $items = Arr::get($payload, 'Data', Arr::get($payload, 'data', []));
        if (is_string($items)) {
            $decodedItems = json_decode($items, true);
            $items = is_array($decodedItems) ? $decodedItems : [];
        }

        if (!is_array($items) || empty($items)) {
            return null;
        }

        return $items[0];
    }

    protected function makeInvoiceData(SalesOrder $salesOrder, ?Quote $quote, ?array $template, array $overrides = []): array
    {
        $salesOrder->loadMissing(['items.product']);
        $items = $salesOrder->items->values();
        $exchangeRate = 1;
        $discountPercent = max(0, (float) ($salesOrder->discount_percent ?? ($quote->discount_percent ?? 0)));

        $roundMoney = static fn (float $value): float => (float) round($value, 0);

        $baseRows = $items->map(function ($item, $index) {
            $qty = (float) ($item->quantity ?? 0);
            $unitPrice = (float) ($item->unit_price ?? 0);
            $amountOC = $qty * $unitPrice;

            $productName = trim((string) ($item->product->name ?? 'Sản phẩm'));
            $productInfo = trim((string) (
                $item->product->information
                ?? $item->product->description
                ?? $productName
            ));

            return [
                'InventoryItemType' => 0,
                'SortOrderView' => $index + 1,
                'SortOrder' => $index + 1,
                'InventoryItemCode' => (string) ($item->product->serial_number ?? $item->product_id ?? ''),
                'ItemName' => $productName,
                'Description' => $productInfo,
                'UnitName' => trim((string) ($item->unit ?? 'Cái')),
                'Quantity' => $qty,
                'UnitPrice' => $unitPrice,
                'AmountOC' => $amountOC,
            ];
        })->values();

        $subTotal = (float) $baseRows->sum(fn ($row) => (float) $row['AmountOC']);
        $targetDiscount = $roundMoney($subTotal * ($discountPercent / 100));

        $lineItems = [];
        $allocatedDiscount = 0.0;
        $totalRows = $baseRows->count();

        foreach ($baseRows as $idx => $row) {
            $amountOC = (float) $row['AmountOC'];
            if ($totalRows <= 1) {
                $lineDiscount = $targetDiscount;
            } elseif ($idx === $totalRows - 1) {
                $lineDiscount = $targetDiscount - $allocatedDiscount;
            } else {
                $lineDiscount = $roundMoney($targetDiscount * ($amountOC / max($subTotal, 1)));
                $allocatedDiscount += $lineDiscount;
            }

            $lineDiscount = min(max($lineDiscount, 0), $amountOC);
            $amountWithoutVat = max(0, $amountOC - $lineDiscount);
            $lineVatPercent = max(0, (float) ($items->get($idx)?->vat_percent ?? $salesOrder->vat_percent ?? ($quote->vat_percent ?? 8)));
            $vatAmount = $roundMoney($amountWithoutVat * ($lineVatPercent / 100));

            $lineItems[] = [
                'ItemType' => 1,
                'SortOrder' => (int) $row['SortOrder'],
                'LineNumber' => (int) $row['SortOrder'],
                'ItemCode' => (string) $row['InventoryItemCode'],
                'ItemName' => (string) $row['ItemName'],
                'Description' => (string) $row['Description'],
                'UnitName' => (string) $row['UnitName'],
                'Quantity' => (float) $row['Quantity'],
                'UnitPrice' => (float) $row['UnitPrice'],
                'AmountOC' => (float) $amountOC,
                'Amount' => (float) $amountOC,
                'DiscountRate' => (float) $discountPercent,
                'DiscountAmountOC' => (float) $lineDiscount,
                'DiscountAmount' => (float) $lineDiscount,
                'AmountWithoutVATOC' => (float) $amountWithoutVat,
                'AmountWithoutVAT' => (float) $amountWithoutVat,
                'VATRateName' => ($lineVatPercent == 0.0 ? 'KCT' : (rtrim(rtrim(number_format($lineVatPercent, 2, '.', ''), '0'), '.') . '%')),
                'VATAmountOC' => (float) $vatAmount,
                'VATAmount' => (float) $vatAmount,
            ];
        }

        $totalDiscountAmountOC = (float) array_sum(array_map(fn ($row) => (float) $row['DiscountAmountOC'], $lineItems));
        $totalAmountWithoutVatOC = max(0, $subTotal - $totalDiscountAmountOC);
        $totalVatAmountOC = (float) array_sum(array_map(fn ($row) => (float) $row['VATAmountOC'], $lineItems));
        $totalAmountOC = $totalAmountWithoutVatOC + $totalVatAmountOC;

        $refId = (string) Str::uuid();
        $invSeries = (string) (
            $this->settings('inv_series', '')
            ?: Arr::get($template, 'InvSeries')
            ?: Arr::get($template, 'invSeries')
            ?: Arr::get($template, 'OrgInvSeries')
            ?: Arr::get($template, 'orgInvSeries')
        );
        $invoiceTemplateId = (string) (
            $this->settings('invoice_template_id', '')
            ?: Arr::get($template, 'InvoiceTemplateID')
            ?: Arr::get($template, 'invoiceTemplateID')
            ?: Arr::get($template, 'IPTemplateID')
            ?: Arr::get($template, 'ipTemplateID')
        );

        if (trim($invSeries) === '') {
            throw new \RuntimeException('Thiếu InvSeries (ký hiệu hóa đơn) để phát hành MISA. Vui lòng cấu hình InvSeries hoặc kiểm tra API /invoice/templates.');
        }

        if (trim($invoiceTemplateId) === '') {
            throw new \RuntimeException('Thiếu InvoiceTemplateID cho webapp/insert. Vui lòng cấu hình invoice_template_id trong Cấu hình MISA hoặc bật API templates webapp.');
        }

        $nowIso = now()->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i:sP');

        $defaultReceiverName = (string) ($salesOrder->customer_contact_person ?: $salesOrder->receiver_name);
        $overrideReceiverName = trim((string) ($overrides['receiver_name'] ?? ''));
        $receiverName = $overrideReceiverName !== '' ? $overrideReceiverName : $defaultReceiverName;

        $defaultReceiverEmail = trim((string) ($salesOrder->customer_email ?? ''));
        $overrideReceiverEmail = trim((string) ($overrides['receiver_email'] ?? ''));
        $receiverEmail = $overrideReceiverEmail !== '' ? $overrideReceiverEmail : $defaultReceiverEmail;

        $payload = [
            'RefID' => $refId,
            'InvoiceTemplateID' => $invoiceTemplateId,
            'InvSeries' => $invSeries,
            'InvDate' => now()->timezone('Asia/Ho_Chi_Minh')->toDateString(),
            'PaymentMethodName' => 'TM/CK',
            'CurrencyCode' => 'VND',
            'DiscountRate' => (float) $discountPercent,
            'ExchangeRate' => $exchangeRate,
            'TotalSaleAmountOC' => $subTotal,
            'TotalSaleAmount' => $subTotal,
            'TotalDiscountAmountOC' => $totalDiscountAmountOC,
            'TotalDiscountAmount' => $totalDiscountAmountOC,
            'TotalAmountWithoutVATOC' => $totalAmountWithoutVatOC,
            'TotalAmountWithoutVAT' => $totalAmountWithoutVatOC,
            'TotalVATAmountOC' => $totalVatAmountOC,
            'TotalVATAmount' => $totalVatAmountOC,
            'TotalAmountOC' => $totalAmountOC,
            'TotalAmount' => $totalAmountOC,
            'CreatedDate' => $nowIso,
            'ModifiedDate' => $nowIso,
            'CreatedBy' => (string) ($this->settings('username', 'ERP') ?: 'ERP'),
            'ModifiedBy' => (string) ($this->settings('username', 'ERP') ?: 'ERP'),
            'ContactName' => $receiverName,
            'ReceiverName' => $receiverName,
            'BuyerLegalName' => (string) ($salesOrder->invoice_company_name ?: $salesOrder->receiver_name),
            'BuyerTaxCode' => (string) ($salesOrder->customer_tax_code ?? ''),
            'BuyerAddress' => (string) ($salesOrder->invoice_address ?: $salesOrder->receiver_address),
            'BuyerFullName' => (string) ($salesOrder->receiver_name ?? ''),
            'BuyerEmail' => (string) ($salesOrder->customer_email ?? ''),
            'OriginalInvoiceDetail' => $lineItems,
            'TaxRateInfo' => $this->buildTaxRateInfoFromLineItems($lineItems),
        ];

        if ($receiverEmail !== '') {
            $payload['ReceiverEmail'] = $receiverEmail;
        }

        return $payload;
    }

    protected function buildTaxRateInfoFromLineItems(array $lineItems): array
    {
        $groups = [];

        foreach ($lineItems as $row) {
            $rateName = (string) ($row['VATRateName'] ?? '0%');
            $amountWithoutVat = (float) ($row['AmountWithoutVATOC'] ?? 0);
            $vatAmount = (float) ($row['VATAmountOC'] ?? 0);

            if (!isset($groups[$rateName])) {
                $groups[$rateName] = [
                    'VATRateName' => $rateName,
                    'AmountWithoutVATOC' => 0.0,
                    'VATAmountOC' => 0.0,
                ];
            }

            $groups[$rateName]['AmountWithoutVATOC'] += $amountWithoutVat;
            $groups[$rateName]['VATAmountOC'] += $vatAmount;
        }

        return array_values($groups);
    }

    protected function baseUrl(): string
    {
        return rtrim((string) $this->settings('base_url', config('services.meinvoice.base_url', 'https://testapi.meinvoice.vn/api/integration')), '/');
    }

    protected function detectInvoiceWithCodeFromSeries(string $invSeries): bool
    {
        $series = strtoupper(trim($invSeries));
        if (strlen($series) < 2) {
            return true;
        }

        return substr($series, 1, 1) === 'C';
    }

    protected function validateInvoiceData(array $invoiceData): void
    {
        if (trim((string) Arr::get($invoiceData, 'RefID', '')) === '') {
            throw new \RuntimeException('Payload MISA thiếu RefID.');
        }

        $details = Arr::get($invoiceData, 'OriginalInvoiceDetail', []);
        if (!is_array($details) || empty($details)) {
            throw new \RuntimeException('Payload MISA thiếu OriginalInvoiceDetail.');
        }

        foreach ($details as $idx => $detail) {
            $amount = (float) Arr::get((array) $detail, 'AmountOC', 0);
            if ($amount <= 0) {
                throw new \RuntimeException('Payload MISA không hợp lệ: OriginalInvoiceDetail[' . $idx . '].AmountOC phải > 0.');
            }

            $vatRateName = (string) Arr::get((array) $detail, 'VATRateName', '');
            if ($vatRateName === '') {
                throw new \RuntimeException('Payload MISA không hợp lệ: OriginalInvoiceDetail[' . $idx . '].VATRateName không được trống.');
            }
        }
    }

    protected function maskToken(string $token): string
    {
        $token = trim($token);
        if ($token === '') {
            return '';
        }

        if (strlen($token) <= 10) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 4) . str_repeat('*', max(strlen($token) - 8, 4)) . substr($token, -4);
    }

    protected function settings(string $key, mixed $default = null): mixed
    {
        $path = storage_path('app/misa-meinvoice-settings.json');
        $stored = [];
        if (File::exists($path)) {
            $stored = json_decode((string) File::get($path), true);
            if (!is_array($stored)) {
                $stored = [];
            }
        }

        return $stored[$key] ?? config('services.meinvoice.' . $key, $default);
    }
}
