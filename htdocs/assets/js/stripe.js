Stripe.setPublishableKey('pk_test_51IuuelAX6HnD6DxptsgF5YRlm4kQX4LiZyzdRAD7YZCIB5YZs7SvhdjcE7mh9NpQDppWryfLa9AY5wYc0FJi4fSy00DFDY6Mft')
var $form = $('#payment_form')
$form.sumbit(function(e){
    e.preventDefault()
    $form.find('.button').attr('disabled',true)
    Stripe.card.createToken({$form},function (status, response){
        debugger;
    })
}) 