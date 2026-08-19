<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function getUsers(array $filters, int $currentAdminId): LengthAwarePaginator
    {
        $query = User::latest()
            // 1. استثناء الإدمن اللي بيعمل الريكويست حالياً
            ->where('id', '!=', $currentAdminId);

        // 2. استثناء أي يوزر معاه صلاحية إدارة
        // (بافتراض أن عمود role موجود مباشرة في جدول users)
        $query->whereNotIn('role', ['admin', 'super_admin']);
        
        /* 
        💡 ملاحظة: لو بتستخدم مكتبة Spatie لإدارة الصلاحيات، 
        امسح السطر اللي فوق واستخدم الكود ده بداله:
        $query->whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin']);
        });
        */

        // 3. فلتر البحث (لو مفيش قيمة مبعوتة للبحث، هيتجاهل الشرط ويرجع كل اليوزرز الباقيين)
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }
}