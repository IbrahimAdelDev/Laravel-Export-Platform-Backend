<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportBatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'file_name'      => $this->file_name,
            'status'         => $this->status, 
            'total_rows'     => $this->total_rows,
            'processed_rows' => $this->processed_rows,
            'created_at'     => $this->created_at->format('Y-m-d H:i:s'),
            'completed_at'   => $this->completed_at ? $this->completed_at->format('Y-m-d H:i:s') : null,
        ];
    }
}