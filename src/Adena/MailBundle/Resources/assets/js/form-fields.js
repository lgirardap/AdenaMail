module.exports = {
    attachToFields: () => {
        aceeditorWidget();
    }
};

aceeditorWidget = () => {
    let ace = require('brace');
    require('brace/mode/twig');
    require('brace/theme/monokai');

    $('.js-aceeditor').each(function() {
        let $element = $(this);
        // create an editor by reading the id from the element
        let editor = ace.edit($element.attr('id'));
        // read the data-target-id attribute to find the textarea
        let textarea = $('#'+$element.data('target-id')).hide();
        // Configure the editor
        editor.setTheme("ace/theme/monokai");
        editor.setOptions({
            maxLines: 50,
        });
        editor.$blockScrolling = Infinity;
        editor.getSession().setMode("ace/mode/twig");
        editor.getSession().setValue(textarea.val());
        editor.getSession().on('change', function(){
            textarea.val(editor.getSession().getValue());
        });
    });
};