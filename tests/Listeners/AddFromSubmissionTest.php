<?php

namespace Lwekuiper\StatamicAcumbamail\Tests\Listeners;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Lwekuiper\StatamicAcumbamail\Facades\FormConfig;
use Lwekuiper\StatamicAcumbamail\Listeners\AddFromSubmission;
use Lwekuiper\StatamicAcumbamail\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\Form;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class AddFromSubmissionTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_should_handle_submission_created_event()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $event = new SubmissionCreated($submission);

        $this->mock(AddFromSubmission::class)->shouldReceive('handle')->with($event)->once();

        Event::dispatch($event);
    }

    #[Test]
    public function it_returns_true_when_consent_field_is_not_configured()
    {
        $listener = new AddFromSubmission();

        $hasConsent = $listener->hasConsent();

        $this->assertTrue($hasConsent);
    }

    #[Test]
    public function it_returns_false_when_configured_consent_field_is_false()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['consent' => false]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->consentField('consent');
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $hasConsent = $listener->hasConsent();

        $this->assertFalse($hasConsent);
    }

    #[Test]
    public function it_returns_true_when_configured_consent_field_is_true()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['consent' => true]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->consentField('consent');
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $hasConsent = $listener->hasConsent();

        $this->assertTrue($hasConsent);
    }

    #[Test]
    public function it_returns_false_when_form_config_is_missing()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $listener = new AddFromSubmission();

        $hasFormConfig = $listener->hasFormConfig($submission);

        $this->assertFalse($hasFormConfig);
    }

    #[Test]
    public function it_returns_true_when_form_config_is_present()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent')->listIds([1]);
        $formConfig->save();

        $listener = new AddFromSubmission();

        $hasFormConfig = $listener->hasFormConfig($submission);

        $this->assertTrue($hasFormConfig);
    }

    #[Test]
    public function it_correctly_uses_email_field_from_config()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'custom_email_field' => 'john@example.com',
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('custom_email_field');
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $email = $listener->getEmail();

        $this->assertEquals('john@example.com', $email);
    }

    #[Test]
    public function it_correctly_prepares_merge_data()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'custom_field' => 'Custom Value',
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent')->listIds([1]);
        $formConfig->mergeFields([
            ['statamic_field' => 'first_name', 'acumbamail_field' => 'nombre'],
            ['statamic_field' => 'last_name', 'acumbamail_field' => 'apellido'],
            ['statamic_field' => 'custom_field', 'acumbamail_field' => 'campo_personalizado'],
        ]);
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $reflectionMethod = new ReflectionMethod(AddFromSubmission::class, 'getMergeData');
        $reflectionMethod->setAccessible(true);
        $mergeData = $reflectionMethod->invoke($listener);

        $this->assertEquals([
            'email' => 'john@example.com',
            'nombre' => 'John',
            'apellido' => 'Doe',
            'campo_personalizado' => 'Custom Value',
        ], $mergeData);
    }

    #[Test]
    public function it_handles_array_fields()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'email' => 'john@example.com',
            'interests' => ['Sports', 'Music', 'Reading'],
            'skills' => ['PHP', '', 'JavaScript', null, 'Laravel'],
            'empty_array' => [],
            'null_values_only' => [null, '', null],
            'mixed_empty' => ['Valid', '', null, 'Also Valid'],
            'empty_string' => '',
            'null_value' => null,
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1]);
        $formConfig->mergeFields([
            ['statamic_field' => 'interests', 'acumbamail_field' => 'interests'],
            ['statamic_field' => 'skills', 'acumbamail_field' => 'skills'],
            ['statamic_field' => 'empty_array', 'acumbamail_field' => 'empty_field'],
            ['statamic_field' => 'null_values_only', 'acumbamail_field' => 'null_field'],
            ['statamic_field' => 'mixed_empty', 'acumbamail_field' => 'mixed_field'],
            ['statamic_field' => 'empty_string', 'acumbamail_field' => 'empty_string_field'],
            ['statamic_field' => 'null_value', 'acumbamail_field' => 'null_value_field'],
        ]);
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $reflectionMethod = new ReflectionMethod(AddFromSubmission::class, 'getMergeData');
        $reflectionMethod->setAccessible(true);
        $mergeData = $reflectionMethod->invoke($listener);

        $this->assertEquals([
            'email' => 'john@example.com',
            'interests' => 'Sports, Music, Reading',
            'skills' => 'PHP, JavaScript, Laravel',
            'mixed_field' => 'Valid, Also Valid',
        ], $mergeData);
    }

    #[Test]
    public function it_subscribes_to_multiple_lists()
    {
        Http::fake([
            'acumbamail.com/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['email' => 'john@example.com']);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1, 2, 3]);
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->handle(new SubmissionCreated($submission));

        Http::assertSentCount(3);
    }

    #[Test]
    public function it_does_not_call_api_when_list_ids_are_empty()
    {
        Http::fake([
            'acumbamail.com/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['email' => 'john@example.com']);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([]);
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->handle(new SubmissionCreated($submission));

        Http::assertSentCount(0);
    }

    #[Test]
    public function it_passes_double_optin_parameter()
    {
        Http::fake([
            'acumbamail.com/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['email' => 'john@example.com']);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1])->doubleOptin(true);
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->handle(new SubmissionCreated($submission));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'addSubscriber')
                && $request['double_optin'] == 1;
        });
    }

    #[Test]
    public function it_defaults_double_optin_to_false()
    {
        Http::fake([
            'acumbamail.com/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['email' => 'john@example.com']);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1]);
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->handle(new SubmissionCreated($submission));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'addSubscriber')
                && $request['double_optin'] == 0;
        });
    }

    #[Test]
    public function it_always_sends_update_subscriber_as_enabled()
    {
        Http::fake([
            'acumbamail.com/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['email' => 'john@example.com']);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1]);
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->handle(new SubmissionCreated($submission));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'addSubscriber')
                && $request['update_subscriber'] == 1;
        });
    }
}
