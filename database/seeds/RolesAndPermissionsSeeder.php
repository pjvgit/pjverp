<?php

use App\Permission;
use App\Role;
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
        Role::insert([
            [
                'name'=>'user',        
            ],
            [
                'name'=>'client',        
            ],
            [
                'name'=>'company',
            ],
            [
                'name'=>'lead',
            ],
        ]);

        // Insert default permissions
        Permission::insert([
            [
                'name'=>'client_add_edit',        
            ],
            [
                'name'=>'client',        
            ],
            [
                'name'=>'company',
            ],
            [
                'name'=>'lead',
            ],
        ]);
    }
}
