<?php
namespace App\Interfaces;

interface OrderServiceInterface {
    public function processOrder(int $productId): string;

    /**
     * @param array<int> $productIds
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function processOrderChunk(array $productIds, array $context = []): array;
}