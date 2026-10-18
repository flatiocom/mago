<?php

declare(strict_types=1);

class InhInsParent
{
}

class InhInsChild extends InhInsParent
{
}

function inh_check_parent(InhInsParent $obj): bool {
    // @mago-expect analysis:redundant-type-comparison
    return $obj instanceof InhInsParent;
}

inh_check_parent(new InhInsChild());
