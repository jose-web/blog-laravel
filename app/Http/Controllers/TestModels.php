<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;

class TestModels extends Controller
{
    public function index(){

        $posts = Post::all();

        foreach ($posts as $post) {
            echo "<h1>" . $post->title . "</h1>";
            echo "<p style='color:gray'> User: " . $post->user->name . "</p>";
            echo "<p style='color:gray'> Category: " . $post->category->name . "</p>";
            echo "<p>" . $post->content . "</p>";
        }

        die(); // without view
    }
}
