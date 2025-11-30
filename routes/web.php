<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('user/import', 'UserController@import');
// Route::get('db2test', 'HomeController@db2test');
// Route::get('helper/sync', 'HelperController@sync');
// Route::get('helper/member', 'HelperController@member');
// Route::get('mysql2', 'HomeController@mysql2');
// Route::get('helper/phase', 'HelperController@phase');
// Route::get('helper/product', 'HelperController@product');
// Route::get('helper/news', 'HelperController@news');
// Route::get('helper/master-stockist', 'HelperController@masterStockist');
// Route::get('helper/is-turbo', 'HelperController@isTurbo');
// Route::get('helper/price-stockist', 'HelperController@priceStockist');
// Route::get('helper/is-hidden', 'HelperController@isHidden');
// Route::get('helper/recipient', 'HelperController@recipient');
// Route::get('helper/roles', 'HelperController@roles');
// Route::get('helper/address', 'HelperController@address');
// Route::get('helper/is-big', 'HelperController@isBig');
// Route::get('helper/profit-sharing-13', 'HelperController@profitSharing13');
// Route::get('helper/poin', 'MonthlyClosingController@poin');
// Route::get('helper/product-month', 'HelperController@productMonth');
// Route::get('helper/big-transaction', 'HelperController@bigTransaction');
// Route::get('helper/pin', 'HelperController@pin');
// Route::get('helper/plana', 'HelperController@plana');
// Route::get('helper/reward', 'HelperController@reward');
// Route::get('helper/pair', 'HelperController@pair');
// Route::get('helper/generasi', 'HelperController@generasi');
// Route::get('helper/syarat-mingguan', 'HelperController@syaratMingguan');
// Route::get('helper/bonus', 'HelperController@bonus');
// Route::get('helper/tokenable', 'HelperController@tokenable');
// Route::get('helper/generasi-up', 'HelperController@generasiUp');
Route::get('helper/level', 'HelperController@level');
Route::get('helper/monoleg', 'HelperController@monoleg');
Route::get('helper/award', 'HelperController@award');

Route::view('privacy', 'privacy')->name('privacy');
Route::get('/', 'HomeController@index')->name('index');
Route::get('potency/profit-sharing-13', 'HomeController@profitSharing13');
Route::get('potency/{user}/list', 'UserController@potencyList');
Route::get('potency/{user}', 'UserController@potency');

Route::get('product', 'ProductController@publicProduct');
Route::get('product/{name}', 'ProductController@publicProductDetail');
Route::get('blog', 'BlogController@publicBlog');
Route::get('blog/{title}', 'BlogController@publicBlogDetail');
Route::get('page/{title}', 'PageController@publicPageDetail');
Route::get('gallery', 'GalleryController@publicGallery');
Route::get('about-us', 'AboutUsController@publicAboutUs');
Route::get('register', 'UserController@register');
Route::post('register', 'UserController@store');

Route::get('province', 'UserController@province');
Route::get('city/{province_id}', 'UserController@city');
Route::get('subdistrict/{city_id}', 'UserController@subdistrict');

// Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::resource('a/dashboard', 'DashboardController');
    Route::resource('a/transaction', 'TransactionController', ['as' => 'a']);
    Route::put('a/transaction/{transaction}/confirm', 'TransactionController@confirm')->name('transaction.confirm');
    Route::put('a/transaction/{transaction}/packed', 'TransactionController@packed')->name('transaction.packed');
    Route::put('a/transaction/{transaction}/shipment', 'TransactionController@shipment')->name('transaction.shipment');
    Route::group(['middleware' => 'admin'], function () {
        Route::post('a/product/big', 'ProductController@storeBig')->name('product.storeBig');
        Route::resource('a/product', 'ProductController');
        Route::resource('a/blog', 'BlogController');
        Route::resource('a/gallery', 'GalleryController');
        // settings
        Route::resource('a/customize', 'CustomizeController');
        Route::resource('a/category', 'CategoryController');
        Route::resource('a/banner', 'BannerController');
        Route::resource('a/about-us', 'AboutUsController');
        Route::resource('a/contact-us', 'ContactUsController');
        Route::resource('a/page', 'PageController');
        Route::resource('a/user', 'UserController');
        Route::put('a/product/{product}/main/{key}', 'ProductController@imageMain')->name('product.image.main');
        Route::delete('a/product/{product}/main/{key}', 'ProductController@imageDelete')->name('product.image.delete');
        Route::get('users', 'UserController@users');
        Route::post('key-value', 'KeyValueController@keyValue');
        Route::resource('bigProduct', 'BigProductController');
    });
    // Route::get('logout', 'AuthController@logout');
    Route::get('account', 'UserController@account');
    Route::get('hirearchy/{username}/{phase}', 'HomeController@phase');
    Route::get('hirearchy', 'HomeController@tree');
    Route::get('plan-a', 'HomeController@planA');

    Route::group(['prefix' => 'admin'], function () {
        Route::get('transaction/general', 'TransactionController@general')->name('admin.transaction.general');
        Route::get('transaction/stockist', 'TransactionController@stockist')->name('admin.transaction.stockist');
        Route::get('transaction/master', 'TransactionController@master')->name('admin.transaction.master');
        Route::get('transaction/official', 'TransactionController@official')->name('admin.transaction.official');
    });
});
Route::resource('address', 'AddressController');
Route::post('cart/check', 'CartController@check');
Route::resource('cart', 'CartController');
Route::get('buy', 'CartController@buy');
Route::post('cost', 'RajaongkirController@cost');
Route::put('transaction/{transaction}/received', 'TransactionController@received')->name('transaction.received');
Route::resource('transaction', 'TransactionController');
Route::post('courier', 'CartController@courier');
Route::post('courier-official', 'RajaongkirController@official');

Route::get('m/home', function () {
    if (Auth::guest()) {
        return redirect('/');
    } else {
        return redirect('home');
    }
});

Route::view('timezone', 'timezone');

Route::group(['middleware' => ['guest']], function () {
    Route::view('login', 'login')->name('login');
    Route::post('login', 'AuthController@login');
    Route::get('logout', function () {
        return redirect(env('CR_ID_REDIRECT') . '/logout');
    });
});

Route::group(['middleware' => ['auth']], function () {
    // Route::post('mode/stockist', 'AuthController@stockist')->name('auth.stockist');
    Route::get('mode/member', 'AuthController@member')->name('auth.member');
    Route::get('mode/stockist', 'AuthController@stockist')->name('auth.stockist');
    Route::get('logout', 'AuthController@logout')->name('logout');
    Route::view('home', 'home')->name('home');
    Route::get('user/create', 'UserController@create')->name('user.create');
    Route::post('user/store', 'UserController@storeMember')->name('user.store');
    Route::get('user/{member}/addresses', 'UserController@addresses');
    Route::put('user/{user}/upgrade', 'UserController@upgrade');
    Route::get('user/{user}/profile', 'UserController@show');
    Route::put('user/{user}/profile', 'UserController@update');
    Route::get('referral', 'UserController@referral');
    Route::view('monoleg', 'monoleg');
    Route::get('tree/dataSource', 'TreeController@dataSource');
    Route::get('tree/children/{user}', 'TreeController@children');
    Route::get('tree/parent/{user}', 'TreeController@parent');
    Route::get('tree/siblings/{user}', 'TreeController@siblings');
    Route::get('tree/families/{user}', 'TreeController@families');
    Route::get('tree/recent-bonuses/{user}', 'TreeController@recentBonuses');
    Route::view('tree', 'tree');
    Route::resource('article', 'ArticleController');

    Route::post('userPin/generate', 'UserPinController@generate')->name('userPin.generate');
    Route::post('userPin/transfer', 'UserPinController@transfer')->name('userPin.transfer');

    Route::get('filter-user', 'UserController@filter');
    Route::get('filter-member', 'UserController@filterMember');
    Route::get('stockist', 'UserController@stockist')->name('stockist.index');
    Route::post('weekly/activate', 'BonusController@weeklyActivate');
    Route::group(['middleware' => 'admin'], function () {
        Route::resource('pin', 'PinController');
        Route::put('weekly/confirm', 'BonusController@weeklyConfirmBulk');
        Route::put('daily/confirm', 'BonusController@dailyConfirmBulk');
        Route::put('daily/{user}/confirm', 'BonusController@dailyConfirm');
        Route::put('daily/{user}/cancel', 'BonusController@dailyCancel');
        Route::put('weekly/{user}/confirm', 'BonusController@weeklyConfirm');
        Route::put('weekly/{user}/cancel', 'BonusController@weeklyCancel');
        Route::put('monthly/confirm', 'BonusController@monthlyConfirmBulk');
        Route::put('monthly/{user}/confirm', 'BonusController@monthlyConfirm');
        Route::put('monthly/{user}/cancel', 'BonusController@monthlyCancel');

        Route::post('stockist', 'UserController@stockistStore')->name('stockist.store');
        Route::put('stockist/{user}/set', 'UserController@setStockist')->name('stockist.set');
        Route::delete('stockist/{user}', 'UserController@stockistDestroy')->name('stockist.destroy');
        Route::post('stockist-master', 'UserController@masterStockistStore')->name('masterStockist.store');
        Route::put('stockist-master/{user}/set', 'UserController@setMasterStockist')->name('masterStockist.set');
        Route::put('stockist-master/{user}/area', 'UserController@areaMasterStockist')->name('masterStockist.area');
        Route::resource('news', 'NewsController');
        Route::resource('monthly-closing', 'MonthlyClosingController');
        Route::post('daily-closing', 'PairController@daily')->name('daily-closing.store');
        Route::get('stockist-area', 'UserController@stockistArea')->name('stockist.area');
        Route::get('admin', 'UserController@admin');
        Route::post('admin', 'UserController@adminStore')->name('admin.store');
        Route::put('admin/{user}', 'UserController@adminUpdate')->name('admin.update');
        Route::delete('admin/{user}', 'UserController@adminDestroy')->name('admin.destroy');
        Route::view('config/daily', 'config.daily')->name('config.daily');
        Route::view('config/monthly', 'config.monthly')->name('config.monthly');
        Route::resource('userPoin', 'UserPoinController');
        Route::get('poin/enable', 'PoinController@enable')->name('poin.enable');
        Route::get('poin/disable', 'PoinController@disable')->name('poin.disable');
        Route::resource('poin', 'PoinController');
        Route::get('qualified/royalty', 'HomeController@royalty');
        Route::get('qualified', 'HomeController@qualified');
        Route::get('daily/pair', 'DailyProfitController@pair');
        Route::get('daily/pair-reward', 'DailyProfitController@pairReward');
        Route::get('pair/enable', 'PairController@enable')->name('pair.enable');
        Route::get('pair/disable', 'PairController@disable')->name('pair.disable');
        Route::resource('pair', 'PairController');
        Route::get('pair-reward/enable', 'PairRewardController@enable')->name('pair-reward.enable');
        Route::get('pair-reward/disable', 'PairRewardController@disable')->name('pair-reward.disable');
        Route::resource('pair-reward', 'PairRewardController');
    });
    Route::resource('userPin', 'UserPinController');
    Route::get('daily2', 'BonusController@daily2');
    Route::get('daily', 'BonusController@daily');
    Route::get('weekly', 'BonusController@weekly');
    Route::get('monthly', 'BonusController@monthly');
    Route::get('official-product', 'ProductController@official');
    Route::get('official-product-big', 'ProductController@officialBig');
    Route::put('official-transaction/{officialTransaction}/confirm', 'OfficialTransactionController@confirm')->name('official-transaction.confirm');
    Route::put('official-transaction/{officialTransaction}/packed', 'OfficialTransactionController@packed')->name('official-transaction.packed');
    Route::put('official-transaction/{officialTransaction}/shipment', 'OfficialTransactionController@shipment')->name('official-transaction.shipment');
    Route::get('official-transaction', 'OfficialTransactionController@index')->name('official-transaction.index');
    Route::post('official-transaction', 'OfficialTransactionController@store')->name('official-transaction.store');
    Route::delete('official-transaction/{officialTransaction}', 'OfficialTransactionController@destroy')->name('official-transaction.destroy');
    Route::resource('official-transaction-stockist', 'OfficialTransactionStockistController', [
        'parameters' => [
            'official-transaction-stockist' => 'officialTransactionStockist',
        ]
    ]);
    Route::resource('reward', 'RewardController');
    Route::resource('user-reward', 'UserRewardController');
    Route::put('user-reward/{reward}/claim', 'UserRewardController@claim')->name('userReward.claim');
    Route::put('user-reward/{userReward}/confirm', 'UserRewardController@confirm')->name('userReward.confirm');
    Route::post('automaintain/claim', 'AutomaintainController@claim')->name('automaintain.claim');
    Route::get('automaintain', 'AutomaintainController@index')->name('automaintain.index');
    Route::patch('topup/{topup}/confirm', 'TopupController@confirm')->name('topup.confirm');
    Route::resource('topup', 'TopupController');
    Route::resource('award', 'AwardController');
    Route::put('userAward/{award}/claim', 'UserAwardController@claim')->name('userAward.claim');
    Route::put('userAward/{userAward}/confirm', 'UserAwardController@confirm')->name('userAward.confirm');
    Route::resource('userAward', 'UserAwardController');
    Route::resource('rank', 'RankController');
    Route::resource('userRank', 'UserRankController');
});

// api
Route::group(array('prefix' => 'apiv2'), function () {
    Route::get('product', 'ProductController@get');
    Route::post('official-transaction-stockist', 'OfficialTransactionController@post');

    Route::post('sanctum/token', 'AuthController@sanctum');
    Route::get('sanctum/login/{token}', 'AuthController@sanctumLogin');
    Route::post('member/update', 'UserController@memberUpdate');
    Route::post('member', 'UserController@post');
    Route::post('member/logout', 'AuthController@memberLogout');
    Route::post('member/basic', 'UserPinController@memberBasic');
});

Route::group(array('prefix' => 'plana'), function () {
    Route::get('dashboard', 'PlanAController@dashboard');
});