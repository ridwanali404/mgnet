<?php

namespace App\Http\Controllers;

use Auth;
use File;
use Mail;
use Image;
use Session;
use Storage;
use Carbon\Carbon;
use App\Models\Pin;
use App\Models\Bank;
use App\Models\City;
use App\Models\User;
use App\Models\Member;
use App\Traits\Helper;

use App\Models\UserPin;
use App\Models\Province;

use App\Models\Subdistrict;
use Illuminate\Http\Request;
use App\Models\Imports\UserImport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('created_at')->get();
        return view('marketplace.admin.user', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $banks = Bank::all();
        $compact = ['banks'];
        if (auth()->user()->type == 'admin') {
            $pins = Pin::where('type', 'premium')->get();
            $compact[] = 'pins';
        } else {
            $bsm = ['BSM SILVER', 'BSM GOLD', 'BSM PLATINUM', 'BSM SILVER UP', 'BSM GOLD UP', 'BSM PLATINUM UP'];
            $userPins = auth()->user()->usableUserPins()->whereIn('name', $bsm)->get();
            $compact[] = 'userPins';
        }
        return view('register', compact($compact));
    }

    public function storeMember(Request $request)
    {
        // check username
        $is_taken_username = User::where('username', $request->username)->first();
        if ($is_taken_username) {
            Session::flash('fail', 'Username sudah digunakan');
            return back()->withInput();
        }
        $r = $request->all();
        $user = auth()->user();
        // $password = substr(str_shuffle(strtolower(sha1(rand() . time() . "crindonesia"))), 0, 8);
        if ($r['is_clone'] == 'yes') {
            $r['name'] = $user->name;
            $r['email'] = $user->email;
            $r['phone'] = $user->phone;
            $r['bank_id'] = $user->bank_id;
            $r['bank_account'] = $user->bank_account;
            $r['bank_as'] = $user->bank_as;
            $r['ktp'] = $user->ktp;
            $r['npwp'] = $user->npwp;
            $r['password'] = $user->password;
        } else {
            $password = $r['password'];
            $r['password'] = bcrypt($r['password']);
        }
        // Set upline_id: jika kosong, gunakan sponsor_id
        $sponsor_id = $r['sponsor_id'] ?? auth()->id();
        $upline_id = $r['upline_id'] ?? $sponsor_id;
        
        // Tentukan placement_side: sponsor pertama di kiri, sponsor kedua di kanan
        $sponsor = User::find($sponsor_id);
        $placement_side = null;
        if ($sponsor) {
            $sponsorCount = $sponsor->sponsors()->count();
            // Sponsor pertama -> left, sponsor kedua -> right, seterusnya bergantian
            if ($sponsorCount == 0) {
                $placement_side = 'left';
            } else if ($sponsorCount == 1) {
                $placement_side = 'right';
            } else {
                // Untuk sponsor ketiga dan seterusnya, cek apakah ada yang di kiri atau kanan
                $leftCount = $sponsor->sponsors()->where('placement_side', 'left')->count();
                $rightCount = $sponsor->sponsors()->where('placement_side', 'right')->count();
                // Tempatkan di sisi yang lebih sedikit
                $placement_side = ($leftCount <= $rightCount) ? 'left' : 'right';
            }
        }
        
        $createdUser = User::create([
            'name' => $r['name'],
            'email' => $r['email'],
            'username' => $r['username'],
            'phone' => $r['phone'],
            'bank_id' => $r['bank_id'],
            'bank_account' => $r['bank_account'],
            'bank_as' => $r['bank_as'],
            'ktp' => $r['ktp'],
            'npwp' => $r['npwp'],
            'password' => $r['password'],
            'sponsor_id' => $sponsor_id,
            'upline_id' => $upline_id,
            'placement_side' => $placement_side,
        ]);
        if ($user->type == 'admin') {
            $pin = Pin::find($r['pin_id']);
            $userPin = UserPin::create([
                'buyer_id' => $createdUser->id,
                'user_id' => $createdUser->id,
                'pin_id' => $pin->id,
                'code' => strtoupper(str_random(6)),
                'name' => $pin->name,
                'price' => $pin->price,
                'level' => $pin->level,
            ]);
            Helper::pinHistory($userPin);
        } else {
            $userPin = UserPin::find($r['user_pin_id']);
            $userPin->update([
                'user_id' => $createdUser->id,
            ]);
        }
        Helper::upgrade($userPin);
        if (env('APP_ENV') == 'production' && $r['is_clone'] == 'no') {
            // send email
            $to_name = $createdUser->name;
            $to_email = $createdUser->email;
            $data = array(
                'user' => $createdUser,
                'password' => $password,
            );
            Mail::send('mail.register', $data, function ($message) use ($to_name, $to_email) {
                $message->to($to_email, $to_name)->subject('Registrasi');
                $message->from('bsmcrid@gmail.com', 'MG Network');
            });
        }
        Session::flash('success', 'Registrasi berhasil');
        return redirect('referral');
    }

    function generateUser()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // check sponsor
        if ($request->sponsor) {
            $sponsor = User::where('username', $request->sponsor)->first();
            if ($sponsor) {
                $sponsor_id = $sponsor->id;
            } else {
                Session::flash('fail', 'Sponsor tidak ditemukan');
                return back()->withInput();
            }
        } else {
            $sponsor_id = User::where('type', 'admin')->value('id');
        }

        // check username
        $is_taken_username = User::where('username', $request->username)->first();
        if ($is_taken_username) {
            Session::flash('fail', 'Username sudah digunakan');
            return back()->withInput();
        }

        // email
        $is_taken_email = User::where('email', $request->email)->first();
        if ($is_taken_email) {
            Session::flash('fail', 'Email sudah digunakan');
            return back()->withInput();
        }

        // phone number
        $is_taken_phone = User::where('phone', $request->phone)->first();
        if ($is_taken_phone) {
            Session::flash('fail', 'Nomor HP sudah digunakan');
            return back()->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'image' => $request->image ? $this->uploadImage($request->image) : null,
            'sponsor_id' => $sponsor_id,
        ]);
        if ($user) {
            // create pin free member
            $pin = \App\Models\Pin::where('type', 'free')->first();
            $user->userPin()->create([
                'pin_id' => $pin->id,
                'name' => $pin->name,
                'price' => $pin->price,
                'level' => $pin->level,
                'code' => strtoupper(str_random(6)),
                'is_used' => true,
            ]);

            Auth::loginUsingId($user->id);

            // send email
            $address = \App\Models\Address::find($request->address_id);
            $to_name = $user->name;
            $to_email = $user->email;
            $data = array(
                'user' => $user,
                'password' => $request->password,
            );
            if (env('MAIL_USERNAME')) {
                Mail::send('mail.register', $data, function ($message) use ($to_name, $to_email) {
                    $message->to($to_email, $to_name)->subject('Registrasi');
                    $message->from('bsmcrid@gmail.com', 'MG Network');
                });
            }

            Session::flash('success', 'Registered');
            return redirect('/');
        } else
            Session::flash('fail', 'Error while saving');
        return back()->withInput();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('profile', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        if (Auth::id() != $user->id && Auth::user()->type != 'admin') {
            return back();
        }
        $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
        ]);
        $rr = $request->all();
        if ($rr['password'] == null) {
            unset($rr['password']);
        } else {
            $rr['password'] = bcrypt($rr['password']);
        }
        if ($request->image) {
            $rr['image'] = $this->uploadImage($request->image);
        }
        $user->update($rr);
        Session::flash('success', 'Update berhasil');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $is_deleted = $user->delete();
        if ($is_deleted)
            Session::flash('success', 'Deleted');
        else
            Session::flash('fail', 'Error while deleting');
        return back();
    }

    public function referral()
    {
        if (request()->get('username')) {
            $user = User::where('username', request()->get('username'))->first();
        } else {
            $user = Auth::user();
        }
        return view('referral', compact('user'));
    }

    public function uploadImage($image)
    {
        $path = 'storage/upload/user/';
        File::exists($path) or File::makeDirectory($path, 0777, true, true);
        if (!is_string($image)) {
            $imageName = date('Ymd') . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $path . $imageName;
            $img = Image::make($image->getRealPath());
        } else {
            // every url will be formatted to jpg
            $imageName = date('Ymd') . time() . '.jpg';
            $imagePath = $path . $imageName;
            $img = Image::make($image);
        }
        // resize the image to a width of 300 and constraint aspect ratio (auto height)
        $img->resize(600, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        // resize the image to a height of 200 and constraint aspect ratio (auto width)
        $img->resize(null, 400, function ($constraint) {
            $constraint->aspectRatio();
        });
        // prevent possible upsizing
        $img->resize(null, 500, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        // save
        $img->save($imagePath, 60);
        return $imagePath;
    }

    public function register()
    {
        $provinces = Province::orderBy('province')->get();
        $cities = City::orderBy('city_name')->get();
        $subdistricts = Subdistrict::orderBy('subdistrict_name')->get();
        return view('shop.register', compact('provinces', 'cities', 'subdistricts'));
    }


    public function province()
    {
        return Province::all();
    }

    public function city($province_id)
    {
        return City::where('province_id', $province_id)->get();
    }

    public function subdistrict($city_id)
    {
        return Subdistrict::where('city_id', $city_id)->get();
    }

    public function account()
    {
        $user = Auth::user();
        $provinces = Province::orderBy('province')->get();
        $cities = City::orderBy('city_name')->get();
        $subdistricts = Subdistrict::orderBy('subdistrict_name')->get();
        return view('shop.account', compact('user', 'provinces', 'cities', 'subdistricts'));
    }

    public function filter()
    {
        $query = User::select('id', 'username as text')->where('type', 'member');
        
        // Jika ada sponsor_id, hanya tampilkan downline dari sponsor tersebut
        if (request()->has('sponsor_id') && request()->get('sponsor_id')) {
            $sponsor = User::find(request()->get('sponsor_id'));
            if ($sponsor) {
                // Ambil semua ID downline dari sponsor (termasuk sponsor itu sendiri)
                $downlineIds = $sponsor->descendants()->pluck('id')->toArray();
                // Tambahkan sponsor itu sendiri ke dalam list
                $downlineIds[] = $sponsor->id;
                $query->whereIn('id', $downlineIds);
            }
        }
        
        if (request()->has('search') && request()->get('search')) {
            $query->where('username', 'like', request()->get('search') . '%');
        }
        
        return $query->paginate(10);
    }

    public function filterMember()
    {
        return User::select('id', 'username as text')->where('type', 'member')->where('username', 'like', request()->get('search') . '%')->paginate(10);
        // return Member::select('member_id as id', 'member_username as text')->where('member_phase_name', '!=', 'User Free')->where('member_username', 'like', request()->get('search') . '%')->paginate(10);
    }

    public function stockist()
    {
        if (request()->ajax()) {
            return User::select('id', 'username as text')->where('type', 'member')->where(function ($q) {
                $q->where('is_stockist', true)->orWhere('is_master_stockist', true);
            })->where('username', 'like', request()->get('search') . '%')->paginate(10);
        }
        $users = User::where(function ($q) {
            $q->where('is_stockist', true)->orWhere('is_master_stockist', true);
        })->orderBy('username')->get();
        return view('stockist', compact('users'));
    }

    public function stockistStore(Request $request)
    {
        $user = User::find($request->user_id);
        if ($user) {
            $user->update([
                'is_stockist' => true,
                'is_master_stockist' => false,
            ]);
        }
        Session::flash('success', 'Stokis berhasil dibuat');
        return back();
    }

    public function masterStockistStore(Request $request)
    {
        $user = User::find($request->user_id);
        if ($user) {
            $user->update([
                'is_stockist' => false,
                'is_master_stockist' => true,
            ]);
        }
        Session::flash('success', 'Master Stokis berhasil dibuat');
        return back();
    }

    public function stockistDestroy(User $user)
    {
        $user->update([
            'is_stockist' => false,
            'is_master_stockist' => false,
        ]);
        Session::flash('success', 'Stokis berhasil dibekukan');
        return back();
    }

    public function setStockist(Request $request, User $user)
    {
        $user->update([
            'is_stockist' => true,
            'is_master_stockist' => false,
        ]);
        Session::flash('success', $user->username . ' berhasil dibuat menjadi Stokis');
        return back();
    }

    public function setMasterStockist(Request $request, User $user)
    {
        $user->update([
            'is_stockist' => false,
            'is_master_stockist' => true,
        ]);
        Session::flash('success', $user->username . ' berhasil dibuat menjadi Master Stokis');
        return back();
    }

    public function upgrade(Request $request, User $user)
    {
        // update userPin
        $userPin = \App\Models\UserPin::find($request->pin_id);
        if (!$userPin) {
            Session::flash('fail', 'PIN upgrade belum dipilih');
            return back();
        }
        if ($userPin->user_id) {
            Session::flash('fail', 'PIN upgrade sudah digunakan');
            return back();
        }
        $userPin->update([
            'user_id' => $user->id,
        ]);
        Helper::upgrade($userPin);
        Session::flash('success', 'Upgrade berhasil');
        return back();
    }

    public function import()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        foreach (User::all() as $a) {
            $a->delete();
        }

        if (!User::find(2)) {
            $admin = User::create([
                'id' => 2,
                'image' => 0,
                'name' => 'Administrator',
                'email' => 'admin@merahputihcoffee.com',
                'password' => bcrypt('testing'),
                'type' => 'admin',
                'username' => 'jarajan',
                'phone' => '85201031214',
                'bank_id' => 1,
                'bank_account' => '0891-01-033236-53-2',
                'bank_as' => 'PT JARAJAN GLOBAL INTERNASIONAL',
            ]);

            $admin->addresses()->create([
                'name' => 'Office',
                'address' => 'Mantrijeron',
                'province_id' => 5,
                'city_id' => 501,
                'subdistrict_id' => 6988,
                'is_active' => true,
            ]);

            $admin->userPin()->create([
                'pin_id' => \App\Models\Pin::where('name', 'Free Member')->value('id'),
                'name' => 'Free Member',
                'code' => strtoupper(str_random(6)),
                'price' => 0,
            ]);
        }

        Excel::import(new UserImport, public_path('import/users.xlsx'));

        $users = User::all();
        foreach ($users as $a) {
            $a->update([
                'image' => null,
            ]);
        }
        return 'done!';
    }

    public function users()
    {
        $users = User::query();
        if (request()->username) {
            $users = $users->where(function ($q) {
                $q->where('username', 'like', request()->username . '%')
                    ->orWhere('name', 'like', request()->username . '%');
            });
        }
        if (request()->rank) {
            $users = $users->where('phase', request()->rank);
        }
        if (false) {
            if (request()->rank) {
                if (request()->rank == 'Agen') {
                    $agen_ids = [];
                    foreach (User::whereHas('premiumUserPin')->get() as $user) {
                        if ($user->rank == 'Agen') {
                            array_push($agen_ids, $user->id);
                        }
                    }
                    $users = $users->whereIn('id', $agen_ids);
                    // ->has('premiumSponsors', '>=', 10)->has('agenSponsors', '<', 10);
                    // ->whereHas('premiumSponsors', function ($q_premiumSponsors) {
                    //     $q_premiumSponsors->havingRaw('COUNT(*) >= 10');
                    // }, '>=', 10);
                    // ->whereHas('sponsors', function ($q_sponsors) {
                    //     $q_sponsors->whereHas('premiumSponsors', function ($q_premiumSponsors) {
                    //         $q_premiumSponsors->havingRaw('COUNT(*) >= 10');
                    //     });
                    // });
                } elseif (request()->rank == 'Distributor') {
                    // $users = $users->has('agenSponsors', '>=', 10);
                    $distributor_ids = [];
                    foreach (User::whereHas('premiumUserPin')->get() as $user) {
                        if ($user->rank == 'Distributor') {
                            array_push($distributor_ids, $user->id);
                        }
                    }
                    $users = $users->whereIn('id', $distributor_ids);
                }
            }
        }
        $users = $users->orderBy('username')->paginate(20);
        return view('users', compact('users'));
    }

    public function post(Request $request)
    {
        if (!$request->member_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_id is required',
            ], 400);
        }
        $idCount = User::where('member_id', $request->member_id)->count();
        if ($idCount) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_id is already in use',
            ], 400);
        }
        if (!$request->member_username) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_username is required',
            ], 400);
        }
        $usernameCount = User::where('username', $request->member_username)->count();
        if ($usernameCount) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_username is already in use',
            ], 400);
        }
        if (!$request->member_email) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_email is required',
            ], 400);
        }
        if (!$request->member_password) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_password is required',
            ], 400);
        }
        if (!$request->member_phase_name) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_phase_name is required',
            ], 400);
        }
        if (!$request->member_sponsor_member_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_sponsor_member_id is required',
            ], 400);
        }
        // add to user table
        $user = User::create([
            'name' => $request->member_name,
            'email' => $request->member_email,
            'password' => $request->member_password,
            'type' => 'member',
            'username' => $request->member_username,
            'phone' => $request->member_phone,
            'ktp' => $request->member_identity_number,
            'npwp' => $request->member_npwp,
            'member_id' => $request->member_id,
            'phase' => $request->member_phase_name,

            'updated_at' => $request->member_datetime,
            'created_at' => $request->member_datetime,
            'sponsor_id' => User::where('member_id', $request->member_sponsor_member_id)->value('id'),
            'is_stockist' => $request->is_stockist ?? 0,
        ]);
        // add pin for member
        if ($request->member_phase_name == 'User Free') {
            $user->userPin()->create([
                'pin_id' => \App\Models\Pin::where('name', 'Free Member')->value('id'),
                'name' => 'Free Member',
                'code' => strtoupper(str_random(6)),
                'price' => 0,
            ]);
        } else {
            $user->userPin()->create([
                'pin_id' => \App\Models\Pin::where('name', 'CR Reseller')->value('id'),
                'name' => 'CR Reseller',
                'code' => strtoupper(str_random(6)),
                'price' => 150000,
            ]);
        }
        // add bank account
        if ($request->member_bank_name) {
            $bank = \App\Models\Bank::where('name', 'like', '%' . $request->member_bank_name . '%')->first();
            if (!$bank) {
                $bank = \App\Models\Bank::create([
                    'name' => strtoupper($request->member_bank_name),
                ]);
            }
            $user->update([
                'bank_id' => $bank->id,
                'bank_account' => $request->member_bank_account_number,
                'bank_as' => $request->member_bank_account_name,
            ]);
        }
        // add address
        if ($request->member_province_id && $request->member_city_id && $request->member_subdistrict_id) {
            $user->addresses()->create([
                'is_active' => true,
                'name' => 'Rumah',
                'email' => $request->member_email,
                'address' => $request->member_address,
                'phone' => $request->member_phone,
                'province_id' => $request->member_province_id,
                'city_id' => $request->member_city_id,
                'subdistrict_id' => $request->member_subdistrict_id,
            ]);
        }
        return $user;
    }

    public function memberUpdate(Request $request)
    {
        if (!$request->member_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_id is required',
            ], 400);
        }
        $user = User::where('member_id', $request->member_id)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_id not found',
            ], 500);
        }
        // add to user table
        $user->update([
            'name' => $request->member_name ?? $user->name,
            'email' => $request->member_email ?? $user->email,
            'password' => $request->member_password ?? bcrypt($user->password),
            // 'type' => 'member',
            'username' => $request->member_username ?? $user->username,
            'phone' => $request->member_phone ?? $user->phone,
            'ktp' => $request->member_identity_number ?? $user->ktp,
            'npwp' => $request->member_npwp ?? $user->npwp,
            // 'member_id' => $request->member_id,
            'phase' => $request->member_phase_name ?? $user->phase,
            // 'is_stockist' => $request->is_stockist,
        ]);
        // add address
        if ($request->member_province_id && $request->member_city_id && $request->member_subdistrict_id) {
            if ($user->address) {
                $user->address()->update([
                    'is_active' => true,
                    'name' => 'Rumah',
                    'email' => $request->member_email ?? $user->address->email,
                    'address' => $request->member_address ?? $user->address->address,
                    'phone' => $request->member_phone ?? $user->address->phone,
                    'province_id' => $request->member_province_id ?? $user->address->province_id,
                    'city_id' => $request->member_city_id ?? $user->address->city_id,
                    'subdistrict_id' => $request->member_subdistrict_id ?? $user->address->subdistrict_id,
                ]);
            } else {
                $user->addresses()->create([
                    'is_active' => true,
                    'name' => 'Rumah',
                    'email' => $request->member_email,
                    'address' => $request->member_address,
                    'phone' => $request->member_phone,
                    'province_id' => $request->member_province_id,
                    'city_id' => $request->member_city_id,
                    'subdistrict_id' => $request->member_subdistrict_id,
                ]);
            }
        }
        // add bank account
        if ($request->member_bank_name && $request->member_bank_account_number && $request->member_bank_account_name) {
            $bank = \App\Models\Bank::where('name', 'like', '%' . $request->member_bank_name . '%')->first();
            if (!$bank) {
                $bank = \App\Models\Bank::create([
                    'name' => strtoupper($request->member_bank_name),
                ]);
            }
            $user->update([
                'bank_id' => $bank->id,
                'bank_account' => $request->member_bank_account_number,
                'bank_as' => $request->member_bank_account_name,
            ]);
        }
        return $user;
    }

    public function stockistArea()
    {
        $users = User::where('is_master_stockist', true)->orderBy('username')->get();
        return view('stockist_area', compact('users'));
    }

    public function areaMasterStockist(Request $request, User $user)
    {
        if ($request->cities) {
            $user->userCities()->delete();
            $userCities = collect($request->cities)->map(function ($city_id) use ($user) {
                $data['user_id'] = $user->id;
                $data['city_id'] = $city_id;
                return $data;
            });
            $user->userCities()->insert($userCities->toArray());
        } else {
            $user->userCities()->delete();
        }
        Session::flash('success', 'Area master stokis berhasil disimpan');
        return back();
    }

    public function admin()
    {
        $users = User::where('type', 'cradmin')->orderBy('username')->get();
        return view('admin', compact('users'));
    }

    public function adminStore(Request $request)
    {
        // check username
        $is_taken_username = User::where('username', $request->username)->count();
        if ($is_taken_username) {
            Session::flash('fail', 'Username sudah digunakan');
            return back()->withInput();
        }

        $request->request->add([
            'type' => 'cradmin',
        ]);
        $request->merge([
            'password' => bcrypt($request->password),
        ]);
        User::create($request->all());
        Session::flash('success', 'Admin CR berhasil disimpan');
        return back();
    }

    public function adminUpdate(Request $request, User $user)
    {
        // check username
        $is_taken_username = User::where('id', '!=', $user->id)->where('username', $request->username)->count();
        if ($is_taken_username) {
            Session::flash('fail', 'Username sudah digunakan');
            return back()->withInput();
        }

        if (!$request->password) {
            $request->request->remove('password');
        } else {
            $request->merge([
                'password' => bcrypt($request->password),
            ]);
        }
        if (!$request->roles) {
            $request->merge([
                'roles' => null,
            ]);
        }
        $user->update($request->all());
        Session::flash('success', 'Admin CR berhasil dihapus');
        return back();
    }

    public function adminDestroy(User $user)
    {
        $user->delete();
        Session::flash('success', 'Admin CR berhasil dihapus');
        return back();
    }

    public function addresses(Member $member)
    {
        return $member->user->addresses;
    }

    public function potency(User $user) // aka bonus unilevel ro
    {
        $month = request()->month ?? date('Y-m');
        if (Helper::isClosing($month)) {
            return number_format($user->monthlyBonuses($month)->where('type', 'Bonus Unilevel RO')->sum('amount'), 0, ',', '.');
        }
        return number_format($user->monthlyBonuses($month)->sum('amount') + $user->monthlyPotency($month)->sum('amount'), 0, ',', '.');
    }

    public function potencyList(User $user)
    {
        $month = request()->month ?? date('Y-m');
        if (Helper::isClosing($month)) {
            $potency = $user->monthlyBonuses($month)->where('amount', '>', 0)->where('type', 'Bonus Unilevel RO')->get()->collect()->map(function ($a) {
                return $a;
            });
        } else {
            $potency = $user->monthlyPotency($month);
        }
        return [
            'data' => $potency,
        ];
    }
}
