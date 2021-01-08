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


    public function confirm(Request $request)
    {
        

        $parameters = array(
            "productid"=>$request->productId,
            "transactionreference"=>$request->reqRef,
            "amount"=>$request->amount
            ); 
        
        $ponmo = http_build_query($parameters) . "\n";
            
        $url = urlencode("https://sandbox.interswitchng.com/test_paydirect/api/v1/gettransaction.json " . $ponmo); // json

        //note the variables appended to the url as get values for these parameters
        $headers = array(
                "GET /HTTP/1.1",
                "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", 
                "Accept-Language: en-us,en;q=0.5",
                "Keep-Alive: 300",      
                "Connection: keep-alive",
                "Hash: " . $request->hash 
                        );
            
        $ch = curl_init(); 
            
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
        curl_setopt($ch, CURLOPT_POST, false );
    
        curl_exec($ch);
        $data = curl_exec($ch);  //EXECUTE CURL STATEMENT///////////////////////////////
        $json = null;
        if (curl_errno($ch)) 
        { 
            dd($ch);
        }
        else 
        {  
            // Show me the result
            $json = json_decode($data, TRUE);
            curl_close($ch);    //END CURL SESSION///////////////////////////////
            dd($json);
        } 

    }

    
}
