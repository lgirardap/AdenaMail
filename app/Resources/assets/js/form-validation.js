let submitCallback = function(e){
    if(e) e.preventDefault();

    let $form = $(e.currentTarget);
    let data = $form.serialize();

    $.ajax($form.attr('action'), {
        method: 'POST',
        data: data,
        success: function(data){
            window.location = data.url;
        },
        error: function(data){
            // Save the name since $form will be replaced.
            let formName = $form.attr('name');
            $form.replaceWith(data.responseJSON.view);
            // Target the "new" form by name and bind the submitCallback to it
            $('form[name='+formName+']').on('submit', submitCallback);
            // Let people know what we failed
            EventBus.trigger('form:validation:failed', {
                formName: formName
            });
        }
    });
};

$(document).ready(()=>{
    $('form:not(.nojsvalidate)').on('submit', submitCallback);
});