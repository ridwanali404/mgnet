<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Auth;
use Session;
use File;
use Image;
use Storage;
use Mail;
use App\Models\OfficialTransactionStockist;
use App\Models\OfficialTransaction;
use App\Models\Address;
use DateTime;
use Carbon\Carbon;
use App\Traits\Helper;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->code) {
            $date = request()->code;
            $date = $this->stringInsert($date, ':', 12);
            $date = $this->stringInsert($date, ':', 10);
            $date = $this->stringInsert($date, ' ', 8);
            $date = $this->stringInsert($date, '-', 6);
            $date = $this->stringInsert($date, '-', 4);
            $transactions = Transaction::where('created_at', $date)->latest()->get();
        } else if (request()->transactions) {
            $transactions = Transaction::whereIn('id', json_decode(request()->transactions))->latest()->get();
        } else if (Auth::guest()) {
            $transactions = Transaction::whereNull('id')->latest()->get();
        } else if (request()->type) {
            $transactions = Auth::user()->sponsorTransactions()->latest()->get();
        } else {
            $transactions = Auth::user()->transactions()->latest()->get();
        }
        return view('shop.transaction', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->courier_text == 'Ambil di Kantor') {
            $request->request->add([
                'shipment' => 'Ambil di Kantor',
                'shipment_fee' => 0,
            ]);
        } else if ($request->courier_text == 'COD') {
            $request->request->add([
                'shipment' => 'COD',
                'shipment_fee' => 0,
            ]);
        }
        if (request()->ajax()) {
            if ($request->sponsor) {
                $sponsor = \App\Models\User::where('username', $request->sponsor)->first();
            }
            $transaction = Transaction::create([
                'address_id' => $request->address_id,
                'shipment' => $request->shipment,
                'shipment_fee' => $request->shipment_fee,
                'sponsor_id' => $sponsor->id ?? null,
                'code' => rand(500, 999),
            ]);
            $carts = \App\Models\Cart::whereIn('id', $request->carts)->whereNull('transaction_id')->latest()->get();
        } else {
            if (!$request->courier_text) {
                Session::flash('fail', 'Silahkan pilih kurir sebelum checkout');
                return back();
            }
            $address_id = $request->address_id;
            if ($address_id == 'new') {
                if (!$request->subdistrict_id) {
                    Session::flash('fail', 'Silahkan lengkapi alamat sebelum checkout');
                    return back()->withInput();
                }
                $address = Auth::user()->addresses()->create([
                    'name' => $request->name,
                    'recipient' => $request->recipient,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'province_id' => $request->province_id,
                    'city_id' => $request->city_id,
                    'subdistrict_id' => $request->subdistrict_id,
                    'postal_code' => $request->postal_code,
                ]);
                $address_id = $address->id;
            } else {
                Address::find($address_id)->update([
                    'name' => $request->name,
                    'recipient' => $request->recipient,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'province_id' => $request->province_id,
                    'city_id' => $request->city_id,
                    'subdistrict_id' => $request->subdistrict_id,
                    'postal_code' => $request->postal_code,
                ]);
            }
            $transaction = Auth::user()->transactions()->create([
                'address_id' => $address_id,
                'shipment' => $request->courier_text,
                'shipment_fee' => $request->courier_cost,
                'code' => rand(500, 999),
            ]);
            $carts = Auth::user()->carts()->whereNull('transaction_id')->get();
        }
        $total_price = 0;
        $total_poin = 0;
        $total_weight = 0;
        $total_cashback = 0;
        foreach ($carts as $a) {
            $total_price += $a->price_total;
            $total_poin += $a->poin_total;
            $total_weight += $a->weight_total;
            if (Auth::guest() || (Auth::user() && Auth::user()->member->member_phase_name == 'User Free')) {
                $total_cashback += ($a->price_total - ($a->product->price_member * $a->qty));
            }
            $a->update([
                'transaction_id' => $transaction->id,
            ]);
        }
        $transaction->update([
            'price' => $total_price,
            'poin' => $total_poin,
            'weight' => $total_weight,
            'cashback' => $total_cashback,
            'price_total' => $total_price + $transaction->shipment_fee + $transaction->code,
            'master_stockist_id' => $request->master_stockist_id,
        ]);
        if (request()->ajax()) {
            // send email
            $address = Address::find($request->address_id);
            $to_name = $address->name;
            $to_email = $address->email;
            $data = array(
                'address' => $address,
                'transaction' => $transaction,
            );
            if (env('MAIL_USERNAME')) {
                Mail::send('mail.transaction', $data, function($message) use ($to_name, $to_email) {
                    $message->to($to_email, $to_name)->subject('Transaksi');
                    $message->from('cs.ptbisnissuksesmulia@gmail.com','MG Network');
                });
            }
            return $transaction->id;
        } else {
            $user = Auth::user();
            $data = array(
                'user' => $user,
                'address' => $user->address,
                'transaction' => $transaction,
            );
            $to_name = $user->name;
            $to_email = $user->email;
            if (env('MAIL_USERNAME')) {
                Mail::send('mail.transaction', $data, function($message) use ($to_name, $to_email) {
                    $message->to($to_email, $to_name)->subject('Transaksi');
                    $message->from('cs.ptbisnissuksesmulia@gmail.com','MG Network');
                });
            }

            // add transaction type
            $transactionType = 'general';
            if (session('mode') == 'stockist') {
                if ($user->is_master_stockist) {
                    $transactionType = 'masterstockist';
                } else if ($user->is_stockist) {
                    $transactionType = 'stockist';
                }
            }
            $transaction->update([
                'type' => $transactionType,
            ]);
        }
        if ($transaction) Session::flash('success', 'Saved');
        else Session::flash('error', 'Error while saving');
        return redirect('transaction');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        if($request->receipt) {
            if($transaction->receipt) File::delete(public_path($transaction->receipt));
            $imagePath = $this->uploadImage($request->receipt);
        }
        else $imagePath = $transaction->receipt;
        $is_updated = $transaction->update(array(
            'receipt' => $imagePath
        ));
        if($is_updated) Session::flash('success', 'Updated');
        else Session::flash('error', 'Error while updating');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        if($transaction->receipt) File::delete(public_path($transaction->receipt));
        $is_deleted = $transaction->delete();
        if($is_deleted) Session::flash('success', 'Deleted');
        else Session::flash('error', 'Error while deleting');
        return back();
    }

    public function uploadImage($image) {
        $path = 'storage/upload/transaction/';
        File::exists($path) or File::makeDirectory($path, 0777, true, true);
        if(!is_string($image)) {
            $imageName = date('Ymd').time().'.'.$image->getClientOriginalExtension();
            $imagePath = $path.$imageName;
            $img = Image::make($image->getRealPath());
        }
        else {
            // every url will be formatted to jpg
            $imageName = date('Ymd').time().'.jpg';
            $imagePath = $path.$imageName;
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

    public function confirm(Request $request, Transaction $transaction)
    {
        // check if master stockist transaction
        $is_updated = $transaction->update(array(
            'status' => 'paid'
        ));
        $username = false;
        // check if non member
        if ($transaction->user) {
            $username = $transaction->user->username;
            $sponsor = $transaction->user->sponsor;
            $user = $transaction->user;
        } else if ($transaction->sponsor) {
            $username = $transaction->address->recipient . ' (Non Member)';
            $sponsor = $transaction->sponsor;
            $user = $sponsor;
        }
        // generate string transaction detail
        $carts = '';
        foreach ($transaction->carts as $key => $cart) {
            if ($key + 1 == $transaction->carts()->count()) {
                if ($key == 0) {
                    $carts .= $cart->qty.' '. ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus').' ('.$cart->poin_total.' poin)';
                } else {
                    $carts .= ' dan '.$cart->qty.' '. ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus').' ('.$cart->poin_total.' poin)';
                }
            } else {
                $carts .= $cart->qty.' '. ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus').' ('.$cart->poin_total.' poin)'.', ';
            }
        }
        if ($username) {
            // cashback bonus to sponsor
            if ($user && $transaction->cashback && $transaction->type == 'general') {
                $user->bonuses()->create([
                    'type' => 'Komisi Penjualan',
                    'amount' => $transaction->cashback,
                    'description' => 'Komisi Penjualan dari belanja '.$username.' dengan rincian belanja '.$carts.'.',
                ]);
            }
            
            // Cek dan perpanjang masa aktif jika belanja RO >= 1.7 juta (dalam masa aktif)
            if ($user && $transaction->type == 'general') {
                Helper::checkAndExtendActiveFromRO($user, $transaction->price);
            }
        }
        // add official_transaction_stockists stocks
        if ($transaction->type != 'general') {
            foreach ($transaction->carts as $a) {
                if ($a->product->is_ro) {
                    OfficialTransactionStockist::create([
                        'product_id' => $a->product_id,
                        'user_id' => $a->user_id,
                        'price' => $a->price_total,
                        'poin' => $a->poin_total,
                        'qty' => $a->qty,
                        'current' => $a->qty,
                        'is_master' => $a->user->is_master_stockist,
                    ]);
                }
            }
        }
        // big transaction
        if ($transaction->type == 'general') {
            foreach ($transaction->carts as $a) {
                if ($a->product->is_big) {
                    $dt = Carbon::parse($transaction->created_at);
                    for ($i = 2; $i <= $a->product->month; $i++) {
                        // create future transactions
                        $date = $dt->copy()->addMonthNoOverflow($i - 1);
                        $transaction->officialTransactions()->create([
                            'user_id' => $transaction->user_id,
                            'product_id' => $a->product_id,
                            'qty' => $a->qty,
                            'poin' => $a->poin_total,
                            'price' => $a->price_total,
                            'product_name' => $a->name,
                            'month_key' => $i,
                            'is_big' => true,
                            'status' => 'received',
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);
                    }
                }
            }
        }
        if ($is_updated) Session::flash('success', 'Confirmed');
        else Session::flash('error', 'Error while confirm');
        return back();
    }

    public function packed(Request $request, Transaction $transaction)
    {
        $is_updated = $transaction->update(array(
            'status' => 'packed'
        ));
        if($is_updated) Session::flash('success', 'Updated');
        else Session::flash('error', 'Error while input');
        return back();
    }

    public function shipment(Request $request, Transaction $transaction)
    {
        $is_updated = $transaction->update(array(
            'shipment_number' => $request->shipment_number,
            'status' => 'shipped'
        ));
        if($is_updated) Session::flash('success', 'Updated');
        else Session::flash('error', 'Error while input');
        return back();
    }

    public function received(Request $request, Transaction $transaction)
    {
        $is_updated = $transaction->update(array(
            'status' => 'received'
        ));
        if($is_updated) Session::flash('success', 'Updated');
        else Session::flash('error', 'Error while confirm');
        return back();
    }

    function stringInsert($str, $insertstr, $pos)
    {
        $str = substr($str, 0, $pos) . $insertstr . substr($str, $pos);
        return $str;
    }

    public function general()
    {
        $month = request()->month ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);
        $transactions = Transaction::with(['user', 'sponsor', 'address', 'address.subdistrict', 'address.city', 'address.province', 'carts', 'carts.product', 'user.sponsor'])
            ->whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'))
            ->where('type', 'general');
        if (request()->status && request()->status != 'all') {
            $transactions = $transactions->where('status', request()->status);
        }
        if (request()->username) {
            $transactions = $transactions->whereHas('user', function($q) {
                $q->where('username', 'like', '%' . request()->username . '%');
            });
        }
        $transactions = $transactions->orderBy('created_at')->get();
        return view('marketplace.admin.transactions.general', compact('transactions'));
    }

    public function stockist()
    {
        $month = request()->month ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);
        $transactions = Transaction::with(['user', 'sponsor', 'address', 'address.subdistrict', 'address.city', 'address.province', 'carts', 'carts.product', 'user.sponsor'])
            ->whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'))
            ->where('type', 'stockist');
        if (request()->status && request()->status != 'all') {
            $transactions = $transactions->where('status', request()->status);
        }
        if (request()->username) {
            $transactions = $transactions->whereHas('user', function($q) {
                $q->where('username', 'like', '%' . request()->username . '%');
            });
        }
        $transactions = $transactions->orderBy('created_at')->get();
        return view('marketplace.admin.transactions.stockist', compact('transactions'));
    }

    public function master()
    {
        $month = request()->month ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);
        $transactions = Transaction::with(['user', 'sponsor', 'address', 'address.subdistrict', 'address.city', 'address.province', 'carts', 'carts.product', 'user.sponsor'])
            ->whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'))
            ->where('type', 'masterstockist');
        if (request()->status && request()->status != 'all') {
            $transactions = $transactions->where('status', request()->status);
        }
        if (request()->username) {
            $transactions = $transactions->whereHas('user', function($q) {
                $q->where('username', 'like', '%' . request()->username . '%');
            });
        }
        $transactions = $transactions->orderBy('created_at')->get();
        return view('marketplace.admin.transactions.master', compact('transactions'));
    }

    public function official()
    {
        $month = request()->month ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);
        $transactions = OfficialTransaction::with(['user', 'address', 'address.subdistrict', 'address.city', 'address.province', 'user.sponsor', 'product'])
            ->whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'))
            ->where(function ($q) {
                $q->where('month_key', '<', 2)->orWhereNull('month_key');
            });
        if (request()->status && request()->status != 'all') {
            $transactions = $transactions->where('status', request()->status);
        }
        if (request()->username) {
            $transactions = $transactions->whereHas('user', function($q) {
                $q->where('username', 'like', '%' . request()->username . '%');
            });
        }
        $transactions = $transactions->orderBy('created_at')->get();
        return view('marketplace.admin.transactions.official', compact('transactions'));
    }
}
