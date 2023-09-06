<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\ServiceJobStatus;
use App\Filament\Resources\ServiceJobResource\Pages\CreateServiceJob;
use App\Filament\Resources\ServiceJobResource\Pages\EditServiceJob;
use App\Filament\Resources\ServiceJobResource\Pages\ListServiceJobs;
use App\Models\ServiceJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;

class ServiceJobTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_datatable(): void
    {
        $jobs = ServiceJob::factory()->count(10)->create();

        Livewire::test(ListServiceJobs::class)
            ->assertCanSeeTableRecords($jobs);
    }

    public function test_the_create_form(): void
    {
        $name = $this->faker->word;
        $status = $this->faker->randomElement(array_keys(ServiceJobStatus::getOptions()));

        Livewire::test(CreateServiceJob::class)
            ->assertSee('Create Service Job')
            ->fillForm([
                'name' => $name,
                'status' => $status,
            ])
            ->assertFormSet([
                'name' => $name,
                'status' => $status,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_the_edit_form(): void
    {
        $name = $this->faker->word;
        $status = $this->faker->randomElement(array_keys(ServiceJobStatus::getOptions()));
        $serviceJob = ServiceJob::factory()->create();

        Livewire::test(EditServiceJob::class, ['record' => $serviceJob->id])
            ->assertSee('Edit Service Job')
            ->fillForm([
                'name' => $name,
                'status' => $status,
            ])
            ->assertFormSet([
                'name' => $name,
                'status' => $status,
            ])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_payment(): void
    {
        $serviceJob = ServiceJob::factory()->create([
            'status' => ServiceJobStatus::COMPLETED->value,
        ]);
        $serviceJob->charges()->create([
            'description' => $this->faker->word,
            'amount' => 100,
        ]);

        $this->assertEquals($serviceJob->status, ServiceJobStatus::COMPLETED->value);
        Livewire::test(EditServiceJob::class, ['record' => $serviceJob->id])
            ->fillForm([
                'payments' => [
                    [
                        'amount' => 100,
                        'method' => PaymentMethod::CASH->value,
                        'payed_at' => now()->format('Y-m-d H:i:s'),
                    ]
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertEquals($serviceJob->fresh()->status, ServiceJobStatus::PAID->value);
    }
}
