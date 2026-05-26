<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response|RedirectResponse
    {
        $enableRegistration = admin_setting('enableRegistration');

        if ($enableRegistration !== 'on') {
            return redirect()->route('login');
        }

        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $enableRegistration = admin_setting('enableRegistration');

        if ($enableRegistration !== 'on') {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $enableEmailVerification = admin_setting('enableEmailVerification');
            $adminUser = User::where('type', 'superadmin')->first();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => $enableEmailVerification === 'on' ? null : now(),
                'type' => 'company',
                'lang' => admin_setting('defaultLanguage') ?? 'en',
                'created_by' => $adminUser?->id,
            ]);

            User::CompanySetting($user->id);
            User::MakeRole($user->id);
            $user->assignRole($user->type);

            $freePlan = Plan::where('free_plan', true)->where('status', true)->first();
            $planModules = [];

            if ($freePlan) {
                $planModules = is_array($freePlan->modules) ? $freePlan->modules : [];
                $user->active_plan = $freePlan->id;
                $user->plan_expire_date = now()->addMonth()->format('Y-m-d');
                $user->total_user = $freePlan->number_of_users;
                $user->storage_limit = $freePlan->storage_limit;
                $user->save();

                // Fast: assign module records only — setup runs after the response is sent.
                applyPlanModulesToUser($user, $planModules, false);
            }

            event(new Registered($user));

            $plainPassword = $validated['password'];
            $userId = $user->id;
            $userEmail = $user->email;
            $adminId = $adminUser?->id;

            app()->terminating(function () use ($userId, $planModules, $plainPassword, $userEmail, $adminId) {
                $companyUser = User::find($userId);
                if (! $companyUser || empty($planModules)) {
                    return;
                }

                dispatchPlanModuleSetup($companyUser, $planModules);

                try {
                    if ($adminId && admin_setting('New User') === 'on') {
                        EmailTemplate::sendEmailTemplate('New User', [$userEmail], [
                            'name' => $companyUser->name,
                            'email' => $userEmail,
                            'password' => $plainPassword,
                        ], $adminId);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            });

            if ($enableEmailVerification === 'on') {
                SetConfigEmail($adminUser->id);
                $user->sendEmailVerificationNotification();

                return redirect()
                    ->route('login', ['email' => $user->email])
                    ->with('success', __('Account created! Please verify your email, then log in.'));
            }

            return redirect()
                ->route('login', ['email' => $user->email])
                ->with('success', __('Account created successfully! Please log in to access your dashboard and features.'));

        } catch (\Exception $e) {
            report($e);

            return back()->withErrors(['email' => __('Registration failed. Please try again.')]);
        }
    }
}
