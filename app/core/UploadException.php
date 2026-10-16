<?php
declare(strict_types=1);

namespace App\Core;

/** Thrown by Uploader. getMessage() is safe to show to end users. */
class UploadException extends \RuntimeException
{
}
