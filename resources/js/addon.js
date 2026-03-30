import MergeFieldsField from './components/fieldtypes/AcumbamailMergeFieldsFieldtype.vue';
import SitesField from './components/fieldtypes/AcumbamailSitesFieldtype.vue';
import FormFieldsField from './components/fieldtypes/StatamicFormFieldsFieldtype.vue';
import Listing from './components/listing/Listing.vue';
import PublishForm from './components/publish/PublishForm.vue';

Statamic.booting(() => {
    Statamic.$components.register('acumbamail_merge_fields-fieldtype', MergeFieldsField);
    Statamic.$components.register('acumbamail_sites-fieldtype', SitesField);
    Statamic.$components.register('acumbamail-listing', Listing);
    Statamic.$components.register('acumbamail-publish-form', PublishForm);
    Statamic.$components.register('statamic_form_fields-fieldtype', FormFieldsField);
});
