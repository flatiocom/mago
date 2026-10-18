<?php

declare(strict_types=1);

$x = intval('123', 1);
// @mago-expect analysis:redundant-cast
echo (int) $x;
