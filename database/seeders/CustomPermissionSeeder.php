<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class CustomPermissionSeeder extends Seeder
{
    const PERMISSIONS = [
        'terminate_internship',
        'create_custom_location',
        'create_custom_task',
        'create_custom_grade',
        'create_custom_leave_type',

        'view_all_grade',
        'view_all_location',
        'view_all_task',
        'view_all_employee',
        'view_all_employee_product',
        'view_all_invoice',
        'view_all_product',
        'view_all_supplier',
        'view_all_intern',
        'view_all_user',
        'view_all_role',
        'view_all_guest',
        'view_all_leave_type',
        'view_all_leave',
        'approve_leave',
        'reject_leave',
        'view_all_training',
        'view_basic_salary',
        'view_all_salary_deductions',
        'view_all_holidays',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
