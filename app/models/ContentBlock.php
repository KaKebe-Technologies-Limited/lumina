<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `content_blocks`. Generic API: see App\Core\Model. */
final class ContentBlock extends Model
{
    protected static string $table = 'content_blocks';
    protected static string $order = 'page_slug ASC, block_key ASC';
}
