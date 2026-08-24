<?php

declare(strict_types=1);

namespace LaraCeemes\Enums;

enum EntryStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
