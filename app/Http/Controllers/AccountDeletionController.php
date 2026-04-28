<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Services\Auth\AccountDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AccountDeletionController extends Controller
{
    public function __construct(
        private readonly AccountDeletionService $accountDeletionService
    ) {
    }

    public function destroy(DeleteAccountRequest $request): JsonResponse
    {
        $user = $request->user();
        $eligibility = $this->accountDeletionService->eligibilityFor($user);

        if ($eligibility['status'] === 'blocked_admin') {
            return response()->json([
                'success' => false,
                'message' => $eligibility['message'],
                'detail' => $eligibility['detail'],
            ], 403);
        }

        if (! $eligibility['allowed']) {
            throw ValidationException::withMessages([
                'account' => [$eligibility['message']],
            ]);
        }

        $this->accountDeletionService->delete($user);

        $guard = Auth::guard();
        if (method_exists($guard, 'logoutCurrentDevice')) {
            $guard->logoutCurrentDevice();
        } else {
            $guard->logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Your account has been deleted successfully.',
            'redirect' => url('/'),
            'redirect_delay' => 3000,
        ]);
    }
}
