<?php

namespace App\Http\Controllers\Admin\DataImport;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use Illuminate\Support\Facades\Bus;

class ImportProgressController extends Controller
{
    public function __invoke($id)
    {
        // 1. نجيب سجل الاستيراد من الداتابيز بتاعتنا
        $importBatch = ImportBatch::findOrFail($id);

        $progressPercentage = 0;
        $laravelBatchDetails = null;

        // 2. لو ليه Job Batch ID، نسأل لارافيل عن حالته في الطابور
        if ($importBatch->job_batch_id) {
            $laravelBatch = Bus::findBatch($importBatch->job_batch_id);
            
            if ($laravelBatch) {
                $progressPercentage = $laravelBatch->progress(); // بترجع رقم من 0 لـ 100
                
                $laravelBatchDetails = [
                    'total_jobs'   => $laravelBatch->totalJobs,
                    'pending_jobs' => $laravelBatch->pendingJobs,
                    'failed_jobs'  => $laravelBatch->failedJobs,
                ];

                // 3. تحديث حالة السجل بتاعنا لو خلص
                if ($laravelBatch->finished() && $importBatch->status !== 'completed') {
                    $importBatch->update(['status' => 'completed']);
                } elseif ($laravelBatch->hasFailures() && $importBatch->status === 'pending') {
                    // ممكن نخليها failed أو partial_completed حسب البزنس بتاعك
                    $importBatch->update(['status' => 'has_errors']);
                }
            }
        }

        // 4. نرجع الرد للفرونت إند
        return response()->json([
            'success' => true,
            'data' => [
                'import_id'           => $importBatch->id,
                'file_name'           => $importBatch->file_name,
                'status'              => $importBatch->status,
                'processed_rows'      => $importBatch->processed_rows, // اللي إحنا بنعملها increment في الووركر
                'progress_percentage' => $progressPercentage,          // نسبة مئوية للـ Progress Bar
                'queue_details'       => $laravelBatchDetails
            ]
        ]);
    }
}