<?php

namespace Database\Factories;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gallery>
 */
class GalleryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Gallery::class;

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
            $path = Storage::disk('public')->putFile('upload/gallery', $photoFile);
            $image = 'storage/' . $path;
        } else {
            $image = null;
        }

        return [
            'name' => 'Gallery ' . fake()->numberBetween(1000, 9999),
            'image' => $image,
        ];
    }
}
