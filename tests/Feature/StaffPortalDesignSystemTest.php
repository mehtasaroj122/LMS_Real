<?php

use App\Models\User;
use App\Support\Currency;
use Illuminate\Support\Facades\Hash;

test('staff portal loads the shared admin design system and accessibility adapter', function () {
    $staff = User::forceCreate([
        'role' => 'staff',
        'name' => 'Staff UI Reviewer',
        'email' => 'staff-ui-reviewer@example.com',
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $this
        ->actingAs($staff)
        ->get(route('staff.dashboard'))
        ->assertOk()
        ->assertSee('class="light-theme admin-portal staff-portal"', false)
        ->assertSee('admin/CSS/admin-design-system.css', false)
        ->assertSee('admin/JS/admin-ui.js', false)
        ->assertSee('quick-action-currency-mark', false)
        ->assertSee(Currency::SYMBOL);
});

test('staff circulation searches reserve space for inset clear buttons', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get(route('staff.issue-book.index'))
        ->assertOk()
        ->assertSee('id="searchStudent" class="search-input search-input-clearable"', false)
        ->assertSee('id="accessionNumber" class="search-input search-input-clearable"', false);

    $this->actingAs($staff)
        ->get(route('staff.return-book.index'))
        ->assertOk()
        ->assertSee('id="searchReturnStudent" class="search-input search-input-clearable"', false)
        ->assertSee('id="returnAccessionNumber" class="search-input search-input-clearable"', false);
});
