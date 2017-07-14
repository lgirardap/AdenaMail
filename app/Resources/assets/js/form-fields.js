const mailBundleFormFields = require("src/Adena/MailBundle/Resources/assets/js/form-fields.js");

const attachAllToFields = () => {
    mailBundleFormFields.attachToFields();
};

$(document).ready(()=>{
    attachAllToFields();
});

module.exports = attachAllToFields;