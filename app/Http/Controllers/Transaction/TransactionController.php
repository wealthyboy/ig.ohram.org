<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\TransactionLog;

class TransactionController extends Controller
{


    public function __construct(){
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function log(Request $request)
    {   
        $transaction_log = new TransactionLog;
        
        $request->session()->put('user_id', 'value');
        if (\Cookie::get('cart') !== null) {
            $cookie = \Cookie::get('cart');
            $request->session()->put('user_id', $cookie);

            $tl = TransactionLog::where('token',$cookie)->first();
            if(null != $tl){
                $tl->status = 'Pending';
                $tl->user_id = request()->user()->id;
                $tl->token = $cookie;
                $tl->save();
                return response(null,200);
            }

            $transaction_log->status = 'Pending';
            $transaction_log->user_id = request()->user()->id;
            $transaction_log->token = $cookie;
            $transaction_log->save();
            return response(null,200);
        
        }
        
        return response(null,404);

        
    }

    
}
