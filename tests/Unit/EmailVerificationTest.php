<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_has_email_verified_at_field()
    {
        $user = User::factory()->create();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_verified_at' => null,
        ]);
    }

    #[Test]
    public function user_can_mark_email_as_verified()
    {
        $user = User::factory()->create();
        
        $user->markEmailAsVerified();
        $user->save();
        
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    #[Test]
    public function user_can_check_if_email_is_verified()
    {
        $user = User::factory()->create();
        
        $this->assertFalse($user->hasVerifiedEmail());
        
        $user->markEmailAsVerified();
        
        $this->assertTrue($user->hasVerifiedEmail());
    }

    #[Test]
    public function user_implements_must_verify_email()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Contracts\Auth\MustVerifyEmail::class, $user);
    }
}
