<?php

declare(strict_types=1);

<h4>Tags</h4>
<ul class="tags">
<?php foreach ($tags as $name => $set): ?>
<li><?php echo ucfirst($name).($set?' - '.implode(', ',$set):''); ?>
<?php endforeach ?>
</ul>