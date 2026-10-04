<?php

namespace App\Services;

use RuntimeException;

/**
 * Raised when the Google Gemini API cannot produce a usable scoring result.
 * The caller should fall back to manual instructor grading (pending status).
 */
class GeminiException extends RuntimeException
{
}
