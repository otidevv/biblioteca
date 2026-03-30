<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private function resolverLayout(Request $request): string
    {
        $layout = (string) $request->query('layout', $request->input('layout', ''));

        if (in_array($layout, ['admin', 'library'], true)) {
            return $layout;
        }

        $tieneAccesoAdmin = $request->user()
            ?->roles()
            ->whereHas('permisos')
            ->exists();

        return $tieneAccesoAdmin ? 'admin' : 'library';
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $layout = $this->resolverLayout($request);

        return view($layout === 'library' ? 'profile.library' : 'profile.admin', [
            'user' => $request->user(),
            'profileLayout' => $layout,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $persona = $user->persona;

        if ($request->hasFile('foto')) {
            $rutaAnterior = $persona?->foto;

            if (!empty($rutaAnterior) && Storage::disk('public')->exists($rutaAnterior)) {
                Storage::disk('public')->delete($rutaAnterior);
            }

            $rutaFoto = $request->file('foto')->store('perfiles', 'public');

            if ($persona) {
                $persona->update(['foto' => $rutaFoto]);
            }
        }

        $user->fill([
            'name' => $data['name'],
        ]);

        $user->save();

        return Redirect::route('perfil.edit', [
            'layout' => $this->resolverLayout($request),
        ])->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $persona = $user->persona;

        Auth::logout();

        if (!empty($persona?->foto) && Storage::disk('public')->exists($persona->foto)) {
            Storage::disk('public')->delete($persona->foto);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
