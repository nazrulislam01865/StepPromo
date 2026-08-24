<?php

namespace App\DTOs\Orders;

final readonly class OrderCreateData
{
    public function __construct(public array $attributes)
    {
    }

    public static function fromLivewire(array $data, bool $draft): self
    {
        $first = collect($data['jobItems'])->first();

        return new self([
            'order_number' => $data['referenceNumber'],
            'is_repeat_order' => (bool) $data['isRepeatedOrder'],
            'repeat_order_number' => $data['isRepeatedOrder'] ? trim((string) $data['repeatedOrderNumber']) : null,
            'title' => $data['jobTitle'],
            'product' => $first['product'] ?? null,
            'category' => $first['category'] ?? null,
            'quantity' => collect($data['jobItems'])->sum('quantity'),
            'items' => $data['jobItems'],
            'priority' => 'Medium',
            'production_urgency_ids' => [],
            'shipment_urgency_ids' => array_values(array_map('intval', $data['shipmentUrgencyIds'] ?? [])),
            'client_id' => $data['clientId'],
            'workflow_id' => $data['workflowId'],
            'workflow_phase_id' => $data['workflowPhaseId'],
            'owner_id' => $data['ownerId'],
            'coordinator_id' => $data['coordinatorId'] ?: $data['ownerId'],
            'delivery_date' => $data['deliveryDate'] ?: null,
            'estimated_delivery_date' => $data['estimatedDeliveryDate'] ?: null,
            'description' => $data['description'],
            'shipping_address' => blank($data['shippingAddress'] ?? null) ? null : trim((string) $data['shippingAddress']),
            'shipping_phone_country_code' => blank($data['shippingPhoneCountryCode'] ?? null) ? null : trim((string) $data['shippingPhoneCountryCode']),
            'shipping_phone' => blank($data['shippingPhone'] ?? null) ? null : trim((string) $data['shippingPhone']),
            'shipping_postal_code' => blank($data['shippingPostalCode'] ?? null) ? null : trim((string) $data['shippingPostalCode']),
            'shipping_source_address_id' => $data['shippingSourceAddressId'] ?? null,
            'draft' => $draft,
        ]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
