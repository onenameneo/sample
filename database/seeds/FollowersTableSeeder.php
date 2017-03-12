<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        $user = User::first();

        $cur_id = $user->id;

        //第一个用户关注所有人
        $followers = $users->slice($cur_id);
        $followers_ids = $followers->pluck('id')->toArray();

        $user->follow($followers_ids);


        //所有人关注第一个用户
        foreach ($followers as $follower) {
            $follower->follow($cur_id);
        }
    }
}
