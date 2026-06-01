<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad.guru@sisko.test', 'nip' => '19700101A001', 'phone' => '081234567890', 'address' => 'Jl. Merdeka 1'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.guru@sisko.test', 'nip' => '19700202B002', 'phone' => '081298765432', 'address' => 'Jl. Merdeka 2'],
        ];

        foreach ($teachers as $t) {
            $user = User::firstOrCreate(
                ['email' => $t['email']],
                [
                    'name' => $t['name'],
                    'password' => Hash::make('password'),
                    'role' => Role::Guru,
                    'email_verified_at' => now(),
                ]
            );

            Teacher::firstOrCreate(
                ['user_id' => $user->id],
                ['nip' => $t['nip'], 'phone' => $t['phone'], 'address' => $t['address']]
            );
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        Teacher::factory()->count(8)->create();
    }
}
