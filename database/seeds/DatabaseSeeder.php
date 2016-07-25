<?php

use Illuminate\Database\Seeder;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        User::create([
            'name'  => 'Kyle Dinh',
            'email' => 'kyledinh.vn@gmail.com',
            'password' => app('hash')->make('123456'),
            'is_admin' => 1
        ]);
    }
}
