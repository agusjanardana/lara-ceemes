<?php

declare(strict_types=1);

namespace LaraCeemes\Actions;

use LaraCeemes\Support\RunsInTransaction;

abstract class Action
{
    use RunsInTransaction;
}
