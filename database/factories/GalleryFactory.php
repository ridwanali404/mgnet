<?php

use Faker\Generator as Faker;
use Illuminate\Http\UploadedFile;

$factory->define(App\Gallery::class, function (Faker $faker) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $photoFile = new UploadedFile(public_path('img/sample_images/p'.$faker->numberBetween($min = 1, $max = 8).'.jpg'), 'user.png', $finfo->file(public_path('img/sample_images/p'.$faker->numberBetween($min = 1, $max = 8).'.jpg')), File::size(public_path('img/sample_images/p'.$faker->numberBetween($min = 1, $max = 8).'.jpg')), 0, false);
    $path = Storage::disk('public')->putFile('upload/gallery', $photoFile);
    return [
        'name' => 'Gallery '.$faker->numberBetween($min = 1000, $max = 9999),
        'image' => 'storage/'.$path
    ];
});
