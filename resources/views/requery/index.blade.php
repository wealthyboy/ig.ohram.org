@extends('layouts.auth')
 
@section('content')
   
 <!--Content-->
    <section class="sec-padding bg--gray">
        <div class="container">
            <div class="row justify-content-center">
                <div class="ml-1 col-md-6  bg--light mr-1">
                    <div class=" mt-4 mb-4">
                    <form method="POST" class="login_form pl-4 pr-4" action="/requery">
                        @csrf
                        
                        <div class="text-center"> 
                            <h2>Transaction</h2>
                        </div>
                        

                        <!--<p class="large">Great to have you back!</p>-->
                        <p class="form-group">
                            <label for="username">Transaction Id</label>
                            <input id="email" type="text" class="form-control" name="txn_ref" value="{{ old('email') }}" required autofocus>
                            @if ($errors->all() )
                                @foreach($errors->all()  as $error)
                                    <span class="error">
                                        <strong class="text-danger">{{ $error }}</strong>
                                    </span>
                                @endforeach
                            @endif
                        </p>
                        <!--<p class="large">Great to have you back!</p>-->
                        <p class="form-group">
                            <label for="username">Amount</label>
                            <input id="email" type="text" class="form-control" name="amount" value="{{ old('email') }}" required autofocus>
                            @if ($errors->all() )
                                @foreach($errors->all()  as $error)
                                    <span class="error">
                                        <strong class="text-danger">{{ $error }}</strong>
                                    </span>
                                @endforeach
                            @endif
                        </p>
                       
                        
                        <div class="clearfix"></div>

                        <p class="form-group ">
                            <button type="submit" id="login_form_button" data-loading="Loading" class=" ml-1 btn btn--primary btn-round btn-lg btn-block" name="login" value="Log in">Log In</button>
                        </p>
                        

                    </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<!--End Content-->
@endsection
