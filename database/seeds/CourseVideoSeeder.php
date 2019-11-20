<?php

use App\Models\Course;
use App\Models\Video;
use Illuminate\Database\Seeder;

class CourseVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Video::class,50)->create();
    }
}
