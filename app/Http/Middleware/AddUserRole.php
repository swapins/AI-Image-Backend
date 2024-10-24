<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() == 200 && $request->user()) {
            // Assuming the User model has a method getRole() to fetch role
            $user = $request->user();
            $role = $user->hasRole('admin') ? 'admin' : 'user';

            // Modify the response to include role
            $responseData = json_decode($response->getContent(), true);
            $responseData['role'] = $role;

            // Return modified response
            $response->setContent(json_encode($responseData));
        }

        return $response;
    }
}
