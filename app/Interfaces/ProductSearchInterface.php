<?php

namespace App\Interfaces;

interface ProductSearchInterface
{
    public function search(string $keyword, int $limit = 5);
}
