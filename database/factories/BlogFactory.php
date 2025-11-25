<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Blog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $imageNumber = fake()->numberBetween(1, 8);
        $imagePath = public_path('img/sample_images/p' . $imageNumber . '.jpg');
        
        if (file_exists($imagePath)) {
            $photoFile = new UploadedFile(
                $imagePath,
                'user.png',
                $finfo->file($imagePath),
                filesize($imagePath),
                0,
                false
            );
            $path = Storage::disk('public')->putFile('upload/blog', $photoFile);
            $image = 'storage/' . $path;
        } else {
            $image = null;
        }

        return [
            'title' => fake()->words(3, true),
            'image' => $image,
            'content' => fake()->paragraphs(20, true),
        ];
    }
}
