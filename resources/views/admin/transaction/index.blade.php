@extends('admin.layouts.app')

@section('content')

<div class="row">
        

        

        <div class="col-md-12">
            <div class="card">
        
                <div class="card-content">
                
                    <h4 class="card-title">Transaction Log</h4>
                    <div class="toolbar">
                        <!-- Here you can write extra buttons/actions for the toolbar              -->
                    </div>
                    <div class="material-datatables">
                    <form action="/admin/products/destroy/multiple" method="post" enctype="multipart/form-data" id="form-products">
                        @method('DELETE')
                        @csrf
                
                        <table id="datatables" class="table table-striped table-shopping table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                            <thead>

                                <tr>
                                  <th>
                                    <div class="checkbox">
                                        <label>
                                            <input onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" type="checkbox" name="optionsCheckboxes" >
                                        </label>
                                    </div>
                                    </th>
                                    <th>Transaction id</th>

                                    <th>Full Name</th>
                                    <th>Transaction Reference</th>
                                    <th>Amount</th>
                                    <th>Approved Amount</th>
                                    <th>Response Description</th>
                                    <th>Status</th>

                                    <th>Response Code</th>
                                    <th class="disabled-sorting text-right">Actions</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                @if($transactions->count())
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" value="" name="selected[]" >
                                                    </label>
                                                </div>
                                            </td>
                                            <!-- cart-active -->
                                            
                                            <td><a target="_blank" href="#">{{ $transaction->id }}</a></td>
                                            <td><a target="_blank" href="#">{{ optional($transaction->user)->fullname() }}</a></td>
                                            <td><a target="_blank" href="#">{{ $transaction->transaction_reference }}</a></td>
                                            <td><a target="_blank" href="#">{{ $transaction->amount }}</a></td>
                                            <td><a target="_blank" href="#">{{ $transaction->approved_amount }} </a></td>
                                            <td><a target="_blank" href="#">{{ $transaction->response_description }} </a></td>
                                            <td><a target="_blank" href="#">{{ $transaction->status }} </a></td>
                                            <td><a target="_blank" href="#">{{ $transaction->response_code }} </a></td>


                                            <td></td>
                                            <td class="td-actions ">                     
                                                <a href="" rel="tooltip" title="Edit" class="btn btn-primary btn-simple btn-xs">
                                                    None
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else

                                @endif
                             
                            </tbody>
                         </table>
                        </form>
                    </div>
                </div><!-- end content-->
            </div><!--  end card  -->
        </div> <!-- end col-md-12 -->
    </div> <!-- end row -->
@endsection
@section('inline-scripts')
$(document).ready(function() {
});
@stop






