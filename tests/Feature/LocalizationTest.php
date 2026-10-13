<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Punto 9: las 7 pantallas que Breeze trae en inglés deben verse en español.
 *
 * Sin `lang/es.json` estas vistas imprimían el texto tal cual salía del `__()`:
 * "Email", "Password", "Save", "Delete Account"... El login y el registro ya
 * estaban traducidos con claves `auth.*`; lo que faltaba era el resto.
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // El idioma real lo fija APP_LOCALE, pero se fuerza acá para que el test
        // no dependa del .env de la máquina donde se corra.
        $this->app->setLocale('es');
    }

    public function test_la_pantalla_de_recuperar_contrasena_esta_en_espanol(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk()
            ->assertSeeText('¿Olvidaste tu contraseña?')
            ->assertSeeText('Enviar enlace para restablecer la contraseña')
            ->assertDontSeeText('Forgot your password?')
            ->assertDontSeeText('Email Password Reset Link');
    }

    public function test_la_pantalla_de_restablecer_contrasena_esta_en_espanol(): void
    {
        $response = $this->get(route('password.reset', ['token' => 'token-de-prueba']));

        $response->assertOk()
            ->assertSeeText('Restablecer contraseña')
            ->assertSeeText('Confirmar contraseña')
            ->assertDontSeeText('Reset Password');
    }

    public function test_la_pantalla_de_confirmar_contrasena_esta_en_espanol(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertOk()
            ->assertSeeText('Esta es un área segura de la aplicación.')
            ->assertSeeText('Confirmar')
            ->assertDontSeeText('This is a secure area of the application.');
    }

    public function test_la_pantalla_de_verificacion_de_correo_esta_en_espanol(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertOk()
            ->assertSeeText('Reenviar correo de verificación')
            ->assertDontSeeText('Resend Verification Email');
    }

    public function test_el_perfil_esta_en_espanol(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSeeText('Actualizar contraseña')
            ->assertSeeText('Eliminar cuenta')
            ->assertSeeText('Nombre')
            ->assertSeeText('Guardar')
            ->assertDontSeeText('Update Password')
            ->assertDontSeeText('Delete Account')
            ->assertDontSeeText('Current Password')
            ->assertDontSeeText('Save');
    }

    public function test_cada_clave_de_breeze_tiene_traduccion(): void
    {
        $claves = array_keys(json_decode(
            file_get_contents(lang_path('es.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        ));

        $this->assertNotEmpty($claves);

        foreach ($claves as $clave) {
            $this->assertNotSame(
                $clave,
                __($clave),
                "La clave «{$clave}» no tiene traducción en lang/es.json.",
            );
        }
    }
}
