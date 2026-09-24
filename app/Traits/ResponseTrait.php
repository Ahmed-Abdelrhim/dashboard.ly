<?php

namespace App\Traits;

trait ResponseTrait
{
    public function success200($data = null, $message = 'Request completed successfully.')
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'status' => 200,
            'message' => $message,
        ], 200);
    }

    public function success201($data = null, $message = 'Created successfully.')
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'status' => 201,
            'message' => $message,
        ], 201);
    }

    public function success202($data = null)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'status' => 202,
            'message' => 'The request has been accepted for processing',
        ], 202);
    }

    public function error400($errors = null, $message = 'Unable to complete this request.')
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
            'status' => 400,
            'message' => $message,
        ], 400);
    }

    public function error401($errors = 'Unauthorized', $message = 'Please sign in to continue.')
    {
        return response()->json([
            'success' => false,
            'status' => 401,
            'errors' => $errors,
            'message' => $message,
        ], 401);
    }

    public function error403($errors = 'Forbidden', $message = 'You do not have permission to perform this action.')
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
            'status' => 403,
            'message' => $message,
        ], 403);
    }

    public function error404($message = 'The requested resource was not found.')
    {
        return response()->json([
            'success' => false,
            'status' => 404,
            'message' => $message,
            'errors' => 'Not Found',
        ], 404);
    }

    public function error422($errors, $message = 'Unable to process this request.')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'status' => 422,
            'errors' => $errors,

        ], 422);
    }

    public function error429($errors = 'Too Many Attempts.', $message = 'Too many requests. Please try again later.', array $headers = [])
    {
        return response()->json([
            'success' => false,
            'status' => 429,
            'message' => $message,
            'errors' => $errors,
        ], 429, $headers);
    }

    public function error500($errors = null, $message = 'Something went wrong. Please try again later.')
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
            'status' => 500,
            'message' => $message,
        ], 500);
    }
}
