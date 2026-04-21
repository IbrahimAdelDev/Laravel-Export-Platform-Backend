<?php

namespace App\Services\Admin\DataImport;

use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;

class PreviewImportService
{
    public function execute($file)
    {
        // save file with profisional name and timestamp to avoid conflicts
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . now()->format('Y_m_d_His') . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('data_imports/temp', $fileName, 'local');

        $absolutePath = Storage::disk('local')->path($path);

        if (!file_exists($absolutePath) || !is_readable($absolutePath)) {
            throw new \Exception("The file cannot be read from the server.");
        }

        $fastExcel = new FastExcel();
        $sheets = $fastExcel->withoutHeaders()->importSheets($absolutePath);
        
        $previewData = [];
        // The library returns the index starting from 0, while the frontend displays it starting from 1
        foreach ($sheets as $index => $sheetData) {
            $previewData[] = [
                'sheet_name' => 'Sheet ' . ($index + 1), // ده اللي هيرجع من الفرونت بعدين
                'sample_rows' => collect($sheetData)->take(7)->toArray(),
            ];
        }

        return [
            'file_path' => $path, // Relative Path
            'preview_data' => $previewData,
        ];
    }
}