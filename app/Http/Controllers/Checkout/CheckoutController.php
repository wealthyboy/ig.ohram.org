<?php

namespace App\Http\Controllers\Checkout;

   
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Product;
use App\ProductVariation;
use App\User;
use App\Voucher;
use App\Order;
use App\OrderedProduct;
use App\Cart;
use App\SystemSetting;
use App\Http\Controllers\Controller;
use App\Mail\OrderReceipt;
use App\Location;
use App\Http\Helper;
use App\Shipping;
use App\Address;
use App\TransactionLog;



class CheckoutController extends Controller
{
      /**
     * object to authenticate the call.
     * @param object $_apiContext
     */
   // private $_apiContext;
   
   
    /**
     * Set the ClientId and the ClientSecret.
     * @param 
     *string $_ClientId
     *string $_ClientSecret
     */
	
	public  $cart;

	public  $settings;

	public function __construct()
	{
		$this->middleware('auth');
		$this->settings =  SystemSetting::first();
	}

		
	public function  index(Request $request)  { 

		
		$carts =  Cart::all_items_in_cart();

		$total  = Cart::sum_items_in_cart();
		if (!$carts->count()){
            return redirect()->to('/cart');
		}
		$csrf = json_encode(['csrf' => csrf_token()]);
		$payementData = [];
		
		


		return view('checkout.index',['csrf' => $csrf,'payementData' => collect($payementData) ]);
	}

	
	public function confirm(Request $request,OrderedProduct $ordered_product,Order $order) { 
		
		$rate = Helper::rate();
		$user  =  \Auth::user();
		$carts =  Cart::all_items_in_cart();
		$cart = new Cart();
		$cookie = \Cookie::get('cart');


		//dd($request->all());
		if ($cookie !== null) {
			$transaction_log = TransactionLog::where('token',$cookie)->first();
			if(null != $transaction_log){
				$this->paymentConfirm($transaction_log,$request->txref,$request->amount);
			}
	    }   


		

		$order->user_id = $user->id;
		$order->address_id     =  $user->active_address->id;
		$order->coupon         =  session('coupon');
		$order->status         = 'Processing';
		$order->shipping_id    =  $request->ship_id;
		$order->shipping_price =  optional(Shipping::find($request->ship_id))->converted_price;
		$order->currency       =  Helper::getCurrency();
		$order->invoice        =  "INV-".date('Y')."-".rand(10000,39999);
		$order->payment_type   =  $request->payment_method;
		$order->txref          =  $request->txref;

		$order->order_type     = $request->admin;
		$order->total          = Cart::sum_items_in_cart();
		$order->ip             = $request->ip();
		$order->user_agent     = $request->server('HTTP_USER_AGENT');
		$order->save();
		foreach ( $carts   as $cart){
			$insert = [
				'order_id'=>$order->id,
				'product_variation_id'=>$cart->product_variation_id,
				'quantity'=>$cart->quantity,
				'status'=>"Processing",
				'price'=>$cart->ConvertCurrencyRate($cart->price),
				'total'=>$cart->ConvertCurrencyRate($cart->quantity * $cart->price),
				'created_at'=>\Carbon\Carbon::now()
			];
			OrderedProduct::Insert($insert);
			$product_variation = ProductVariation::find($cart->product_variation_id);
			$qty  = $product_variation->quantity - $cart->quantity;
			$product_variation->quantity =  $qty < 1 ? 0 : $qty;
			$product_variation->save();
		}
		$admin_emails = explode(',',$this->settings->alert_email);
		$symbol = Helper::getCurrency();
		try {
			$when = now()->addMinutes(5);
			\Mail::to($user->email)
			   ->bcc($admin_emails[0])
			   ->send(new OrderReceipt($order,$this->settings,$symbol));
		} catch (\Throwable $th) {
			dd($th);
		}

		//delete cart
		//$affectedRows = Cart::delete_items_in_cart_purchased();
		if ($request->session()->has('coupon')) {
			$code = trim(session('coupon'));
			$coupon =  Voucher::where('code',$code)->first();
			if(null !== $coupon && $coupon->type == 'specific'){
                $coupon->update(['valid'=>false]);
			}
		}
		//unset the coupon
		$request->session()->forget('coupon');
		$request->session()->forget('coupon_total');
		\Cookie::queue(\Cookie::forget('cart'));
		return redirect('/thankyou')->with(['txref' => $request->txref, 'order_id' => $order->id]);
	}

	
	protected function coupon (Request $request) { 

		$cart_total  = Cart::sum_items_in_cart();
		if ( !$cart_total ){
			$error['error']='We cannot process your voucher';
			return response()->json($error, 422);
		}

		$user  =  \Auth::user();
		// Build the input for validation
		$coupon = array('coupon' => $request->coupon);
		// Tell the validator that this file should be an image
		$rules = array(
		  'coupon' => 'required' 
		);
	
		// Now pass the input and rules into the validator
		$validator = \Validator::make($coupon, $rules);

		if ($validator->fails()) {
			return response()->json($validator->messages(), 422);
		}
		
		$coupon=  Voucher::
		                   where('code',$request->coupon)
		                    ->where('status',1)    
							->first();
	
		$error = array();

		if( empty( $coupon ) ) { 
			$error['error']='Coupon is invalid ';
			return response()->json($error, 422);
		}

		if( $coupon->is_coupon_expired() ){
			$error['error']='Coupon has expired';
			return response()->json($error, 422); 
		}


		if ( $cart_total < $coupon->from_value){
			$error['error']='You can only use this coupon when your purchase is above  '. $this->settings->currency->symbol .$coupon->from_value;
			return response()->json($error, 422);
		}


		if( !$coupon->is_valid() ){
			$error['error']='Coupon is invalid ';
			return response()->json($error, 422); 
		}
		//get all the infomation 
		$total = [];
		$total['currency'] = $this->settings->currency->symbol;

		if ( !empty ( $coupon->from_value ) && $cart_total >= $coupon->from_value  ) {
			$new_total = ($coupon->amount * $cart_total) /100;
			$new_total = $cart_total - $new_total;
			$total['sub_total'] = round($new_total,0);
			$request->session()->put(['new_total'=>$new_total]);
			$request->session()->put(['coupon_total'=>$new_total]);
			$request->session()->put(['coupon'=>$request->coupon]);
			$total['percent'] = $coupon->amount .'%  percent off';
			return response()->json($total, 200);
		} else if ( !empty ($coupon->from_value ) && $cart_total < $coupon->from_value  ) { 
			$error['error']='Coupon is invalid ';
			return response()->json($error, 422);
		} else  {
			$new_total = ($coupon->amount * $cart_total) /100;
			$new_total = $cart_total - $new_total; 
			$total['sub_total'] =   $new_total;
			$request->session()->put(['new_total'=>$new_total]);
			$request->session()->put(['coupon_total'=>$new_total]);
			$request->session()->put(['coupon'=>$request->coupon]);
			$total['percent'] = $coupon->amount .'%  percent off';
			return response()->json($total, 200);  
		}
					
	}

	public function paymentConfirm($transaction_log,$tranid,$amt){
			$prudid = 1076;
			
			$parameters = array(
				"productid"=>$prudid,
				"transactionreference"=> $tranid,
				"amount"=>$amt
				); 
			
			$ponmo = http_build_query($parameters);
				
			$url = "https://sandbox.interswitchng.com/collections/api/v1/gettransaction.json?" . $ponmo; // json
			$mac    = "D3D1D05AFE42AD50818167EAC73C109168A0F108F32645C8B59E897FA930DA44F9230910DAC9E20641823799A107A02068F7BC0F4CC41D2952E249552255710F";
			$hashv =$prudid . $tranid . $mac;
			$thash = hash('sha512',$hashv);
			//note the variables appended to the url as get values for these parameters
			$headers = array(
				"GET /HTTP/1.1",
				"Host: sandbox.interswitchng.com",
				"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1",
				"Accept-Language: en-us,en;q=0.5",
				"Keep-Alive: 300",
				"Connection: keep-alive",
					"Hash: " . $thash 
							);
				
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
			curl_setopt($ch, CURLOPT_POST, false );
			
			
			$data = curl_exec($ch);  //EXECUTE CURL STATEMENT///////////////////////////////
			$json = null;
			if (curl_errno($ch)) 
			{ 
				Log::info($ch);
			}
			else 
			{  
				// Show me the result
				$json = json_decode($data, TRUE);
				curl_close($ch);    //END CURL SESSION///////////////////////////////
			   if ($json["ResponseCode"] != '00'){
					$transaction_log->transaction_reference = $json["MerchantReference"];
					$transaction_log->approved_amount = $json["Amount"] / 100;
					$transaction_log->response_description = $json["ResponseDescription"];
					$transaction_log->status = 'Failed';
					$transaction_log->save();
			   }else{
					$transaction_log->transaction_reference = $json["MerchantReference"];
					$transaction_log->approved_amount = $json["Amount"] / 100;
					$transaction_log->response_description = $json["ResponseDescription"];
					$transaction_log->status = 'Confirmed';
					$transaction_log->save();
			   }
			}
			
	    }
		
		
}
