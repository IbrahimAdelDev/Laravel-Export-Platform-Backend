<?php

namespace App\Services\Admin;

use App\Models\ImportBatch; // تأكد إن الموديل ده موجود عندك
use Illuminate\Pagination\LengthAwarePaginator;

class ImportHistoryService
{
    public function getHistory(int $perPage = 15): LengthAwarePaginator
    {
        // بنجيب الأحدث الأول
        return ImportBatch::orderBy('id', 'desc')->paginate($perPage);
    }
}