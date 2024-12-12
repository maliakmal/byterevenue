<?php

namespace App\Http\Controllers\Swagger;

class AuthSwaggerController extends SwaggerController
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="User Email",
     *         @OA\Schema(
     *             type="string",
     *             format="email",
     *             example="user@email.com"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         description="User Password",
     *             @OA\Schema(
     *                 type="string",
     *                 format="password",
     *                 example="password"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="xx|xxxxxxxxxxxxxxxx")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(property="message", type="string", example="The provided credentials are incorrect")
     *         )
     *     )
     * )
     */
    public function login() {}

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register user",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="User Name",
     *         @OA\Schema(
     *             type="string",
     *             example="John Doe"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="User Email",
     *         @OA\Schema(
     *             type="string",
     *             format="email",
     *             example="example@mail.com"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         description="User Password",
     *         @OA\Schema(
     *             type="string",
     *             format="password",
     *             example="password"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="terms",
     *         in="query",
     *         required=true,
     *         description="Accept Terms and Conditions",
     *         @OA\Schema(
     *             type="boolean",
     *             example=true
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful registration",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="xx|xxxxxxxxxxxxxxxx")
     *             ),
     *             @OA\Property(property="message", type="string", example="User registered successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The email field must be a valid email address."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="The email field must be a valid email address."
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="The password field is required."
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function register() {}

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     * )
     */
    public function logout() {}

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh token",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="xx|xxxxxxxxxxxxxxxx")
     *             ),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     * )
     */
    public function refresh() {}

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Get authenticated user",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object", ref="#/components/schemas/User")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function me() {}

    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Forgot password",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="User Email",
     *         @OA\Schema(
     *             type="string",
     *             format="email",
     *             example="example@email.com"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="We have emailed your password reset link!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Content",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The email field must be a valid email address."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="The email field must be a valid email address."
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function forgotPassword() {}

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset password",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *     name="token",
     *     in="query",
     *     required=true,
     *     description="Password reset token",
     *     @OA\Schema(
     *         type="string",
     *         example="token"
     *     )),
     *     @OA\Parameter(
     *     name="email",
     *     in="query",
     *     required=true,
     *     description="User Email",
     *     @OA\Schema(
     *         type="string",
     *         format="email",
     *     )),
     *     @OA\Parameter(
     *     name="password",
     *     in="query",
     *     required=true,
     *     description="User Password",
     *     @OA\Schema(
     *         type="string",
     *         format="password",
     *     )),
     *     @OA\Parameter(
     *     name="password_confirmation",
     *     in="query",
     *     required=true,
     *     description="User Password Confirmation",
     *     @OA\Schema(
     *         type="string",
     *         format="password",
     *     )),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your password has been reset!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid token or email"
     *     )
     * )
     */
    public function resetPassword() {}
}
