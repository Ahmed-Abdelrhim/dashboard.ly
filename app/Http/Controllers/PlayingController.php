<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ResponseTrait;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use ipinfo\ipinfo\Details;
use PragmaRX\Google2FAQRCode\Google2FA;

class PlayingController extends Controller
{
    use ResponseTrait;

    /**
     * Playing is here
     */
    public function playing(Request $request)
    {
        return 'Playing';

        // $sessionId = 'ce540a41-936a-4f10-aba5-cf27e6454287';
        // return hash('sha256', $sessionId);

        $user = User::find(1);
        if (! $user) {
            return 'not found';
        }

        return $id = session('user_session_id') ?? (request()->hasSession() ? request()->session()->get('user_session_id') : null);

        return $user->getAuthIdentifier();

        $google2fa = (new Google2FA);
        // Old default behavior (v8.x and below)
        // return $secret = $google2fa->generateSecretKey();

        // return $secret = $google2fa->generateSecretKey(32);

        // New way to get 16-character secrets (v9.0+)
        // return $secret = $google2fa->generateSecretKey(16);

        $name = 'compnay Name';
        $email = 'compnayEmail@domain.com';
        $secretKey = $google2fa->generateSecretKey(32);

        $g2faUrl = $google2fa->getQRCodeUrl($name, $email, $secretKey);

        // $qrCodeUrl = $google2fa->getQRCodeInline($companyName, $companyEmail, $secretKey);

        $writer = new Writer(new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd));

        $qrcodeImage = base64_encode($writer->writeString($g2faUrl));

        return view('playing', [
            'qrcodeImage' => 'data:image/svg+xml;base64,'.$qrcodeImage,
            'secretKey' => $secretKey,
            'companyName' => $companyName,
            'companyEmail' => $companyEmail,
        ]);

        // Get SVG content as a string
        $qrCodeSvg = $writer->writeString('https://laravel.com');

        // Generate qr code url

        $secret = $request->input('secret', 'SecretIsHere');

        $valid = $google2fa->verifyKey($user->google2fa_secret, $secret);

        /** @var Details|null $ipinfo */
        $ipinfo = $request->attributes->get('ipinfo');

        $ip = $ipinfo->ip ?? $request->ip();

        $city = $ipinfo->city ?? 'Unknown';

        $country = $ipinfo->country_name ?? 'Unknown';

        return $this->success200([
            'ip' => $ip,
            'city' => $city,
            'country' => $country,
            'is_bogon' => $ipinfo->bogon ?? false,
        ], "The IP address is {$ip}.");
    }

    /**
     * Check secret.
     */
    public function checkSecret(Request $request)
    {
        $google2fa = (new Google2FA);
        $secret = $request->input('secret', '687352');

        $window = 1;
        $valid = $google2fa->verifyKey('XXMSWD5RHAVABQ6VISI2VSZTQNJABQWJ', $secret, $window);

        return $this->success200([
            'secret' => $secret,
            'valid' => $valid,
        ], 'Check secret done.');
    }
}
