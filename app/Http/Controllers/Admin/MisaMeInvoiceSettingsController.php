<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MisaMeInvoiceSettingsController extends Controller
{
    private string $settingsPath;

    public function __construct()
    {
        $this->settingsPath = storage_path('app/misa-meinvoice-settings.json');
    }

    public function edit()
    {
        $settings = $this->readSettings();

        return view('admin.misa_meinvoice.settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'base_url' => ['required', 'string', 'max:255'],
            'app_id' => ['required', 'string', 'max:255'],
            'tax_code' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'sign_type' => ['required', 'integer', 'in:1,2,3,4,5,6'],
            'certificate_sn' => ['nullable', 'string', 'max:255'],
            'inv_series' => ['nullable', 'string', 'max:50'],
            'invoice_template_id' => ['nullable', 'string', 'max:100'],
            'timeout' => ['nullable', 'integer', 'min:5', 'max:120'],
        ]);

        $settings = [
            'base_url' => rtrim($validated['base_url'], '/'),
            'app_id' => $validated['app_id'],
            'tax_code' => $validated['tax_code'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'sign_type' => (int) $validated['sign_type'],
            'certificate_sn' => $validated['certificate_sn'] ?? '',
            'inv_series' => trim((string) ($validated['inv_series'] ?? '')),
            'invoice_template_id' => trim((string) ($validated['invoice_template_id'] ?? '')),
            'timeout' => (int) ($validated['timeout'] ?? 30),
        ];

        File::ensureDirectoryExists(dirname($this->settingsPath));
        File::put($this->settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return back()->with('success', 'Đã lưu cấu hình MISA meInvoice.');
    }
    public function testConnection(Request $request)
    {
        $settings = $this->readSettings();

        try {
            $response = Http::baseUrl(rtrim((string) $settings['base_url'], '/'))
                ->acceptJson()
                ->timeout((int) ($settings['timeout'] ?? 30))
                ->post('/auth/token', [
                    'appid' => $settings['app_id'] ?? '',
                    'taxcode' => $settings['tax_code'] ?? '',
                    'username' => $settings['username'] ?? '',
                    'password' => $settings['password'] ?? '',
                ]);

            $body = (string) $response->body();
            $json = null;
            try {
                $json = $response->json();
            } catch (\Throwable $e) {
                $json = null;
            }

            return back()->with([
                'success' => 'Đã gọi thử kết nối MISA.',
                'misa_test_connection' => [
                    'status' => $response->status(),
                    'successful' => $response->successful(),
                    'body' => $body,
                    'json' => $json,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Test kết nối MISA thất bại: ' . $e->getMessage());
        }
    }

    public function fetchCertificates(Request $request)
    {
        $settings = $this->readSettings();

        try {
            $tokenResponse = Http::baseUrl(rtrim((string) $settings['base_url'], '/'))
                ->acceptJson()
                ->timeout((int) ($settings['timeout'] ?? 30))
                ->post('/auth/token', [
                    'appid' => $settings['app_id'] ?? '',
                    'taxcode' => $settings['tax_code'] ?? '',
                    'username' => $settings['username'] ?? '',
                    'password' => $settings['password'] ?? '',
                ]);

            $tokenBody = (string) $tokenResponse->body();
            $tokenPayload = null;
            try {
                $tokenPayload = $tokenResponse->json();
            } catch (\Throwable $e) {
                $tokenPayload = null;
            }

            $token = (string) Arr::get($tokenPayload, 'Data', Arr::get($tokenPayload, 'data', ''));
            if ($token === '') {
                return back()->with([
                    'error' => 'Không lấy được token MISA để tải danh sách chứng thư số.',
                    'misa_certificates' => [
                        'token_status' => $tokenResponse->status(),
                        'token_successful' => $tokenResponse->successful(),
                        'token_success' => Arr::get($tokenPayload, 'Success', Arr::get($tokenPayload, 'success', null)),
                        'token_error_code' => Arr::get($tokenPayload, 'ErrorCode', Arr::get($tokenPayload, 'errorCode', null)),
                        'token_errors' => Arr::get($tokenPayload, 'Errors', Arr::get($tokenPayload, 'errors', null)),
                        'token_body' => $tokenBody,
                        'status' => null,
                        'successful' => false,
                        'success' => null,
                        'error_code' => null,
                        'description_error_code' => null,
                        'errors' => null,
                        'custom_data' => null,
                        'body' => null,
                        'items' => [],
                    ],
                ]);
            }

            $certResponse = Http::baseUrl(rtrim((string) $settings['base_url'], '/'))
                ->acceptJson()
                ->withToken($token)
                ->timeout((int) ($settings['timeout'] ?? 30))
                ->get('/invoice/get-certificates');

            $body = (string) $certResponse->body();
            $json = null;
            try {
                $json = $certResponse->json();
            } catch (\Throwable $e) {
                $json = null;
            }

            $certificates = Arr::get($json, 'Data', Arr::get($json, 'data', []));
            if (is_string($certificates)) {
                $decodedCertificates = json_decode($certificates, true);
                $certificates = is_array($decodedCertificates) ? $decodedCertificates : [];
            }
            if (!is_array($certificates)) {
                $certificates = [];
            }

            return back()->with([
                'success' => 'Đã gọi API lấy chứng thư số từ MISA.',
                'misa_certificates' => [
                    'token_status' => $tokenResponse->status(),
                    'token_successful' => $tokenResponse->successful(),
                    'token_success' => Arr::get($tokenPayload, 'Success', Arr::get($tokenPayload, 'success', null)),
                    'token_error_code' => Arr::get($tokenPayload, 'ErrorCode', Arr::get($tokenPayload, 'errorCode', null)),
                    'token_errors' => Arr::get($tokenPayload, 'Errors', Arr::get($tokenPayload, 'errors', null)),
                    'token_body' => $tokenBody,
                    'status' => $certResponse->status(),
                    'successful' => $certResponse->successful(),
                    'success' => Arr::get($json, 'Success', Arr::get($json, 'success', null)),
                    'error_code' => Arr::get($json, 'ErrorCode', Arr::get($json, 'errorCode', null)),
                    'description_error_code' => Arr::get($json, 'DescriptionErrorCode', Arr::get($json, 'descriptionErrorCode', null)),
                    'errors' => Arr::get($json, 'Errors', Arr::get($json, 'errors', null)),
                    'custom_data' => Arr::get($json, 'CustomData', Arr::get($json, 'customData', null)),
                    'body' => $body,
                    'items' => $certificates,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Lấy chứng thư số thất bại: ' . $e->getMessage());
        }
    }

    public function fetchWebappTemplates(Request $request)
    {
        $settings = $this->readSettings();

        try {
            $templatesResult = $this->fetchInvoiceTemplatesFromApi($settings);

            return back()->with([
                'success' => 'Đã gọi API /invoice/templates từ MISA (invoiceWithCode=true/false).',
                'misa_webapp_templates' => [
                    'status' => $templatesResult['status'],
                    'successful' => $templatesResult['successful'],
                    'body' => $templatesResult['body'],
                    'items' => $templatesResult['items'],
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Lấy mẫu hóa đơn thất bại: ' . $e->getMessage());
        }
    }

    public function invoiceTemplates(Request $request)
    {
        $settings = $this->readSettings();

        try {
            $templatesResult = $this->fetchInvoiceTemplatesFromApi($settings);

            return view('admin.misa_meinvoice.invoice-templates', [
                'settings' => $settings,
                'templates' => $templatesResult['items'],
                'result' => $templatesResult,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return view('admin.misa_meinvoice.invoice-templates', [
                'settings' => $settings,
                'templates' => [],
                'result' => [
                    'status' => null,
                    'successful' => false,
                    'body' => '',
                    'error' => $e->getMessage(),
                ],
            ]);
        }
    }

    public function saveDefaultInvoiceTemplate(Request $request)
    {
        $validated = $request->validate([
            'invoice_template_id' => ['required', 'string', 'max:100'],
            'inv_series' => ['required', 'string', 'max:50'],
            'inv_template_no' => ['nullable', 'string', 'max:50'],
            'template_name' => ['nullable', 'string', 'max:255'],
            'is_used' => ['nullable', 'boolean'],
        ]);

        $settings = $this->readSettings();
        $settings['invoice_template_id'] = trim((string) $validated['invoice_template_id']);
        $settings['inv_series'] = trim((string) $validated['inv_series']);
        $settings['default_invoice_template_name'] = trim((string) ($validated['template_name'] ?? ''));
        $settings['default_invoice_template_no'] = trim((string) ($validated['inv_template_no'] ?? ''));
        $settings['default_invoice_template_is_used'] = (bool) ($validated['is_used'] ?? false);

        File::ensureDirectoryExists(dirname($this->settingsPath));
        File::put($this->settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return back()->with('success', 'Đã lưu mẫu hóa đơn mặc định để phát hành nhanh.');
    }

    private function fetchInvoiceTemplatesFromApi(array $settings): array
    {
        $tokenResponse = Http::baseUrl(rtrim((string) $settings['base_url'], '/'))
            ->acceptJson()
            ->timeout((int) ($settings['timeout'] ?? 30))
            ->post('/auth/token', [
                'appid' => $settings['app_id'] ?? '',
                'taxcode' => $settings['tax_code'] ?? '',
                'username' => $settings['username'] ?? '',
                'password' => $settings['password'] ?? '',
            ]);

        $tokenBody = (string) $tokenResponse->body();
        $tokenPayload = null;
        try {
            $tokenPayload = $tokenResponse->json();
        } catch (\Throwable $e) {
            $tokenPayload = null;
        }

        $token = (string) (Arr::get($tokenPayload, 'Data') ?? Arr::get($tokenPayload, 'data', ''));
        if ($token === '') {
            throw new \RuntimeException('Không lấy được token để gọi /invoice/templates. Body: ' . $tokenBody);
        }

        $callTemplates = function (bool $invoiceWithCode) use ($settings, $token): array {
            $response = Http::baseUrl(rtrim((string) $settings['base_url'], '/'))
                ->acceptJson()
                ->withToken($token)
                ->timeout((int) ($settings['timeout'] ?? 30))
                ->get('/invoice/templates', [
                    'invoiceWithCode' => $invoiceWithCode ? 'true' : 'false',
                    'ticket' => 'false',
                ]);

            $body = (string) $response->body();
            $json = null;
            try {
                $json = $response->json();
            } catch (\Throwable $e) {
                $json = null;
            }

            $items = Arr::get($json, 'Data', Arr::get($json, 'data', []));
            if (is_string($items)) {
                $decoded = json_decode($items, true);
                $items = is_array($decoded) ? $decoded : [];
            }
            if (!is_array($items)) {
                $items = [];
            }

            return [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $body,
                'items' => $items,
            ];
        };

        $withCode = $callTemplates(true);
        $withoutCode = $callTemplates(false);

        $merged = [];
        foreach (array_merge($withCode['items'], $withoutCode['items']) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $key = (string) (
                data_get($item, 'InvoiceTemplateID')
                ?? data_get($item, 'invoiceTemplateID')
                ?? data_get($item, 'IPTemplateID')
                ?? data_get($item, 'ipTemplateID')
                ?? Str::uuid()->toString()
            );
            $merged[$key] = $item;
        }

        return [
            'status' => ($withCode['status'] === 200 || $withoutCode['status'] === 200) ? 200 : ($withCode['status'] ?: $withoutCode['status']),
            'successful' => !empty($withCode['successful']) || !empty($withoutCode['successful']),
            'body' => json_encode([
                'with_code' => $withCode['body'],
                'without_code' => $withoutCode['body'],
            ], JSON_UNESCAPED_UNICODE),
            'items' => array_values($merged),
        ];
    }

    private function readSettings(): array
    {
        $defaults = [
            'base_url' => config('services.meinvoice.base_url', 'https://testapi.meinvoice.vn/api/integration'),
            'app_id' => config('services.meinvoice.app_id', ''),
            'tax_code' => config('services.meinvoice.tax_code', ''),
            'username' => config('services.meinvoice.username', ''),
            'password' => config('services.meinvoice.password', ''),
            'sign_type' => config('services.meinvoice.sign_type', 2),
            'certificate_sn' => config('services.meinvoice.certificate_sn', ''),
            'inv_series' => config('services.meinvoice.inv_series', ''),
            'invoice_template_id' => config('services.meinvoice.invoice_template_id', ''),
            'timeout' => config('services.meinvoice.timeout', 30),
        ];

        if (!File::exists($this->settingsPath)) {
            return $defaults;
        }

        $json = json_decode((string) File::get($this->settingsPath), true);
        return array_merge($defaults, is_array($json) ? $json : []);
    }
}
