<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'username' => 'trial1',
                'email' => 'trial1@aturaja.me',            
                'password' => app('hash')->make('Rahasia1'),
                'fullname' => 'User Trial 1',
                'phone_number' => '061123456789',
                'email_verified_at' => date('Y-m-d H:i:s', time()),
            ],
            [
                'username' => 'trial2',
                'email' => 'trial2@aturaja.me',            
                'password' => app('hash')->make('Rahasia2'),
                'fullname' => 'User Trial 2',
                'phone_number' => '062123456789',
                'email_verified_at' => date('Y-m-d H:i:s', time()),
            ]
            ];
        
        foreach($users as $key => $user){
            User::create($user);
        }
    }
}
