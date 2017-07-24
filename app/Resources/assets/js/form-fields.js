const appBundleFormFields = require("src/Adena/MailBundle/Resources/assets/js/form-fields.js");

const attachAllToFields = (el) => {
    appBundleFormFields(el);
};

// Attach our fields
$(document).ready(()=>{
    attachAllToFields();
});

// If a form validation fails, we want to re-attach
EventBus.on('form:validation:failed', (e, data)=>{
    attachAllToFields('form[name='+data.formName+']');
});

module.exports = attachAllToFields;