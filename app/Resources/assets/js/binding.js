const WatchJS = require('watchjs');

const binding = {
    data: {

    },

    bindings: {

    },

    domBindings:{

    },

    // Parses both bindings and domBindings and does the binding
    bind(){
        this.unbind();

        // First bind the events
        Object.keys(this.bindings).forEach(binding => {
            let eventName = binding.split(/\s+/)[0];
            let selector = binding.split(/\s+/)[1];
            let callback = this[this.bindings[binding]].bind(this);
            let $element = $(selector);

            // Do the binding
            $element.on(eventName, callback);
            // Remember it
            this._bindings.push({
                element: $element,
                event: eventName,
                callback: callback
            });
        });

        // Bind the DOM elements
        Object.keys(this.domBindings).forEach(binding => {
            let bindedData = this.domBindings[binding];
            let $element = $(binding);
            let value = this.data[bindedData];
            let callback = this._valueChanged.bind(this, $element, bindedData);

            // Watch the value and update the dom when it changes
            WatchJS.watch(this.data, bindedData, callback);

            // Remember it
            this._domBindings.push({
                bindedData: bindedData,
                callback: callback
            });

            // If we already have data for the binding, set it
            if (value) {
                this._updateElementvalue($element, value)
            }
        });
    },

    // Unbinds everything from the page (used automatically in bind() so we avoid duplicates and zombies).
    unbind(){
        this._bindings.forEach((item)=>{
            item.element.off(item.event, item.callback);
        });

        this._domBindings.forEach((item)=>{
            WatchJS.unwatch(this.data, item.bindedData, item.callback);
        });
    },

    // Called by WatchJS when a value changes
    _valueChanged($element, bindedData){
        console.log('valueChanged');
        let value = this.data[bindedData];
        this._updateElementvalue($element, value)
    },

    // Does the actual changing of value depending on the type of DOM element we are binded to.
    _updateElementvalue($element, value){
        // Inputs, Selects and Texteareas rely on the "val" prop, while the others use "html".
        if (["input", "select", "textarea"].indexOf($element.prop('nodeName')) !== -1) {
            $element.val(value);
        } else {
            $element.html(value);
        }
    },

    // Stores the REAL bindings, so we can unbind them
    _bindings: [],

    // Stores the REAL domBiding, so we can unbind them
    _domBindings: []
};

module.exports = (options) => {
    let _binding = binding;
    Object.assign(_binding, options);

    return _binding;
};