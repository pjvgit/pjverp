<?php

// use App\Permission;
// use App\Role;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert default roles
        $roles = [
            ['name'=>'user', 'guard_name' => 'web'],
            ['name'=>'client', 'guard_name' => 'web'],
            ['name'=>'company', 'guard_name' => 'web'],
            ['name'=>'lead', 'guard_name' => 'web'],
        ];

        foreach($roles as $item) {
            Role::updateOrCreate(['name' => $item['name']], $item);
        }

        // Insert default permissions
        $permissions = [
            ['name'=>'client_add_edit', 'guard_name' => 'web'],
            ['name'=>'client_view', 'guard_name' => 'web'],
            ['name'=>'lead_add_edit', 'guard_name' => 'web'],
            ['name'=>'lead_view', 'guard_name' => 'web'],
            ['name'=>'case_add_edit', 'guard_name' => 'web'],
            ['name'=>'case_view', 'guard_name' => 'web'],
            ['name'=>'event_add_edit', 'guard_name' => 'web'],
            ['name'=>'event_view', 'guard_name' => 'web'],
            ['name'=>'document_add_edit', 'guard_name' => 'web'],
            ['name'=>'document_view', 'guard_name' => 'web'],
            ['name'=>'commenting_add_edit', 'guard_name' => 'web'],
            ['name'=>'commenting_view', 'guard_name' => 'web'],
            ['name'=>'text_messaging_add_edit', 'guard_name' => 'web'],
            ['name'=>'text_messaging_view', 'guard_name' => 'web'],
            ['name'=>'messaging_add_edit', 'guard_name' => 'web'],
            ['name'=>'messaging_view', 'guard_name' => 'web'],
            ['name'=>'access_all_messages', 'guard_name' => 'web'],
            ['name'=>'can_delete_messages', 'guard_name' => 'web'],
            ['name'=>'billing_add_edit', 'guard_name' => 'web'],
            ['name'=>'billing_view', 'guard_name' => 'web'],
            ['name'=>'billing_restrict_time_entry_and_expense', 'guard_name' => 'web'],
            ['name'=>'billing_access_financial_insight', 'guard_name' => 'web'],
            ['name'=>'reporting_entire_firm', 'guard_name' => 'web'],
            ['name'=>'reporting_personal_only', 'guard_name' => 'web'],
            
            ['name'=>'access_all_cases', 'guard_name' => 'web'],
            ['name'=>'access_only_linked_cases', 'guard_name' => 'web'],
            ['name'=>'add_firm_user', 'guard_name' => 'web'],
            ['name'=>'edit_firm_user_permission', 'guard_name' => 'web'], 
            ['name'=>'delete_items', 'guard_name' => 'web'],   // Delete items like events, documents etc
            ['name'=>'empty_trash_permission', 'guard_name' => 'web'],   // Permanetly delete documents from the trash bin
            ['name'=>'edit_import_export_settings', 'guard_name' => 'web'],
            ['name'=>'edit_custom_fields_settings', 'guard_name' => 'web'],
            ['name'=>'edit_custom_fields_settings', 'guard_name' => 'web'],
            ['name'=>'manage_firm_and_billing_settings', 'guard_name' => 'web'],
        ];

        foreach($permissions as $item) {
            Permission::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
