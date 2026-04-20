<?php

namespace App\Repositories;

use App\Helpers\SanitizerHelper;
use App\Models\Payment;

class PaymentRepository
{
    /**
     * Cria um novo pagamento
     */
    public function create(array $data): Payment
    {
        $sanitizedData = SanitizerHelper::sanitize($data);
        return Payment::create($sanitizedData);
    }

    /**
     * Atualiza pagamento
     */
    public function update(int $paymentId, array $data): bool
    {
        $payment = Payment::find($paymentId);
        if (!$payment) {
            return false;
        }

        $sanitizedData = SanitizerHelper::sanitize($data);
        return $payment->update($sanitizedData);
    }

    /**
     * Encontra pagamento por ID
     */
    public function findById(int $id): ?Payment
    {
        return Payment::with('sale')->find($id);
    }

    /**
     * Encontra pagamento por payment_id do Mercado Pago
     */
    public function findByPaymentId(string $paymentId): ?Payment
    {
        return Payment::where('payment_id', $paymentId)->first();
    }

    /**
     * Encontra pagamento por preference_id
     */
    public function findByPreferenceId(string $preferenceId): ?Payment
    {
        return Payment::where('preference_id', $preferenceId)->first();
    }

    /**
     * Atualiza status do pagamento
     */
    public function updateStatus(int $paymentId, string $status, ?string $statusDetail = null): bool
    {
        $payment = Payment::find($paymentId);
        if (!$payment) {
            return false;
        }

        $updateData = ['status' => $status];
        if ($statusDetail) {
            $updateData['status_detail'] = $statusDetail;
        }

        return $payment->update($updateData);
    }

    /**
     * Atualiza pagamento com dados do Mercado Pago
     */
    public function updateWithMercadoPagoData(int $paymentId, array $mpData): bool
    {
        $payment = Payment::find($paymentId);
        if (!$payment) {
            return false;
        }

        $updateData = [
            'payment_id' => $mpData['id'] ?? $payment->payment_id,
            'status' => $mpData['status'] ?? $payment->status,
            'status_detail' => $mpData['status_detail'] ?? $payment->status_detail,
            'payment_type' => $mpData['payment_type_id'] ?? $payment->payment_type,
            'transaction_amount' => $mpData['transaction_amount'] ?? $payment->transaction_amount,
            'net_amount' => $mpData['transaction_amount'] ?? $payment->net_amount,
            'date_approved' => isset($mpData['date_approved']) ? now()->parse($mpData['date_approved']) : $payment->date_approved,
            'date_created' => isset($mpData['date_created']) ? now()->parse($mpData['date_created']) : $payment->date_created,
            'date_last_updated' => now(),
        ];

        // Dados do cartão
        if (isset($mpData['card'])) {
            $updateData['cardholder_name'] = $mpData['card']['cardholder']['name'] ?? null;
            $updateData['card_last_four'] = $mpData['card']['last_four_digits'] ?? null;
            $updateData['card_first_six'] = $mpData['card']['first_six_digits'] ?? null;
            $updateData['card_brand'] = $mpData['card']['payment_method']['id'] ?? null;
        }

        // Dados PIX
        if (isset($mpData['pix_code'])) {
            $updateData['pix_code'] = $mpData['pix_code'];
            $updateData['pix_expiration_date'] = isset($mpData['date_of_expiration']) ? now()->parse($mpData['date_of_expiration']) : null;
        }

        // Dados do boleto
        if (isset($mpData['barcode'])) {
            $updateData['barcode'] = $mpData['barcode'];
            $updateData['due_date'] = isset($mpData['date_of_expiration']) ? now()->parse($mpData['date_of_expiration']) : null;
        }

        return $payment->update($updateData);
    }

    /**
     * Retorna pagamentos de uma venda
     */
    public function getBySaleId(int $saleId): ?Payment
    {
        return Payment::where('sale_id', $saleId)->first();
    }
}
