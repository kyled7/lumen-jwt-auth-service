<?php

use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'  => 'Kyle Dinh',
            'email' => 'kyledinh.vn@gmail.com',
            'password' => bcrypt('123456'),
            'is_admin' => 1
        ]);
    }
}
