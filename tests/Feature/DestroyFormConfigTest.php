<?php

namespace Lwekuiper\StatamicAcumbamail\Tests\Feature;

use Lwekuiper\StatamicAcumbamail\Facades\FormConfig;
use Lwekuiper\StatamicAcumbamail\Tests\FakesRoles;
use Lwekuiper\StatamicAcumbamail\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class DestroyFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view acumbamail']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1])->consentField('consent');
        $formConfig->save();

        $this->assertCount(1, FormConfig::all());

        $this->actingAs($user)
            ->deleteJson($formConfig->deleteUrl())
            ->assertForbidden();

        $this->assertCount(1, FormConfig::all());
    }

    #[Test]
    public function it_deletes_a_form_config()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit acumbamail']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1])->consentField('consent');
        $formConfig->save();

        $this->assertCount(1, FormConfig::all());

        $this->actingAs($user)
            ->delete($formConfig->deleteUrl())
            ->assertNoContent();

        $this->assertCount(0, FormConfig::all());
    }
}
