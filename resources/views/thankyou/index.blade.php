@extends('layouts.app')
 
@section('content')


<div class="page-contaiter">
<!--Content-->
<section class="sec-padding--lg">
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="error-page text-center">
                    <h1>Your Transaction</h1>
                    <p class="large">Your order has been received .</p>
                    <p class="large">Status: Approved Successfull</p>
                    <p class="large">Order Id:  {{ session('order_id') }} .</p>
                    <p class="large">Transaction Reference: {{ session('txref') }} .</p>
                    <a href="/home" class="btn btn--gray space-t--2">Check History</a>

                </div>
            </div>

        </div>
    </div>
</section>
<!--End Content-->
</div>
@endsection