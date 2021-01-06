<template>
   <div>
        
        <div v-if="loading && !carts.length" class="row">
             <div class="col-md-8">
                <article>
                    <div class="cart-product-table-wrap">
                        <div    class="row cart-rows raised mb-4 pb-3 pt-3 mt-3 border border-gray">
                            <div class="col-md-7 col-6">
                                <span  class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span> Loading....
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <div v-if="!loading && carts.length" class="row">
            <div class="col-md-8">
                <article>
                    <form action="/update/cart" method="" class="cart-form">
                        <input type="hidden" value="" name="_token" />
                        <div class="cart-product-table-wrap ">
                                <div v-for="cart in carts"  :key="cart.id" class="row cart-rows raised mb-3 pt-4 pb-4 border border-gray">
                                    <div class="col-md-2 col-6">
                                        <div class="cart-image">
                                            <img :src="cart.product_variation.image_tn" alt="">
                                        </div>
                                    </div>
                                    <div class="col-md-7 col-6">
                                        <h5><a href="#">{{ cart.product_name }}</a></h5>
                                        <div class="product--share  mt-3">
                                            <span class="bold">Item #:</span> {{ cart.product_variation.sku }}
                                        </div>
                                         
                                              <!--Product Ratting-->
                                        <div class="product-item-price"  v-if="cart.product_variation.discounted_price">
                                            <div class="product-price-amount">
                                                <span class="retail-title text-gold">SALE PRICE</span>
                                                <span class="product--price text-gold">{{ meta.currency }}{{ cart.product_variation.discounted_price | priceFormat }}</span>
                                                <span class="retail-title">{{ cart.product_variation.percentage_off }}% off</span>
                                            </div>

                                            <div class="product-price-amount retail">
                                                <span class="retail-title text-gold">PRICE</span>
                                                <span class="product--price retail-price text-gold">{{ meta.currency }}{{ cart.product_variation.price | priceFormat }}</span>
                                                <span class="retail-title"></span>
                                            </div>
                                        </div>

                                        <div class="product-item-price" v-else>
                                            <div class="product-price-amount">
                                                <span class="retail-title text-gold">PRICE</span>
                                                <span class="product--price">{{ meta.currency }}{{ cart.price | priceFormat}}</span>
                                                <span class="retail-title"></span>
                                            </div>
                                        </div>
                                        <p v-if="cart.variations.length"> {{ cart.variations.toString() }}</p>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="pt-2 pb-4">                     
                                            <label class="bold">Qty</label>
                                            <div id="quantity_1234" class="product-quantity">
                                                <select @change="updateCartQty($event,cart.product_variation.id)" id="add-to-cart-quantity" name="qty"  class="input--lg form-full"> 
                                                    <option   v-for="x in parseInt(cart.product_variation.quantity)" :selected="x == cart.quantity"  :key="x" >{{ x }}</option>
                                                </select> 
                                            </div>
                                        </div>
                                        <div class="">                     
                                            <a href="#"  @click.prevent="removeFromCart($event,cart.id)" class=" text-danger btn-transparent bold"> 
                                                <span class="button-icon"> 
                                                    <i class="far fa-trash-alt"></i>
                                                </span>
                                                {{ removeCart }} 
                                            </a> 
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </form>
                </article>
            </div>

            <div class="col-md-4">
                <div class="cart-collateralse  border pb-3 pt-3 pl-3 pt-3 pr-3 raised">
                    <div class="cart_totalse">
                        <h3> Summary </h3>
                        <div class="">
                        <span class="bold">Subtotal</span> 
                        <span class="price-amount amount bold float-right"><span class="currencySymbol">{{  meta.currency }}{{ meta.sub_total | priceFormat}}</span></span>
                        </div>
                        <hr/>
                        <div>
                        <span class="bold">Total</span> 
                            <span class="price-amount amount bold float-right"><span class="currencySymbol">{{  meta.currency }}{{ meta.sub_total | priceFormat}}</span></span>
                        </div>
                        <div class="proceed-to-checkout">
                            <a href="/checkout" class="checkout-button btn btn--lg btn--primary bold full-width">Proceed to checkout</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--End Paragraph-->
        </div>
        
       
    </div>
</template>
<script>

import  { mapGetters,mapActions } from 'vuex'

export default {
    data(){
        return {
           removeCart: 'Remove',
        }
    },
    computed: {
        ...mapGetters({
            carts: 'carts',
            meta:  'meta',
            loading:'loading'
        })
    },
    mounted(){
    },
    methods: {
        ...mapActions({
            getCart:'getCart',
            deleteCart: 'deleteCart',
            updateCart: 'updateCart'
        }),
        removeFromCart(evt,cart_id){
            this.deleteCart({
                cart_id:cart_id
            })
        },
        updateCartQty(e,product_variation_id){
            let qty = e.target.value
            this.updateCart({
                product_variation_id: product_variation_id,
                quantity:qty
            })
        }

    }
    
}
</script>