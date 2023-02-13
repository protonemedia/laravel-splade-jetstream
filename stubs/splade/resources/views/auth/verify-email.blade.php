@seoTitle(__('Email Verification'))

<x-jet-authentication-card>
    <x-slot:logo>
        <x-jet-authentication-card-logo />
    </x-slot>

    <x-splade-form :action="route('verification.send')" stay prevent-scroll>
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        <div v-if="form.wasSuccessful" class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
        </div>

        <div class="mt-4 flex items-center justify-between">
            <x-splade-submit :label="__('Resend Verification Email')" />

            <div>
                <Link
                    href="{{ route('profile.show') }}"
                    class="underline text-sm text-gray-600 hover:text-gray-900"
                >{{ __('Edit Profile') }}</Link>

                <Link
                    href="{{ route('logout') }}"
                    method="post"
                    as="button"
                    class="underline text-sm text-gray-600 hover:text-gray-900 ml-2"
                >{{ __('Log Out') }}</Link>
            </div>
        </div>
    </x-splade-form>
</x-jet-authentication-card>
