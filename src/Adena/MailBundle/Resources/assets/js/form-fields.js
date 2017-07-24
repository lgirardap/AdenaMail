module.exports = (el) => {
    aceeditorWidget(el);
    datetimepickerWidget(el);
    select2Widget(el);
};

aceeditorWidget = (el) => {
    let ace = require('brace');
    require('brace/mode/twig');
    require('brace/theme/monokai');

    el = el || '';

    $(el+' .js-aceeditor').each(function() {
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

datetimepickerWidget = (el) => {
    require('eonasdan-bootstrap-datetimepicker');

    el = el || '';

    $(el+' .js-datetimepicker').each(function() {
        $(this).datetimepicker({
            viewMode: 'years',
            format: 'YYYY-MM-DD'
        });
    });
};

select2Widget = (el) => {
    require('select2');
    require('vendor/tetranz/select2entity-bundle/Tetranz/Select2EntityBundle/Resources/public/js/select2entity');

    el = el || '';

    $(el+' .js-select2entity').each(function() {
        $(this).select2entity();
    });
};