<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */


use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;


$factory->define(User::class, function (Faker $faker) {
    return [
        'avatar'=>$faker->imageUrl(),
        'nick_name'=>$faker->firstName.mt_rand(0,100),
        'mobile'=>$faker->randomElement(['136','188','159']).mt_rand(1000,9999).mt_rand(1000,9999),
        'password'=>bcrypt('12346'),
        'credit1'=>mt_rand(0,10000),
        'credit2'=>mt_rand(0,10000),
        'credit3'=>mt_rand(0,10000),
        'is_active'=>$faker->randomElement([\App\User::ACTIVE_NO, \App\User::ACTIVE_YES]),
        'is_lock'=>$faker->randomElement([\App\User::LOCK_NO,\App\User::LOCK_YES]),
    ];
});
