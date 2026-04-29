<?php
namespace App\Interfaces;

interface OrderServiceInterface {
    public function processOrder($productId);
}