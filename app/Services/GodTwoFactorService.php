<?php

namespace App\Services;

use App\GodTwoFactorAuthentication;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use LeadMax\TrackYourStats\System\IPWhitelist;
use PragmaRX\Google2FA\Google2FA;

class GodTwoFactorService
{
    public function isGod(int $repId): bool
    {
        return DB::table('privileges')
            ->where('rep_idrep', $repId)
            ->where('is_god', 1)
            ->exists();
    }

    public function isRequestWhitelisted(Request $request): bool
    {
        $ip = (string) $request->ip();

        if (in_array($ip, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        return IPWhitelist::contains(
            $ip,
            DB::table('ip_whitelist')->pluck('ip_address')->all()
        );
    }

    public function recordFor(int $repId): ?GodTwoFactorAuthentication
    {
        return GodTwoFactorAuthentication::query()->where('rep_id', $repId)->first();
    }

    public function confirmedRecordFor(int $repId): ?GodTwoFactorAuthentication
    {
        return GodTwoFactorAuthentication::query()
            ->where('rep_id', $repId)
            ->whereNotNull('confirmed_at')
            ->first();
    }

    public function beginEnrollment(int $repId): array
    {
        $record = $this->recordFor($repId);

        if (!$record) {
            $secret = (new Google2FA())->generateSecretKey();
            $record = GodTwoFactorAuthentication::query()->updateOrCreate(
                ['rep_id' => $repId],
                [
                    'encrypted_secret' => Crypt::encryptString($secret),
                    'recovery_codes' => null,
                    'last_used_timestep' => null,
                    'confirmed_at' => null,
                ]
            );
        } else {
            $secret = Crypt::decryptString($record->encrypted_secret);
        }

        return [$record, $secret];
    }

    public function qrDataUri(string $account, string $secret): string
    {
        $issuer = (string) config('app.network_name', 'Rising Limitless');
        $uri = (new Google2FA())->getQRCodeUrl($issuer, $account, $secret);
        $renderer = new ImageRenderer(new RendererStyle(260, 2), new SvgImageBackEnd());
        $svg = (new Writer($renderer))->writeString($uri);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function confirmEnrollment(GodTwoFactorAuthentication $record, string $code): array|false
    {
        return DB::transaction(function () use ($record, $code) {
            $locked = GodTwoFactorAuthentication::query()->lockForUpdate()->find($record->id);

            if (!$locked || $locked->confirmed_at) {
                return false;
            }

            $secret = Crypt::decryptString($locked->encrypted_secret);
            $timestep = (new Google2FA())->verifyKeyNewer($secret, $code, 0, 1);

            if ($timestep === false) {
                return false;
            }

            [$plainCodes, $hashedCodes] = $this->makeRecoveryCodes();
            $locked->forceFill([
                'confirmed_at' => now(),
                'last_used_timestep' => $timestep,
                'recovery_codes' => json_encode($hashedCodes),
            ])->save();

            return $plainCodes;
        });
    }

    public function verify(GodTwoFactorAuthentication $record, string $code): bool
    {
        return DB::transaction(function () use ($record, $code) {
            $locked = GodTwoFactorAuthentication::query()->lockForUpdate()->find($record->id);

            if (!$locked || !$locked->confirmed_at) {
                return false;
            }

            $normalized = preg_replace('/\s+/', '', $code);

            if (preg_match('/^\d{6}$/', $normalized)) {
                $secret = Crypt::decryptString($locked->encrypted_secret);
                $timestep = (new Google2FA())->verifyKeyNewer(
                    $secret,
                    $normalized,
                    $locked->last_used_timestep ?? 0,
                    1
                );

                if ($timestep === false) {
                    return false;
                }

                $locked->forceFill(['last_used_timestep' => $timestep])->save();
                return true;
            }

            $recoveryCode = Str::upper($normalized);
            $hashes = json_decode((string) $locked->recovery_codes, true) ?: [];

            foreach ($hashes as $index => $hash) {
                if (Hash::check($recoveryCode, $hash)) {
                    unset($hashes[$index]);
                    $locked->forceFill(['recovery_codes' => json_encode(array_values($hashes))])->save();
                    return true;
                }
            }

            return false;
        });
    }

    public function disable(int $repId): void
    {
        GodTwoFactorAuthentication::query()->where('rep_id', $repId)->delete();
    }

    private function makeRecoveryCodes(): array
    {
        $plain = [];

        for ($i = 0; $i < 8; $i++) {
            $plain[] = Str::upper(Str::random(5).'-'.Str::random(5));
        }

        return [$plain, array_map(fn (string $code) => Hash::make($code), $plain)];
    }
}
