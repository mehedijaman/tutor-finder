<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Database\Seeder;

class BlogDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = BlogCategory::factory()
            ->count(10)
            ->create();

        $tags = BlogTag::factory()
            ->count(20)
            ->create();

        $posts = BlogPost::factory()
            ->count(100)
            ->create();

        foreach ($posts as $post) {
            $post->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
            $post->tags()->attach(
                $tags->random(rand(2, 5))->pluck('id')->toArray()
            );
        }
    }
}
